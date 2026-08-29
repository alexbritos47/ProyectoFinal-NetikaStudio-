<?php

class SocioService
{
    private SocioRepository $repository;

    public function __construct(SocioRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerSocios(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function obtenerSocio(int $id): array|false
    {
        return $this->repository->obtenerPorId($id);
    }

    public function registrarSocio(array $datos): bool
    {
        return $this->repository->crear($datos);
    }

    public function actualizarSocio(int $id, array $datos): bool
    {
        return $this->repository->actualizar($id, $datos);
    }

    public function eliminarSocio(int $id): bool
    {
        return $this->repository->eliminar($id);
    }
}