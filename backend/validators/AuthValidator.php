<?php

// Clase encargada de validar los datos relacionados
// con el inicio de sesión.
class AuthValidator
{
    // Función que valida los datos enviados para iniciar sesión.
    // Recibe los datos como un array.
    // Devuelve un array con los posibles errores.
    public function validarInicioSesion(array $datos): array
    {
        // Crea un array vacío donde se van a guardar
        // los errores encontrados.
        $errores = [];

        // Comprueba si el nombre de usuario está vacío
        // o no fue enviado.
        if (empty($datos['nombre_usuario'])) {

            // Si está vacío, guarda un mensaje de error
            // asociado al campo nombre_usuario.
            $errores['nombre_usuario'] =
                'El nombre de usuario es obligatorio.';
        }

        // Comprueba si la contraseña está vacía
        // o no fue enviada.
        if (empty($datos['contrasena'])) {

            // Si está vacía, guarda un mensaje de error
            // asociado al campo contrasena.
            $errores['contrasena'] =
                'La contraseña es obligatoria.';
        }

        // Devuelve el array con todos los errores encontrados.
        //
        // Si no hubo errores:
        // []
        //
        // Si hubo errores, por ejemplo:
        // [
        //     'nombre_usuario' => 'El nombre de usuario es obligatorio.',
        //     'contrasena' => 'La contraseña es obligatoria.'
        // ]
        return $errores;
    }
}

