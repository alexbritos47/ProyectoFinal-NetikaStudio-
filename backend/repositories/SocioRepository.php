<?php

class SocioRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM socios";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT * FROM socios WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO socios
                (nombre, documento, telefono, correo)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $datos['nombre'],
            $datos['documento'],
            $datos['telefono'],
            $datos['correo']
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE socios
                SET nombre = ?,
                    documento = ?,
                    telefono = ?,
                    correo = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $datos['nombre'],
            $datos['documento'],
            $datos['telefono'],
            $datos['correo'],
            $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM socios WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }
}