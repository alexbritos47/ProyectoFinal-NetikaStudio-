<?php

class SocioValidator
{
    public function validar(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = "El nombre es obligatorio";
        }

        if (empty($datos['documento'])) {
            $errores[] = "El documento es obligatorio";
        }

        if (empty($datos['correo'])) {
            $errores[] = "El correo es obligatorio";
        } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo no es válido";
        }

        return $errores;
    }
}