
<?php


class ReporteController

// Este Controller se encarga de gestionar las peticiones
// relacionadas con los reportes del sistema.
{
    // Guarda el Service que contiene la lógica de los reportes.
    private ReporteService $service;


    public function __construct(ReporteService $service)
    // Constructor de la clase.
    // Recibe el ReporteService cuando se crea el Controller.
    {
        // Guarda el Service recibido dentro de la variable $service.
        $this->service = $service;
    }


    public function ingresos(): void
    // Función que obtiene un reporte de los ingresos.
    // : void significa que no devuelve un valor directamente.
    {
        // Obtiene la fecha inicial desde la URL.
        // Si no se envía "desde", utiliza null.
        $desde = $_GET['desde'] ?? null;

        // Obtiene la fecha final desde la URL.
        // Si no se envía "hasta", utiliza null.
        $hasta = $_GET['hasta'] ?? null;

        // Llama al Service para obtener los ingresos.
        // Le pasa la fecha desde y la fecha hasta.
        // json_encode convierte el resultado a formato JSON.
        echo json_encode($this->service->ingresos($desde, $hasta));
    }


    public function morosos(): void
    // Función que obtiene un reporte de los socios morosos.
    {
        // Llama al Service para obtener los socios que tienen pagos pendientes.
        // json_encode convierte el resultado a formato JSON.
        echo json_encode($this->service->morosos());
    }


    public function porcentajeCobranza(): void
    // Función que obtiene el porcentaje de cobranza.
    {
        // Obtiene la fecha enviada desde la URL.
        // Si no se envía una fecha, utiliza la fecha actual.
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        // Llama al Service para calcular el porcentaje de cobranza.
        // Le pasa la fecha seleccionada.
        // json_encode convierte el resultado a formato JSON.
        echo json_encode($this->service->porcentajeCobranza($fecha));
    }
}

