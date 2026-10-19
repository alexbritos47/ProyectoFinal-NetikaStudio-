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

        // Acceso al portal (opcional): si se manda nombre_usuario,
        // hace falta una contraseña de al menos 6 caracteres.
        if (!empty($datos['nombre_usuario'])) {

            if (!empty($datos['id_usuario'])) {

                $errores['nombre_usuario'] =
                    'Mandá nombre_usuario (crear acceso) o id_usuario '
                    . '(vincular uno existente), no los dos.';
            }

            if (empty($datos['contrasena'])) {

                $errores['contrasena'] =
                    'La contraseña es obligatoria para crear el acceso.';

            } elseif (strlen((string) $datos['contrasena']) < 6) {

                $errores['contrasena'] =
                    'La contraseña debe tener al menos 6 caracteres.';
            }
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

        // Largo máximo de los datos de contacto (coincide con la base de datos).
        $maximos = ['email' => 100, 'telefono' => 30, 'direccion' => 200];

        foreach ($maximos as $campo => $maximo) {

            if (!isset($datos[$campo])) {
                continue;
            }

            if (!is_string($datos[$campo])) {

                $errores[$campo] = "El campo $campo no es válido.";

            } elseif ($this->largo($datos[$campo]) > $maximo) {

                $errores[$campo] =
                    "El campo $campo admite hasta $maximo caracteres.";
            }
        }

        // Devuelve todos los errores encontrados.
        return $errores;
    }

    // Cuenta caracteres (no bytes): una "á" cuenta como 1.
    // Usa mbstring si está disponible; si no, un método equivalente.
    private function largo(string $texto): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($texto);
        }

        $letras = preg_split('//u', $texto, -1, PREG_SPLIT_NO_EMPTY);

        return $letras === false ? strlen($texto) : count($letras);
    }
}