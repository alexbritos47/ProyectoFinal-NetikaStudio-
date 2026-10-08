<?php

// Clase encargada de validar los datos
// necesarios para registrar un pago.
class PagoValidator
{
    // Función que valida los datos recibidos
    // para registrar un nuevo pago.
    //
    // Recibe los datos como un array.
    // Devuelve un array con los errores encontrados.
    public function validarRegistrar(array $datos): array
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

        // Comprueba dos cosas:
        //
        // 1. Que el campo "recibos" no esté vacío.
        // 2. Que "recibos" sea realmente un array.
        //
        // || significa "O".
        if (empty($datos['recibos']) || !is_array($datos['recibos'])) {

            // Si alguna de las condiciones se cumple,
            // significa que los recibos no fueron enviados
            // correctamente.
            $errores['recibos'] =
                'Debe indicar al menos un recibo a cancelar.';
        }

        // Devuelve todos los errores encontrados.
        //
        // Si no hay errores, devuelve un array vacío [].
        return $errores;
    }
}

