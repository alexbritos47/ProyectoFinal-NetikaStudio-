<?php

class AuthService
{
    private UsuarioRepository $repository;

    public function __construct(UsuarioRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Valida credenciales y abre sesión.
     * @throws RuntimeException si el usuario no existe, está inactivo o la contraseña es incorrecta.
     */
    public function login(string $nombreUsuario, string $contrasena): array
    {
        $usuario = $this->repository->obtenerPorNombreUsuario($nombreUsuario);

        if (!$usuario) {
            throw new RuntimeException('Usuario o contraseña incorrectos.');
        }

        if ($usuario['estado'] !== 'activo') {
            throw new RuntimeException('El usuario se encuentra inactivo.');
        }

        if (!password_verify($contrasena, $usuario['contrasena'])) {
            throw new RuntimeException('Usuario o contraseña incorrectos.');
        }

        unset($usuario['contrasena']);

        $_SESSION['usuario'] = $usuario;

        return $usuario;
    }

    public function logout(): void
    {
        unset($_SESSION['usuario']);
        session_destroy();
    }

    public function usuarioActual(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }
}
