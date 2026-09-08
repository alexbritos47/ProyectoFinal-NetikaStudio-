<?php

require_once __DIR__ . '/../database/Database.php';

class CobranzaRepository
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    // Socios con recibos pendientes o en estado moroso, asignados a un cobrador
    public function obtenerPendientes(?int $idCobrador = null, ?string $fecha = null): array
    {
        $sql = "SELECT s.id_socio, s.numero_socio, s.nombre, s.apellido, s.direccion, s.telefono,
                       s.estado, COUNT(r.id_recibo) AS recibos_pendientes
                FROM SOCIO s
                JOIN RECIBO r ON r.id_socio = s.id_socio AND r.estado = 'pendiente'";

        $condiciones = [];
        $parametros = [];

        if ($fecha) {
            $condiciones[] = "r.fecha_vencimiento <= :fecha";
            $parametros['fecha'] = $fecha;
        }
        if ($idCobrador) {
            $condiciones[] = "s.id_cobrador = :id_cobrador";
            $parametros['id_cobrador'] = $idCobrador;
        }

        if ($condiciones) {
            $sql .= " WHERE " . implode(' AND ', $condiciones);
        }

        $sql .= " GROUP BY s.id_socio, s.numero_socio, s.nombre, s.apellido, s.direccion, s.telefono, s.estado
                  ORDER BY recibos_pendientes DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function registrarVisita(int $idSocio, int $idCobrador, string $resultado, ?string $observacion): int
    {
        $resultadosValidos = [
            'no_estaba', 'no_quiso_pagar', 'direccion_incorrecta', 'volver_a_visitar', 'cobro_realizado'
        ];

        if (!in_array($resultado, $resultadosValidos, true)) {
            throw new InvalidArgumentException('Resultado de visita inválido.');
        }

        $stmt = $this->conexion->prepare(
            "INSERT INTO COBRANZA_VISITA (id_socio, id_cobrador, resultado, observacion)
             VALUES (:id_socio, :id_cobrador, :resultado, :observacion)"
        );

        $stmt->execute([
            'id_socio'    => $idSocio,
            'id_cobrador' => $idCobrador,
            'resultado'   => $resultado,
            'observacion' => $observacion,
        ]);

        return (int) $this->conexion->lastInsertId();
    }
}
