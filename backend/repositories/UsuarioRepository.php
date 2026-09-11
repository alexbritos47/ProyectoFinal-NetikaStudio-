<?php

require_once __DIR__ . '/../database/Database.php';

class UsuarioRepository
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->conexion->query(
            "SELECT id_usuario, nombre_usuario, nombre_completo, rol, email, estado
             FROM USUARIO"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_usuario, nombre_usuario, nombre_completo, rol, email, estado
             FROM USUARIO WHERE id_usuario = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Incluye la contraseña (hash), sólo para uso interno de autenticación.
    public function obtenerPorNombreUsuario(string $nombreUsuario): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM USUARIO WHERE nombre_usuario = :nombre_usuario"
        );
        $stmt->execute(['nombre_usuario' => $nombreUsuario]);
        return $stmt->fetch();
    }

    public function obtenerPorCorreo(string $email): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM USUARIO WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO USUARIO (nombre_usuario, contrasena, nombre_completo, rol, email, estado)
             VALUES (:nombre_usuario, :contrasena, :nombre_completo, :rol, :email, :estado)"
        );

        $stmt->execute([
            'nombre_usuario'  => $datos['nombre_usuario'],
            'contrasena'      => password_hash($datos['contrasena'], PASSWORD_DEFAULT),
            'nombre_completo' => $datos['nombre_completo'],
            'rol'             => $datos['rol'],
            'email'           => $datos['email'] ?? null,
            'estado'          => $datos['estado'] ?? 'activo',
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $parametros = ['id' => $id];

        $permitidos = ['nombre_usuario', 'nombre_completo', 'rol', 'email', 'estado'];
        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $datos)) {
                $campos[] = "$campo = :$campo";
                $parametros[$campo] = $datos[$campo];
            }
        }

        if (array_key_exists('contrasena', $datos) && !empty($datos['contrasena'])) {
            $campos[] = "contrasena = :contrasena";
            $parametros['contrasena'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE USUARIO SET " . implode(', ', $campos) . " WHERE id_usuario = :id";
        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute($parametros);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conexion->prepare("DELETE FROM USUARIO WHERE id_usuario = :id");
        return $stmt->execute(['id' => $id]);
    }
}
