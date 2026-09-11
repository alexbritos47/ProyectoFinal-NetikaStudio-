<?php

class PagoService
{
    private PagoRepository $repository;

    public function __construct(PagoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerTodos(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function obtenerPorId(int $id): array|false
    {
        return $this->repository->obtenerPorId($id);
    }

    public function registrarPago(int $idSocio, array $idsRecibos, ?int $idUsuario, ?string $metodoPago): int
    {
        return $this->repository->registrarPago($idSocio, $idsRecibos, $idUsuario, $metodoPago);
    }

    public function anular(int $id): bool
    {
        return $this->repository->anular($id);
    }
}
