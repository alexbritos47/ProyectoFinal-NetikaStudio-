<?php

class AuthValidator
{
    public function validarInicioSesion(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre_usuario'])) {
            $errores['nombre_usuario'] = 'El nombre de usuario es obligatorio.';
        }

        if (empty($datos['contrasena'])) {
            $errores['contrasena'] = 'La contraseña es obligatoria.';
        }

        return $errores;
    }
}
