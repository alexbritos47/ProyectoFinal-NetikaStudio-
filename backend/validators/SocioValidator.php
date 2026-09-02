<?php

class SocioValidator
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

        return $errores;
    }

    public static function validarActualizar(array $datos)
    {
        $errores = [];

        if (isset($datos['correo']) &&
            !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato válido.';
        }

        return $errores;
    }
}