<?php

class CategoriaValidator
{
    public function validarCrear(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre de la categoría es obligatorio.';
        }

        if (!isset($datos['monto_cuota']) || !is_numeric($datos['monto_cuota'])) {
            $errores['monto_cuota'] = 'El monto de la cuota es obligatorio y debe ser numérico.';
        }

        return $errores;
    }

    public function validarActualizar(array $datos): array
    {
        $errores = [];

        if (isset($datos['monto_cuota']) && !is_numeric($datos['monto_cuota'])) {
            $errores['monto_cuota'] = 'El monto de la cuota debe ser numérico.';
        }

        return $errores;
    }
}
