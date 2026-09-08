<?php

class SocioValidator
{
    public function validarCrear(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre es obligatorio.';
        }

        if (empty($datos['apellido'])) {
            $errores['apellido'] = 'El apellido es obligatorio.';
        }

        if (empty($datos['tipo_documento'])) {
            $errores['tipo_documento'] = 'El tipo de documento es obligatorio.';
        }

        if (empty($datos['numero_documento'])) {
            $errores['numero_documento'] = 'El número de documento es obligatorio.';
        }

        if (empty($datos['id_categoria'])) {
            $errores['id_categoria'] = 'La categoría del socio es obligatoria.';
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

        if (isset($datos['estado']) &&
            !in_array($datos['estado'], ['activo', 'inactivo', 'moroso'], true)) {
            $errores['estado'] = 'El estado debe ser activo, inactivo o moroso.';
        }

        return $errores;
    }
}
