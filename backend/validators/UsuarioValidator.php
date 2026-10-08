<?php

// Clase encargada de validar los datos
// relacionados con los usuarios.
class UsuarioValidator
{
    // Función que valida los datos necesarios
    // para crear un nuevo usuario.
    //
    // Recibe los datos como un array.
    // Devuelve un array con los errores encontrados.
    public function validarCrear(array $datos): array
    {
        // Creamos un array vacío para guardar
        // los posibles errores.
        $errores = [];

        // Comprueba si se recibió el nombre de usuario.
        if (empty($datos['nombre_usuario'])) {

            // Si está vacío, guarda un mensaje de error.
            $errores['nombre_usuario'] =
                'El nombre de usuario es obligatorio.';
        }

        // Comprueba si se recibió el nombre completo.
        if (empty($datos['nombre_completo'])) {

            // Si está vacío, guarda un mensaje de error.
            $errores['nombre_completo'] =
                'El nombre completo es obligatorio.';
        }

        // Comprueba si se recibió una contraseña.
        if (empty($datos['contrasena'])) {

            // Si no se recibió,
            // guarda un mensaje de error.
            $errores['contrasena'] =
                'La contraseña es obligatoria.';

        // Si existe una contraseña,
        // comprueba que tenga al menos 6 caracteres.
        } elseif (strlen($datos['contrasena']) < 6) {

            // Si tiene menos de 6 caracteres,
            // guarda un mensaje de error.
            $errores['contrasena'] =
                'La contraseña debe tener al menos 6 caracteres.';
        }

        // Comprueba si se recibió el rol del usuario.
        if (empty($datos['rol'])) {

            // Si no se recibió,
            // guarda un mensaje de error.
            $errores['rol'] = 'El rol es obligatorio.';

        // Si existe un rol, comprueba que esté
        // dentro de los roles permitidos.
        } elseif (
            !in_array(
                $datos['rol'],
                ['administrador', 'socio', 'cobrador'],
                true
            )
        ) {

            // Si el rol no es válido,
            // guarda un mensaje de error.
            $errores['rol'] =
                'El rol debe ser administrador, socio o cobrador.';
        }

        // Comprueba el correo electrónico.
        //
        // Si se envió un correo y no tiene
        // un formato válido, genera un error.
        if (
            !empty($datos['email']) &&
            !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)
        ) {

            // Guarda el error relacionado con el correo.
            $errores['email'] =
                'El correo no tiene un formato válido.';
        }

        // Devuelve todos los errores encontrados.
        //
        // Si no hay errores, devuelve [].
        return $errores;
    }

    // Función que valida los datos enviados
    // para actualizar un usuario existente.
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

        // Comprueba si se envió el campo rol.
        //
        // Si existe, verifica que sea uno
        // de los roles permitidos.
        if (
            isset($datos['rol']) &&
            !in_array(
                $datos['rol'],
                ['administrador', 'socio', 'cobrador'],
                true
            )
        ) {

            // Si el rol no es válido,
            // guarda un mensaje de error.
            $errores['rol'] =
                'El rol debe ser administrador, socio o cobrador.';
        }

        // Comprueba si se envió una contraseña.
        //
        // Si existe, no está vacía y tiene menos
        // de 6 caracteres, genera un error.
        if (
            isset($datos['contrasena']) &&
            $datos['contrasena'] !== '' &&
            strlen($datos['contrasena']) < 6
        ) {

            // Guarda el error relacionado con la contraseña.
            $errores['contrasena'] =
                'La contraseña debe tener al menos 6 caracteres.';
        }

        // Devuelve todos los errores encontrados.
        return $errores;
    }
}

