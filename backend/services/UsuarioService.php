```php
<?php

class UsuarioService
{
    private $usuarioRepository;

    public function __construct($usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    // Listar todos los usuarios
    public function listarUsuarios()
    {
        $usuarios = $this->usuarioRepository->obtenerTodos();

        // No devolver contraseñas
        foreach ($usuarios as &$usuario) {
            unset($usuario['contrasena']);
        }

        return $usuarios;
    }

    // Obtener un usuario por ID
    public function obtenerUsuario($id)
    {
        $usuario = $this->usuarioRepository->obtenerPorId($id);

        if (!$usuario) {
            throw new Exception("El usuario no existe");
        }

        // No devolver contraseña
        unset($usuario['contrasena']);

        return $usuario;
    }

    // Crear usuario
    public function crearUsuario($datos)
    {
        // Comprobar documento duplicado
        $usuario = $this->usuarioRepository
            ->obtenerPorDocumento($datos['documento']);

        if ($usuario) {
            throw new Exception("El documento ya está registrado");
        }

        // Comprobar correo duplicado
        $usuario = $this->usuarioRepository
            ->obtenerPorCorreo($datos['correo']);

        if ($usuario) {
            throw new Exception("El correo ya está registrado");
        }

        // Comprobar que el rol sea válido
        if (
            $datos['rol'] !== 'administrador' &&
            $datos['rol'] !== 'socio'
        ) {
            throw new Exception("El rol no es válido");
        }

        // Hashear contraseña
        $datos['contrasena'] = password_hash(
            $datos['contrasena'],
            PASSWORD_DEFAULT
        );

        // Crear usuario
        $id = $this->usuarioRepository->crear($datos);

        // Devolver usuario creado sin contraseña
        $usuario = $this->usuarioRepository->obtenerPorId($id);

        unset($usuario['contrasena']);

        return $usuario;
    }

    // Actualizar usuario
    public function actualizarUsuario($id, $datos)
    {
        // Comprobar que exista
        $usuario = $this->usuarioRepository->obtenerPorId($id);

        if (!$usuario) {
            throw new Exception("El usuario no existe");
        }

        // Comprobar documento si se está modificando
        if (isset($datos['documento'])) {

            $usuarioDocumento = $this->usuarioRepository
                ->obtenerPorDocumento($datos['documento']);

            if (
                $usuarioDocumento &&
                $usuarioDocumento['id'] != $id
            ) {
                throw new Exception("El documento ya está registrado");
            }
        }

        // Comprobar correo si se está modificando
        if (isset($datos['correo'])) {

            $usuarioCorreo = $this->usuarioRepository
                ->obtenerPorCorreo($datos['correo']);

            if (
                $usuarioCorreo &&
                $usuarioCorreo['id'] != $id
            ) {
                throw new Exception("El correo ya está registrado");
            }
        }

        // Comprobar rol
        if (isset($datos['rol'])) {

            if (
                $datos['rol'] !== 'administrador' &&
                $datos['rol'] !== 'socio'
            ) {
                throw new Exception("El rol no es válido");
            }
        }

        // Hashear contraseña solamente si se está modificando
        if (isset($datos['contrasena']) && !empty($datos['contrasena'])) {

            $datos['contrasena'] = password_hash(
                $datos['contrasena'],
                PASSWORD_DEFAULT
            );
        }

        // Actualizar
        $this->usuarioRepository->actualizar($id, $datos);

        // Obtener usuario actualizado
        $usuario = $this->usuarioRepository->obtenerPorId($id);

        // Nunca devolver contraseña
        unset($usuario['contrasena']);

        return $usuario;
    }

    // Eliminar usuario
    public function eliminarUsuario($id)
    {
        // Comprobar que exista
        $usuario = $this->usuarioRepository->obtenerPorId($id);

        if (!$usuario) {
            throw new Exception("El usuario no existe");
        }

        // Eliminar
        $this->usuarioRepository->eliminar($id);

        return true;
    }
}
