<?php

class NotificacionValidator
{
    public function validarCrear(array $datos): array
    {
        $errores = [];

        if (empty($datos['id_socio'])) {
            $errores['id_socio'] = 'El socio es obligatorio.';
        }

        if (empty($datos['titulo'])) {
            $errores['titulo'] = 'El título es obligatorio.';
        }

        if (empty($datos['mensaje'])) {
            $errores['mensaje'] = 'El mensaje es obligatorio.';
        }

        return $errores;
    }
}
