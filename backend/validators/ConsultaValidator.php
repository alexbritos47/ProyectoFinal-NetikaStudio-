<?php

// Clase encargada de validar los datos relacionados
// con las consultas de los socios.
class ConsultaValidator
{
    // Función que valida los datos necesarios
    // para crear una nueva consulta.
    //
    // Recibe los datos como un array.
    // Devuelve un array con los errores encontrados.
    public function validarCrear(array $datos): array
    {
        // Creamos un array vacío para guardar
        // los posibles errores.
        $errores = [];

        // Comprueba si se recibió el ID del socio.
        if (empty($datos['id_socio'])) {

            // Si no se recibió el socio,
            // guarda un mensaje de error.
            $errores['id_socio'] = 'El socio es obligatorio.';
        }

        // Comprueba si se recibió el asunto de la consulta.
        if (empty($datos['asunto'])) {

            // Si el asunto está vacío,
            // guarda un mensaje de error.
            $errores['asunto'] = 'El asunto es obligatorio.';
        }

        // Comprueba si se recibió el mensaje de la consulta.
        if (empty($datos['mensaje'])) {

            // Si el mensaje está vacío,
            // guarda un mensaje de error.
            $errores['mensaje'] = 'El mensaje es obligatorio.';
        }

        // Devuelve todos los errores encontrados.
        //
        // Si no hubo errores, devuelve un array vacío [].
        return $errores;
    }

    // Función que valida los datos necesarios
    // para responder una consulta.
    public function validarRespuesta(array $datos): array
    {
        // Creamos un array vacío para guardar
        // los posibles errores.
        $errores = [];

        // Comprueba si se recibió una respuesta.
        if (empty($datos['respuesta'])) {

            // Si la respuesta está vacía,
            // guarda un mensaje de error.
            $errores['respuesta'] = 'La respuesta es obligatoria.';
        }

        // Devuelve todos los errores encontrados.
        return $errores;
    }
}

