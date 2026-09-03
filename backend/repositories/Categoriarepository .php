<?php

require_once __DIR__ . '/../database/database.php';

class CategoriaRepository
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
            "SELECT id_categoria, nombre, monto_cuota FROM CATEGORIA_SOCIO"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_categoria, nombre, monto_cuota FROM CATEGORIA_SOCIO WHERE id_categoria = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO CATEGORIA_SOCIO (nombre, monto_cuota) VALUES (:nombre, :monto_cuota)"
        );

        $stmt->execute([
            'nombre'      => $datos['nombre'],
            'monto_cuota' => $datos['monto_cuota'],
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $parametros = ['id' => $id];

        $permitidos = ['nombre', 'monto_cuota'];
        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $datos)) {
                $campos[] = "$campo = :$campo";
                $parametros[$campo] = $datos[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE CATEGORIA_SOCIO SET " . implode(', ', $campos) . " WHERE id_categoria = :id";
        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute($parametros);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conexion->prepare("DELETE FROM CATEGORIA_SOCIO WHERE id_categoria = :id");
        return $stmt->execute(['id' => $id]);
    }
}
