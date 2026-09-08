<?php

class UsuarioValidator
{
    public function validarCrear(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre_usuario'])) {
            $errores['nombre_usuario'] = 'El nombre de usuario es obligatorio.';
        }

        if (empty($datos['nombre_completo'])) {
            $errores['nombre_completo'] = 'El nombre completo es obligatorio.';
        }

        if (empty($datos['contrasena'])) {
            $errores['contrasena'] = 'La contraseña es obligatoria.';
        } elseif (strlen($datos['contrasena']) < 6) {
            $errores['contrasena'] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        if (empty($datos['rol'])) {
            $errores['rol'] = 'El rol es obligatorio.';
        } elseif (!in_array($datos['rol'], ['administrador', 'socio', 'cobrador'], true)) {
            $errores['rol'] = 'El rol debe ser administrador, socio o cobrador.';
        }

        if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El correo no tiene un formato válido.';
        }

        return $errores;
    }

    public function validarActualizar(array $datos): array
    {
        $errores = [];

        if (isset($datos['email']) && $datos['email'] !== '' &&
            !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El correo no tiene un formato válido.';
        }

        if (isset($datos['rol']) &&
            !in_array($datos['rol'], ['administrador', 'socio', 'cobrador'], true)) {
            $errores['rol'] = 'El rol debe ser administrador, socio o cobrador.';
        }

        if (isset($datos['contrasena']) && $datos['contrasena'] !== '' && strlen($datos['contrasena']) < 6) {
            $errores['contrasena'] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        return $errores;
    }
}
