<?php

require_once __DIR__ . '/../database/Database.php';

class ReciboRepository
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->conexion->query(
            "SELECT id_recibo, numero_recibo, id_socio, periodo, importe,
                    fecha_vencimiento, estado, fecha_pago
             FROM RECIBO ORDER BY fecha_vencimiento DESC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_recibo, numero_recibo, id_socio, periodo, importe,
                    fecha_vencimiento, estado, fecha_pago
             FROM RECIBO WHERE id_recibo = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorSocio(int $idSocio): array
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_recibo, numero_recibo, periodo, importe, fecha_vencimiento, estado, fecha_pago
             FROM RECIBO WHERE id_socio = :id_socio ORDER BY periodo DESC"
        );
        $stmt->execute(['id_socio' => $idSocio]);
        return $stmt->fetchAll();
    }

    public function obtenerPendientesPorSocio(int $idSocio): array
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_recibo, numero_recibo, periodo, importe, fecha_vencimiento
             FROM RECIBO WHERE id_socio = :id_socio AND estado = 'pendiente'
             ORDER BY fecha_vencimiento ASC"
        );
        $stmt->execute(['id_socio' => $idSocio]);
        return $stmt->fetchAll();
    }

    public function contarPendientesPorSocio(int $idSocio): int
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM RECIBO WHERE id_socio = :id_socio AND estado = 'pendiente'"
        );
        $stmt->execute(['id_socio' => $idSocio]);
        return (int) $stmt->fetchColumn();
    }

    public function existePeriodo(int $idSocio, string $periodo): bool
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM RECIBO WHERE id_socio = :id_socio AND periodo = :periodo"
        );
        $stmt->execute(['id_socio' => $idSocio, 'periodo' => $periodo]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO RECIBO (numero_recibo, id_socio, periodo, importe, fecha_vencimiento, estado, fecha_pago)
             VALUES (:numero_recibo, :id_socio, :periodo, :importe, :fecha_vencimiento, :estado, :fecha_pago)"
        );

        $stmt->execute([
            'numero_recibo'     => $datos['numero_recibo'],
            'id_socio'          => $datos['id_socio'],
            'periodo'           => $datos['periodo'],
            'importe'           => $datos['importe'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'estado'            => $datos['estado'] ?? 'pendiente',
            'fecha_pago'        => $datos['fecha_pago'] ?? null,
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    // Marca un conjunto de recibos como pagados (se usa dentro de una transacción de PagoRepository)
    public function marcarComoPagado(int $id, string $fechaPago): bool
    {
        $stmt = $this->conexion->prepare(
            "UPDATE RECIBO SET estado = 'pagado', fecha_pago = :fecha_pago
             WHERE id_recibo = :id AND estado = 'pendiente'"
        );
        return $stmt->execute(['fecha_pago' => $fechaPago, 'id' => $id]);
    }

    public function anular(int $id): bool
    {
        $stmt = $this->conexion->prepare(
            "UPDATE RECIBO SET estado = 'anulado' WHERE id_recibo = :id"
        );
        return $stmt->execute(['id' => $id]);
    }

    public function obtenerMorosos(): array
    {
        // Regla del proyecto: moroso = más de un recibo pendiente
        $stmt = $this->conexion->query(
            "SELECT s.id_socio, s.numero_socio, s.nombre, s.apellido,
                    COUNT(r.id_recibo) AS recibos_pendientes
             FROM SOCIO s
             JOIN RECIBO r ON r.id_socio = s.id_socio AND r.estado = 'pendiente'
             GROUP BY s.id_socio, s.numero_socio, s.nombre, s.apellido
             HAVING COUNT(r.id_recibo) > 1"
        );
        return $stmt->fetchAll();
    }

    public function porcentajeCobranza(string $fecha): array
    {
        $stmt = $this->conexion->prepare(
            "SELECT
                COUNT(*) AS total_recibos,
                SUM(CASE WHEN estado = 'pagado' THEN 1 ELSE 0 END) AS recibos_pagados
             FROM RECIBO
             WHERE fecha_vencimiento <= :fecha AND estado != 'anulado'"
        );
        $stmt->execute(['fecha' => $fecha]);
        $fila = $stmt->fetch();

        $total = (int) ($fila['total_recibos'] ?? 0);
        $pagados = (int) ($fila['recibos_pagados'] ?? 0);

        return [
            'fecha' => $fecha,
            'total_recibos' => $total,
            'recibos_pagados' => $pagados,
            'porcentaje' => $total > 0 ? round(($pagados / $total) * 100, 2) : 0.0,
        ];
    }

    public function totalIngresos(?string $desde = null, ?string $hasta = null): float
    {
        $sql = "SELECT COALESCE(SUM(importe), 0) FROM RECIBO WHERE estado = 'pagado'";
        $parametros = [];

        if ($desde) {
            $sql .= " AND fecha_pago >= :desde";
            $parametros['desde'] = $desde;
        }
        if ($hasta) {
            $sql .= " AND fecha_pago <= :hasta";
            $parametros['hasta'] = $hasta;
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);

        return (float) $stmt->fetchColumn();
    }
}
