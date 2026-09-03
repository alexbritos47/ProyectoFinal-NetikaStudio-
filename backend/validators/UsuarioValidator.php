<?php

class UsuarioValidator
{
    public static function validarCrear(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre es obligatorio';
        }

        if (empty($datos['documento'])) {
            $errores['documento'] = 'El documento es obligatorio';
        }

        if (empty($datos['telefono'])) {
            $errores['telefono'] = 'El teléfono es obligatorio';
        }

        if (empty($datos['correo'])) {
            $errores['correo'] = 'El correo es obligatorio';
        } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato válido';
        }

        if (empty($datos['contrasena'])) {
            $errores['contrasena'] = 'La contraseña es obligatoria';
        }

        if (empty($datos['rol'])) {
            $errores['rol'] = 'El rol es obligatorio';
        } elseif (!in_array($datos['rol'], ['administrador', 'socio'])) {
            $errores['rol'] = 'El rol debe ser administrador o socio';
        }

        return $errores;
    }


    public static function validarActualizar(array $datos): array
    {
        $errores = [];

        if (isset($datos['nombre']) && empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre no puede estar vacío';
        }

        if (isset($datos['documento']) && empty($datos['documento'])) {
            $errores['documento'] = 'El documento no puede estar vacío';
        }

        if (isset($datos['telefono']) && empty($datos['telefono'])) {
            $errores['telefono'] = 'El teléfono no puede estar vacío';
        }

        if (isset($datos['correo'])) {

            if (empty($datos['correo'])) {
                $errores['correo'] = 'El correo no puede estar vacío';
            } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = 'El correo no tiene un formato válido';
            }
        }

        if (isset($datos['contrasena']) && empty($datos['contrasena'])) {
            $errores['contrasena'] = 'La contraseña no puede estar vacía';
        }

        if (isset($datos['rol'])) {

            if (!in_array($datos['rol'], ['administrador', 'socio'])) {
                $errores['rol'] = 'El rol debe ser administrador o socio';
            }
        }

        return $errores;
    }
}