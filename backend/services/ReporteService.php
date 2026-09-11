<?php

class ReporteService
{
    private ReciboRepository $recibos;

    public function __construct(ReciboRepository $recibos)
    {
        $this->recibos = $recibos;
    }

    public function ingresos(?string $desde, ?string $hasta): array
    {
        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'total_ingresos' => $this->recibos->totalIngresos($desde, $hasta),
        ];
    }

    public function morosos(): array
    {
        return $this->recibos->obtenerMorosos();
    }

    public function porcentajeCobranza(string $fecha): array
    {
        return $this->recibos->porcentajeCobranza($fecha);
    }
}
