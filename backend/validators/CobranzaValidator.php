<?php

class CobranzaValidator
{
    private const RESULTADOS_VALIDOS = [
        'no_estaba', 'no_quiso_pagar', 'direccion_incorrecta', 'volver_a_visitar', 'cobro_realizado'
    ];

    public function validarVisita(array $datos): array
    {
        $errores = [];

        if (empty($datos['id_socio'])) {
            $errores['id_socio'] = 'El socio es obligatorio.';
        }

        if (empty($datos['id_cobrador'])) {
            $errores['id_cobrador'] = 'El cobrador es obligatorio.';
        }

        if (empty($datos['resultado'])) {
            $errores['resultado'] = 'El resultado de la visita es obligatorio.';
        } elseif (!in_array($datos['resultado'], self::RESULTADOS_VALIDOS, true)) {
            $errores['resultado'] = 'Resultado inválido. Use: ' . implode(', ', self::RESULTADOS_VALIDOS) . '.';
        }

        return $errores;
    }
}
