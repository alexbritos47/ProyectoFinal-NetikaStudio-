<?php



class Pago

// Esta clase representa un pago realizado por un socio.
{
    // Guarda el ID del pago.
    // ?int significa que puede ser un número entero o null.
    // Se inicia en null porque el ID normalmente lo genera la base de datos.
    public ?int $id_pago = null;


    // Guarda el ID del socio que realizó el pago.
    // Es un número entero y es obligatorio.
    public int $id_socio;


    // Guarda el ID del usuario que registró el pago.
    // Puede ser un número entero o null.
    // Por eso se utiliza ?int.
    public ?int $id_usuario = null;


    // Guarda el monto total del pago.
    // float permite almacenar números con decimales.
    public float $monto_total;


    // Guarda el método utilizado para realizar el pago.
    // Puede ser texto o null si todavía no se indicó.
    public ?string $metodo_pago = null;


    // Guarda la fecha en la que se realizó el pago.
    // Puede ser texto o null.
    public ?string $fecha_pago = null;


    // Guarda una lista con los IDs de los recibos
    // que fueron cancelados mediante este pago.
    // [] significa que comienza como un array vacío.
    public array $recibos = [];
}


