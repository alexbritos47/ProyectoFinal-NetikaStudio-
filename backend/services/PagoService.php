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
    // Lista pagos con filtros opcionales que llegan de la URL:
    //   ?id_socio=3  ?metodo_pago=efectivo  ?anulado=0  ?desde=2026-01-01  ?hasta=2026-12-31
    // "desde" y "hasta" son fechas AAAA-MM-DD y se incluyen ambos días.
    // Un filtro con formato inválido lanza InvalidArgumentException
    // (el controller lo convierte en un error 400).
    public function obtenerFiltrados(array $parametros): array
    {
        $filtros = [];

        $idSocio = $this->textoFiltro($parametros, 'id_socio');
        if ($idSocio !== null) {
            if (!ctype_digit($idSocio)) {
                throw new InvalidArgumentException('id_socio debe ser un número.');
            }
            $filtros['id_socio'] = (int) $idSocio;
        }

        $metodo = $this->textoFiltro($parametros, 'metodo_pago');
        if ($metodo !== null) {
            if (strlen($metodo) > 20) {
                throw new InvalidArgumentException('metodo_pago es demasiado largo.');
            }
            $filtros['metodo_pago'] = $metodo;
        }

        $anulado = $this->textoFiltro($parametros, 'anulado');
        if ($anulado !== null) {
            if (!in_array($anulado, ['0', '1'], true)) {
                throw new InvalidArgumentException('anulado debe ser 0 o 1.');
            }
            $filtros['anulado'] = (int) $anulado;
        }

        $desde = $this->textoFiltro($parametros, 'desde');
        $hasta = $this->textoFiltro($parametros, 'hasta');

        if ($desde !== null) {
            $fechaDesde = $this->fechaFiltro($desde, 'desde');
            $filtros['desde'] = $fechaDesde->format('Y-m-d') . ' 00:00:00';
        }

        if ($hasta !== null) {
            $fechaHasta = $this->fechaFiltro($hasta, 'hasta');
            // Se incluye todo el día "hasta": se busca antes del día siguiente.
            $filtros['hasta_exclusivo'] = $fechaHasta
                ->modify('+1 day')
                ->format('Y-m-d') . ' 00:00:00';
        }

        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            throw new InvalidArgumentException(
                'La fecha desde no puede ser posterior a la fecha hasta.'
            );
        }

        return $this->repository->obtenerFiltrados($filtros);
    }

    // Convierte AAAA-MM-DD en fecha. Rechaza fechas inexistentes (2026-02-31).
    private function fechaFiltro(string $texto, string $nombre): DateTime
    {
        $fecha = DateTime::createFromFormat('!Y-m-d', $texto);

        if (!$fecha || $fecha->format('Y-m-d') !== $texto) {
            throw new InvalidArgumentException(
                "$nombre debe ser una fecha válida con formato AAAA-MM-DD."
            );
        }

        return $fecha;
    }

    // Devuelve el valor de un parámetro como texto, o null si no vino o está vacío.
    // Rechaza valores que no sean texto (por ejemplo ?anulado[]=1).
    private function textoFiltro(array $parametros, string $clave): ?string
    {
        if (!isset($parametros[$clave]) || $parametros[$clave] === '') {
            return null;
        }

        if (!is_string($parametros[$clave])) {
            throw new InvalidArgumentException("El filtro $clave no es válido.");
        }

        return trim($parametros[$clave]);
    }


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