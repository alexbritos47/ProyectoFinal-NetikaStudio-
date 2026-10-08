<?php

// Clase que se encarga de manejar la lógica relacionada con las cobranzas.
class CobranzaService
{
    // Guarda una instancia de CobranzaRepository.
    // El Repository es el encargado de trabajar directamente con la base de datos.
    private CobranzaRepository $repository;

    // Constructor de la clase.
    // Recibe un objeto CobranzaRepository.
    public function __construct(CobranzaRepository $repository)
    {
        // Guarda el Repository recibido dentro de la propiedad $repository.
        $this->repository = $repository;
    }

    // Función que obtiene las cobranzas pendientes.
    // $idCobrador puede ser un número entero o null.
    // $fecha puede ser una fecha o null.
    // Devuelve un array con los resultados.
    public function obtenerPendientes(?int $idCobrador = null, ?string $fecha = null): array
    {
        // Llama al método obtenerPendientes() del Repository.
        // Le pasa el ID del cobrador y la fecha.
        // Si no se proporcionan, ambos pueden ser null.
        return $this->repository->obtenerPendientes($idCobrador, $fecha);
    }

    // Función que registra el resultado de una visita de cobranza.
    // Recibe el ID del socio, el ID del cobrador,
    // el resultado de la visita y una observación opcional.
    // Devuelve un número entero, normalmente el ID de la visita creada.
    public function registrarVisita(
        int $idSocio,
        int $idCobrador,
        string $resultado,
        ?string $observacion
    ): int
    {
        // Envía todos los datos al Repository.
        // El Repository se encarga de guardarlos en la base de datos.
        return $this->repository->registrarVisita(
            $idSocio,
            $idCobrador,
            $resultado,
            $observacion
        );
    }
}

