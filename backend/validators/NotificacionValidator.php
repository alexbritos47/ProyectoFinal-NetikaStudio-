<?php

// Clase encargada de validar los datos
// de las notificaciones.
class NotificacionValidator
{
    // Función que valida los datos necesarios
    // para crear una nueva notificación.
    //
    // Recibe los datos como un array.
    // Devuelve un array con los errores encontrados.
    public function validarCrear(array $datos): array
    {
        // Creamos un array vacío donde se van a guardar
        // los posibles errores.
        $errores = [];

        // Comprueba si se recibió el ID del socio.
        if (empty($datos['id_socio'])) {

            // Si el ID del socio está vacío,
            // guarda un mensaje de error.
            $errores['id_socio'] = 'El socio es obligatorio.';
        }

        // Comprueba si se recibió el título
        // de la notificación.
        if (empty($datos['titulo'])) {

            // Si el título está vacío,
            // guarda un mensaje de error.
            $errores['titulo'] = 'El título es obligatorio.';
        }

        // Comprueba si se recibió el mensaje
        // de la notificación.
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
}

