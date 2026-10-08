<?php

// Clase encargada de validar los datos
// relacionados con los socios.
class SocioValidator
{
    // Función que valida los datos necesarios
    // para crear un nuevo socio.
    //
    // Recibe los datos como un array.
    // Devuelve un array con los errores encontrados.
    public function validarCrear(array $datos): array
    {
        // Creamos un array vacío donde vamos a guardar
        // los posibles errores.
        $errores = [];

        // Comprueba si se recibió el nombre del socio.
        if (empty($datos['nombre'])) {

            // Si el nombre está vacío,
            // guarda un mensaje de error.
            $errores['nombre'] = 'El nombre es obligatorio.';
        }

        // Comprueba si se recibió el apellido del socio.
        if (empty($datos['apellido'])) {

            // Si el apellido está vacío,
            // guarda un mensaje de error.
            $errores['apellido'] = 'El apellido es obligatorio.';
        }

        // Comprueba si se recibió el tipo de documento.
        if (empty($datos['tipo_documento'])) {

            // Si no se recibió,
            // guarda un mensaje de error.
            $errores['tipo_documento'] =
                'El tipo de documento es obligatorio.';
        }

        // Comprueba si se recibió el número de documento.
        if (empty($datos['numero_documento'])) {

            // Si está vacío,
            // guarda un mensaje de error.
            $errores['numero_documento'] =
                'El número de documento es obligatorio.';
        }

        // Comprueba si se indicó la categoría del socio.
        if (empty($datos['id_categoria'])) {

            // Si no se indicó la categoría,
            // guarda un mensaje de error.
            $errores['id_categoria'] =
                'La categoría del socio es obligatoria.';
        }

        // Comprueba el correo electrónico.
        //
        // Primero verifica que exista un correo.
        // Si existe, comprueba que tenga un formato válido.
        if (
            !empty($datos['email']) &&
            !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)
        ) {

            // Si el correo no tiene un formato válido,
            // guarda un mensaje de error.
            $errores['email'] =
                'El correo no tiene un formato válido.';
        }

        // Devuelve todos los errores encontrados.
        //
        // Si no hay errores, devuelve [].
        return $errores;
    }

    // Función que valida los datos enviados
    // para actualizar un socio existente.
    public function validarActualizar(array $datos): array
    {
        // Creamos un array vacío para guardar
        // los posibles errores.
        $errores = [];

        // Comprueba si se envió el campo email.
        //
        // Si existe y no está vacío,
        // comprueba que tenga un formato válido.
        if (
            isset($datos['email']) &&
            $datos['email'] !== '' &&
            !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)
        ) {

            // Si el correo no es válido,
            // guarda un mensaje de error.
            $errores['email'] =
                'El correo no tiene un formato válido.';
        }

        // Comprueba si se envió el campo estado.
        if (
            isset($datos['estado']) &&
            !in_array(
                $datos['estado'],
                ['activo', 'inactivo', 'moroso'],
                true
            )
        ) {

            // Si el estado no pertenece a los valores permitidos,
            // guarda un mensaje de error.
            $errores['estado'] =
                'El estado debe ser activo, inactivo o moroso.';
        }

        // Devuelve todos los errores encontrados.
        return $errores;
    }
}

