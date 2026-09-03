<?php

class AuthService
{
    private $usuarioRepository;

    public function __construct($usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function iniciarSesion($correo, $contrasena)
    {
        // 1. Buscar usuario por correo
        $usuario = $this->usuarioRepository->obtenerPorCorreo($correo);

        // 2. Comprobar que exista
        if (!$usuario) {
            throw new Exception("Usuario o contraseña incorrectos");
        }

        // 3. Comprobar que esté activo
        if ($usuario['estado'] !== 'activo') {
            throw new Exception("El usuario está inactivo");
        }

        // 4. Verificar contraseña
        if (!password_verify($contrasena, $usuario['contrasena'])) {
            throw new Exception("Usuario o contraseña incorrectos");
        }

        // 5. Eliminar la contraseña antes de devolver los datos
        unset($usuario['contrasena']);

        // 6. Devolver datos del usuario
        return $usuario;
    }
}