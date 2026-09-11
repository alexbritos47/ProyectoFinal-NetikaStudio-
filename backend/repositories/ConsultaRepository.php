<?php

require_once __DIR__ . '/../database/Database.php';

class ConsultaRepository
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodas(): array
    {
        $stmt = $this->conexion->query(
            "SELECT id_consulta, id_socio, asunto, mensaje, respuesta, estado, fecha
             FROM CONSULTA ORDER BY fecha DESC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_consulta, id_socio, asunto, mensaje, respuesta, estado, fecha
             FROM CONSULTA WHERE id_consulta = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function crear(int $idSocio, string $asunto, string $mensaje): int
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO CONSULTA (id_socio, asunto, mensaje) VALUES (:id_socio, :asunto, :mensaje)"
        );
        $stmt->execute(['id_socio' => $idSocio, 'asunto' => $asunto, 'mensaje' => $mensaje]);

        return (int) $this->conexion->lastInsertId();
    }

    public function responder(int $id, string $respuesta): bool
    {
        $stmt = $this->conexion->prepare(
            "UPDATE CONSULTA SET respuesta = :respuesta, estado = 'respondida' WHERE id_consulta = :id"
        );
        return $stmt->execute(['respuesta' => $respuesta, 'id' => $id]);
    }
}
