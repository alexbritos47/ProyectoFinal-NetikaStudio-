<?php

class Recibo
{
    public ?int $id_recibo = null;
    public string $numero_recibo;
    public int $id_socio;
    public string $periodo;         // AAAA-MM
    public float $importe;
    public string $fecha_vencimiento;
    public string $estado = 'pendiente'; // pendiente | pagado | anulado
    public ?string $fecha_pago = null;
}
