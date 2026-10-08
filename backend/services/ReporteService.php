<?php

// Clase encargada de manejar la lógica relacionada con los reportes.
class ReporteService
{
    // Guarda una instancia de ReciboRepository.
    // Este Repository es el que consulta la información de los recibos
    // en la base de datos.
    private ReciboRepository $recibos;

    // Constructor de la clase.
    // Recibe un objeto de tipo ReciboRepository.
    public function __construct(ReciboRepository $recibos)
    {
        // Guarda el Repository recibido en la propiedad $recibos.
        $this->recibos = $recibos;
    }

    // Genera un reporte de ingresos.
    // Recibe una fecha inicial y una fecha final.
    // Ambas pueden ser null.
    // Devuelve un array con la información del reporte.
    public function ingresos(?string $desde, ?string $hasta): array
    {
        // Devuelve un array con:
        // - La fecha desde.
        // - La fecha hasta.
        // - El total de ingresos obtenido desde el Repository.
        return [
            'desde' => $desde,
            'hasta' => $hasta,

            // Llama al Repository para calcular
            // el total de ingresos dentro del período indicado.
            'total_ingresos' => $this->recibos->totalIngresos($desde, $hasta),
        ];
    }

    // Obtiene la información de los socios morosos.
    // Devuelve un array.
    public function morosos(): array
    {
        // Llama al Repository para obtener los socios
        // que tienen recibos pendientes o están en situación de morosidad.
        return $this->recibos->obtenerMorosos();
    }

    // Calcula el porcentaje de cobranza correspondiente a una fecha.
    // Recibe una fecha como texto.
    // Devuelve un array con el resultado.
    public function porcentajeCobranza(string $fecha): array
    {
        // Llama al Repository para calcular
        // el porcentaje de cobranza de la fecha indicada.
        return $this->recibos->porcentajeCobranza($fecha);
    }
}

