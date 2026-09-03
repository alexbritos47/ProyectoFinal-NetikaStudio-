<?php

require_once __DIR__ . '/../database/database.php';

class SocioRepository
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
            "SELECT id_socio, numero_socio, nombre, apellido, tipo_documento, numero_documento,
                    telefono, email, fecha_ingreso, estado, id_categoria
             FROM SOCIO"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_socio, numero_socio, nombre, apellido, tipo_documento, numero_documento,
                    telefono, email, fecha_ingreso, estado, id_categoria
             FROM SOCIO WHERE id_socio = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorDocumento(string $numeroDocumento): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM SOCIO WHERE numero_documento = :numero_documento"
        );
        $stmt->execute(['numero_documento' => $numeroDocumento]);
        return $stmt->fetch();
    }

    public function obtenerPorCorreo(string $email): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM SOCIO WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO SOCIO (numero_socio, nombre, apellido, tipo_documento, numero_documento,
                                 telefono, email, fecha_ingreso, estado, id_categoria)
             VALUES (:numero_socio, :nombre, :apellido, :tipo_documento, :numero_documento,
                     :telefono, :email, :fecha_ingreso, :estado, :id_categoria)"
        );

        $stmt->execute([
            'numero_socio'     => $datos['numero_socio'],
            'nombre'           => $datos['nombre'],
            'apellido'         => $datos['apellido'],
            'tipo_documento'   => $datos['tipo_documento'],
            'numero_documento' => $datos['numero_documento'],
            'telefono'         => $datos['telefono'] ?? null,
            'email'            => $datos['email'] ?? null,
            'fecha_ingreso'    => $datos['fecha_ingreso'] ?? date('Y-m-d'),
            'estado'           => $datos['estado'] ?? 'activo',
            'id_categoria'     => $datos['id_categoria'],
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $parametros = ['id' => $id];

        $permitidos = [
            'numero_socio', 'nombre', 'apellido', 'tipo_documento', 'numero_documento',
            'telefono', 'email', 'fecha_ingreso', 'estado', 'id_categoria'
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

        $sql = "UPDATE SOCIO SET " . implode(', ', $campos) . " WHERE id_socio = :id";
        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute($parametros);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conexion->prepare("DELETE FROM SOCIO WHERE id_socio = :id");
        return $stmt->execute(['id' => $id]);
    }
}