<?php

class CobranzaService
{
    private CobranzaRepository $repository;

    public function __construct(CobranzaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerPendientes(?int $idCobrador = null, ?string $fecha = null): array
    {
        return $this->repository->obtenerPendientes($idCobrador, $fecha);
    }

    public function registrarVisita(int $idSocio, int $idCobrador, string $resultado, ?string $observacion): int
    {
        return $this->repository->registrarVisita($idSocio, $idCobrador, $resultado, $observacion);
    }
}
