<?php

require_once __DIR__ . '/../database/database.php';

class CuotaPagoRepository
{
    private PDO $conexion;

    public function __construct()
    {
        $database = new Database();
        $this->conexion = $database->conectar();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->conexion->query(
            "SELECT id_pago, id_socio, periodo, monto, fecha_vencimiento, fecha_pago,
                    estado_pago, metodo_pago, numero_comprobante, id_usuario
             FROM CUOTA_PAGO"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_pago, id_socio, periodo, monto, fecha_vencimiento, fecha_pago,
                    estado_pago, metodo_pago, numero_comprobante, id_usuario
             FROM CUOTA_PAGO WHERE id_pago = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorSocio(int $idSocio): array
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM CUOTA_PAGO WHERE id_socio = :id_socio ORDER BY periodo DESC"
        );
        $stmt->execute(['id_socio' => $idSocio]);
        return $stmt->fetchAll();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO CUOTA_PAGO (id_socio, periodo, monto, fecha_vencimiento, fecha_pago,
                                      estado_pago, metodo_pago, numero_comprobante, id_usuario)
             VALUES (:id_socio, :periodo, :monto, :fecha_vencimiento, :fecha_pago,
                     :estado_pago, :metodo_pago, :numero_comprobante, :id_usuario)"
        );

        $stmt->execute([
            'id_socio'           => $datos['id_socio'],
            'periodo'            => $datos['periodo'],
            'monto'              => $datos['monto'],
            'fecha_vencimiento'  => $datos['fecha_vencimiento'],
            'fecha_pago'         => $datos['fecha_pago'] ?? null,
            'estado_pago'        => $datos['estado_pago'] ?? 'pendiente',
            'metodo_pago'        => $datos['metodo_pago'] ?? null,
            'numero_comprobante' => $datos['numero_comprobante'] ?? null,
            'id_usuario'         => $datos['id_usuario'] ?? null,
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $parametros = ['id' => $id];

        $permitidos = [
            'periodo', 'monto', 'fecha_vencimiento', 'fecha_pago',
            'estado_pago', 'metodo_pago', 'numero_comprobante', 'id_usuario'
        ];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $datos)) {
                $campos[] = "$campo = :$campo";
                $parametros[$campo] = $datos[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE CUOTA_PAGO SET " . implode(', ', $campos) . " WHERE id_pago = :id";
        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute($parametros);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conexion->prepare("DELETE FROM CUOTA_PAGO WHERE id_pago = :id");
        return $stmt->execute(['id' => $id]);
    }
}