<?php



class Recibo

// Esta clase representa un recibo de un socio.
{
    // Guarda el ID del recibo.
    // ?int significa que puede ser un número entero o null.
    // Comienza en null porque normalmente el ID lo genera la base de datos.
    public ?int $id_recibo = null;


    // Guarda el número que identifica al recibo.
    // Es un dato de tipo texto.
    public string $numero_recibo;


    // Guarda el ID del socio al que pertenece el recibo.
    // Es un número entero.
    public int $id_socio;


    // Guarda el período correspondiente al recibo.
    // Se utiliza el formato AAAA-MM.
    // Ejemplo: 2026-10 significa octubre de 2026.
    public string $periodo;


    // Guarda el importe o monto que debe pagar el socio.
    // float permite utilizar números con decimales.
    public float $importe;


    // Guarda la fecha de vencimiento del recibo.
    // Es un texto que representa una fecha.
    public string $fecha_vencimiento;


    // Guarda el estado actual del recibo.
    // Por defecto comienza como "pendiente".
    // Puede ser: pendiente, pagado o anulado.
    public string $estado = 'pendiente';


    // Guarda la fecha en la que se realizó el pago.
    // Puede ser una fecha o null si todavía no fue pagado.
    public ?string $fecha_pago = null;
}

