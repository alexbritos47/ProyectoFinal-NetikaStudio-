<?php

require_once __DIR__ . '/../database/Database.php';

class SocioRepository
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->conexion->query(
            "SELECT id_socio, id_usuario, id_cobrador, numero_socio, nombre, apellido,
                    tipo_documento, numero_documento, direccion, telefono, email,
                    fecha_ingreso, estado, id_categoria
             FROM SOCIO"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_socio, id_usuario, id_cobrador, numero_socio, nombre, apellido,
                    tipo_documento, numero_documento, direccion, telefono, email,
                    fecha_ingreso, estado, id_categoria
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

    public function obtenerPorCobrador(int $idCobrador): array
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_socio, numero_socio, nombre, apellido, direccion, telefono, estado
             FROM SOCIO WHERE id_cobrador = :id_cobrador"
        );
        $stmt->execute(['id_cobrador' => $idCobrador]);
        return $stmt->fetchAll();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO SOCIO (id_usuario, id_cobrador, numero_socio, nombre, apellido,
                                 tipo_documento, numero_documento, direccion, telefono, email,
                                 fecha_ingreso, estado, id_categoria)
             VALUES (:id_usuario, :id_cobrador, :numero_socio, :nombre, :apellido,
                     :tipo_documento, :numero_documento, :direccion, :telefono, :email,
                     :fecha_ingreso, :estado, :id_categoria)"
        );

        $stmt->execute([
            'id_usuario'       => $datos['id_usuario'] ?? null,
            'id_cobrador'      => $datos['id_cobrador'] ?? null,
            'numero_socio'     => $datos['numero_socio'],
            'nombre'           => $datos['nombre'],
            'apellido'         => $datos['apellido'],
            'tipo_documento'   => $datos['tipo_documento'],
            'numero_documento' => $datos['numero_documento'],
            'direccion'        => $datos['direccion'] ?? null,
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
            'id_usuario', 'id_cobrador', 'numero_socio', 'nombre', 'apellido',
            'tipo_documento', 'numero_documento', 'direccion', 'telefono', 'email',
            'fecha_ingreso', 'estado', 'id_categoria'
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

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->conexion->prepare(
            "UPDATE SOCIO SET estado = :estado WHERE id_socio = :id"
        );
        return $stmt->execute(['estado' => $estado, 'id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conexion->prepare("DELETE FROM SOCIO WHERE id_socio = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function obtenerActivos(): array
    {
        $stmt = $this->conexion->query(
            "SELECT id_socio, id_categoria FROM SOCIO WHERE estado = 'activo'"
        );
        return $stmt->fetchAll();
    }
}
