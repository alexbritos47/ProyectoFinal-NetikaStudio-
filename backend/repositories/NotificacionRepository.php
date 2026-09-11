<?php

require_once __DIR__ . '/../database/Database.php';

class NotificacionRepository
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerPorSocio(int $idSocio): array
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_notificacion, titulo, mensaje, fecha_envio, leida
             FROM NOTIFICACION WHERE id_socio = :id_socio ORDER BY fecha_envio DESC"
        );
        $stmt->execute(['id_socio' => $idSocio]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_notificacion, id_socio, titulo, mensaje, fecha_envio, leida
             FROM NOTIFICACION WHERE id_notificacion = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function crear(int $idSocio, string $titulo, string $mensaje): int
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO NOTIFICACION (id_socio, titulo, mensaje) VALUES (:id_socio, :titulo, :mensaje)"
        );
        $stmt->execute(['id_socio' => $idSocio, 'titulo' => $titulo, 'mensaje' => $mensaje]);

        return (int) $this->conexion->lastInsertId();
    }

    public function marcarLeida(int $id): bool
    {
        $stmt = $this->conexion->prepare(
            "UPDATE NOTIFICACION SET leida = 1 WHERE id_notificacion = :id"
        );
        return $stmt->execute(['id' => $id]);
    }
}
