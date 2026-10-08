<?php

// Clase encargada de validar los datos de las categorías.
class CategoriaValidator
{
    // Función que valida los datos necesarios
    // para crear una nueva categoría.
    // Recibe los datos como un array.
    // Devuelve un array con los errores encontrados.
    public function validarCrear(array $datos): array
    {
        // Crea un array vacío para guardar los posibles errores.
        $errores = [];

        // Comprueba si el nombre de la categoría está vacío
        // o no fue enviado.
        if (empty($datos['nombre'])) {

            // Si está vacío, agrega un mensaje de error
            // indicando que el nombre es obligatorio.
            $errores['nombre'] =
                'El nombre de la categoría es obligatorio.';
        }

        // Comprueba dos cosas:
        //
        // 1. Si el campo monto_cuota fue enviado.
        // 2. Si el valor es numérico.
        //
        // !isset() significa que el dato NO existe o es null.
        // !is_numeric() significa que el dato NO es numérico.
        if (!isset($datos['monto_cuota']) || !is_numeric($datos['monto_cuota'])) {

            // Si alguna de las condiciones falla,
            // agrega un mensaje de error.
            $errores['monto_cuota'] =
                'El monto de la cuota es obligatorio y debe ser numérico.';
        }

        // Devuelve todos los errores encontrados.
        // Si no hay errores, devuelve [].
        return $errores;
    }

    // Función que valida los datos para actualizar
    // una categoría existente.
    public function validarActualizar(array $datos): array
    {
        // Crea un array vacío para guardar los posibles errores.
        $errores = [];

        // Comprueba si monto_cuota fue enviado.
        // Si fue enviado, verifica que sea numérico.
        //
        // En una actualización, el monto puede no enviarse,
        // porque quizás solamente queremos modificar otro campo.
        if (isset($datos['monto_cuota']) && !is_numeric($datos['monto_cuota'])) {

            // Si el monto fue enviado pero no es numérico,
            // guarda un mensaje de error.
            $errores['monto_cuota'] =
                'El monto de la cuota debe ser numérico.';
        }

        // Devuelve los errores encontrados.
        return $errores;
    }
}

