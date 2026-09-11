<?php

class Pago
{
    public ?int $id_pago = null;
    public int $id_socio;
    public ?int $id_usuario = null;
    public float $monto_total;
    public ?string $metodo_pago = null;
    public ?string $fecha_pago = null;
    public array $recibos = []; // ids de recibos cancelados por este pago
}
