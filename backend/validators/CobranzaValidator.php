<?php

// Clase encargada de validar los datos relacionados
// con las cobranzas y las visitas a los socios.
class CobranzaValidator
{
    // Constante privada que contiene todos los resultados
    // de visita que el sistema permite aceptar.
    //
    // private = solamente se puede utilizar dentro de esta clase.
    // const = su valor no cambia durante la ejecución.
    private const RESULTADOS_VALIDOS = [
        'no_estaba',
        'no_quiso_pagar',
        'direccion_incorrecta',
        'volver_a_visitar',
        'cobro_realizado'
    ];

    // Función que valida los datos recibidos
    // para registrar una visita de cobranza.
    //
    // Recibe los datos como un array.
    // Devuelve un array con los errores encontrados.
    public function validarVisita(array $datos): array
    {
        // Creamos un array vacío donde vamos a guardar
        // los posibles errores de validación.
        $errores = [];

        // Comprueba si se recibió el ID del socio.
        //
        // empty() comprueba si el valor está vacío,
        // no existe o tiene un valor considerado vacío.
        if (empty($datos['id_socio'])) {

            // Si no se recibió el socio,
            // guardamos un mensaje de error.
            $errores['id_socio'] = 'El socio es obligatorio.';
        }

        // Comprueba si se recibió el ID del cobrador.
        if (empty($datos['id_cobrador'])) {

            // Si no se recibió el cobrador,
            // guardamos un mensaje de error.
            $errores['id_cobrador'] = 'El cobrador es obligatorio.';
        }

        // Comprueba si se recibió el resultado de la visita.
        if (empty($datos['resultado'])) {

            // Si no se recibió el resultado,
            // guardamos un mensaje de error.
            $errores['resultado'] =
                'El resultado de la visita es obligatorio.';

        // Si sí se recibió el resultado,
        // comprobamos que esté dentro de los resultados permitidos.
        } elseif (!in_array($datos['resultado'], self::RESULTADOS_VALIDOS, true)) {

            // Si el resultado no está permitido,
            // guardamos un mensaje de error.
            //
            // implode() convierte el array de resultados válidos
            // en un texto separado por ", ".
            $errores['resultado'] =
                'Resultado inválido. Use: ' .
                implode(', ', self::RESULTADOS_VALIDOS) .
                '.';
        }

        // Devuelve todos los errores encontrados.
        //
        // Si no hubo errores, devuelve un array vacío [].
        return $errores;
    }
}

