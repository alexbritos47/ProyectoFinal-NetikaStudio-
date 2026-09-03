<?php

require_once __DIR__ . '/../database/database.php';

class UsuarioRepository
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

    // NOTA: el modelo USUARIO todavía no tiene campo "documento" en el PDF.
    // Por ahora este método busca por nombre_usuario (el único campo único
    // disponible). Cuando se agregue "documento" a la tabla, cambiar la
    // consulta para que filtre por esa columna en vez de nombre_usuario.
    public function obtenerPorDocumento(string $documento): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM USUARIO WHERE nombre_usuario = :documento"
        );
        $stmt->execute(['documento' => $documento]);
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