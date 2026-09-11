<?php

class ConsultaValidator
{
    public function validarCrear(array $datos): array
    {
        $errores = [];

        if (empty($datos['id_socio'])) {
            $errores['id_socio'] = 'El socio es obligatorio.';
        }

        if (empty($datos['asunto'])) {
            $errores['asunto'] = 'El asunto es obligatorio.';
        }

        if (empty($datos['mensaje'])) {
            $errores['mensaje'] = 'El mensaje es obligatorio.';
        }

        return $errores;
    }

    public function validarRespuesta(array $datos): array
    {
        $errores = [];

        if (empty($datos['respuesta'])) {
            $errores['respuesta'] = 'La respuesta es obligatoria.';
        }

        return $errores;
    }
}
