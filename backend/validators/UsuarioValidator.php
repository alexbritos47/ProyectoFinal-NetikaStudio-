<?php

class UsuarioValidator
{
    public static function validarCrear($datos)
    {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre es obligatorio.';
        }

        if (empty($datos['documento'])) {
            $errores['documento'] = 'El documento es obligatorio.';
        }

        if (empty($datos['telefono'])) {
            $errores['telefono'] = 'El teléfono es obligatorio.';
        }

        if (empty($datos['correo'])) {
            $errores['correo'] = 'El correo es obligatorio.';
        } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato válido.';
        }

        if (empty($datos['contrasena'])) {
            $errores['contrasena'] = 'La contraseña es obligatoria.';
        }

        if (empty($datos['rol'])) {
            $errores['rol'] = 'El rol es obligatorio.';
        } elseif (!in_array($datos['rol'], ['administrador', 'socio'])) {
            $errores['rol'] = 'El rol debe ser administrador o socio.';
        }

        return $errores;
    }

    public static function validarActualizar(array $datos)
    {
        $errores = [];

        if (isset($datos['correo']) &&
            !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato válido.';
        }

        if (isset($datos['rol']) &&
            !in_array($datos['rol'], ['administrador', 'socio'])) {
            $errores['rol'] = 'El rol debe ser administrador o socio.';
        }

        return $errores;
    }
}