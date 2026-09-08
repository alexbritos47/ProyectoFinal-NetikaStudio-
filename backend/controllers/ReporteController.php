<?php

class ReporteController
{
    private ReporteService $service;

    public function __construct(ReporteService $service)
    {
        $this->service = $service;
    }

    public function ingresos(): void
    {
        $desde = $_GET['desde'] ?? null;
        $hasta = $_GET['hasta'] ?? null;

        echo json_encode($this->service->ingresos($desde, $hasta));
    }

    public function morosos(): void
    {
        echo json_encode($this->service->morosos());
    }

    public function porcentajeCobranza(): void
    {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        echo json_encode($this->service->porcentajeCobranza($fecha));
    }
}
