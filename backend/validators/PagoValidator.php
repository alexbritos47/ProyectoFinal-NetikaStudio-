<?php

class PagoValidator
{
    public function validarRegistrar(array $datos): array
    {
        $errores = [];

        if (empty($datos['id_socio'])) {
            $errores['id_socio'] = 'El socio es obligatorio.';
        }

        if (empty($datos['recibos']) || !is_array($datos['recibos'])) {
            $errores['recibos'] = 'Debe indicar al menos un recibo a cancelar.';
        }

        return $errores;
    }
}
