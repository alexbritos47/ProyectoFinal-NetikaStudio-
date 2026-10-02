
<?php

// Clase encargada de manejar la lógica relacionada con los pagos.
class PagoService
{
    // Guarda una instancia de PagoRepository.
    // El Repository es el encargado de realizar las operaciones
    // directamente sobre la base de datos.
    private PagoRepository $repository;

    // Constructor de la clase.
    // Recibe un objeto de tipo PagoRepository.
    public function __construct(PagoRepository $repository)
    {
        // Guarda el Repository recibido dentro de la propiedad $repository.
        $this->repository = $repository;
    }

    // Obtiene todos los pagos registrados.
    // Devuelve un array con los pagos.
    public function obtenerTodos(): array
    {
        // Llama al Repository para obtener todos los pagos.
        return $this->repository->obtenerTodos();
    }

    // Busca un pago específico por su ID.
    // Recibe un número entero.
    // Puede devolver un array o false si no existe.
    public function obtenerPorId(int $id): array|false
    {
        // Envía el ID al Repository para buscar el pago.
        return $this->repository->obtenerPorId($id);
    }

    // Registra un nuevo pago.
    // Recibe:
    // - ID del socio.
    // - Array con los IDs de los recibos que se van a pagar.
    // - ID del usuario que registra el pago, que puede ser null.
    // - Método de pago, que también puede ser null.
    // Devuelve un número entero, normalmente el ID del pago creado.
    public function registrarPago(
        int $idSocio,
        array $idsRecibos,
        ?int $idUsuario,
        ?string $metodoPago
    ): int
    {
        // Envía todos los datos al Repository.
        // El Repository se encarga de realizar el registro en la base de datos.
        return $this->repository->registrarPago(
            $idSocio,
            $idsRecibos,
            $idUsuario,
            $metodoPago
        );
    }

    // Anula un pago existente.
    // Recibe el ID del pago.
    // Devuelve true si se anuló correctamente o false si no.
    public function anular(int $id): bool
    {
        // Envía el ID al Repository para realizar la anulación.
        return $this->repository->anular($id);
    }
}

