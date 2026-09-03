<?php

class AuthValidator
{
    public static function validar(array $datos): array
    {
        $errores = [];

        if (empty($datos['correo'])) {
            $errores['correo'] = 'El correo es obligatorio';
        } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato válido';
        }

        if (empty($datos['contrasena'])) {
            $errores['contrasena'] = 'La contraseña es obligatoria';
        }

        return $errores;
    }
}