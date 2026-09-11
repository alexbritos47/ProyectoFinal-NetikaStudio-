<?php

class ConsultaService
{
    private ConsultaRepository $repository;

    public function __construct(ConsultaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerTodas(): array
    {
        return $this->repository->obtenerTodas();
    }

    public function obtenerPorId(int $id): array|false
    {
        return $this->repository->obtenerPorId($id);
    }

    public function crear(int $idSocio, string $asunto, string $mensaje): int
    {
        return $this->repository->crear($idSocio, $asunto, $mensaje);
    }

    public function responder(int $id, string $respuesta): bool
    {
        return $this->repository->responder($id, $respuesta);
    }
}
