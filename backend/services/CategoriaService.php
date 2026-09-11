<?php

class CategoriaService
{
    private CategoriaRepository $repository;

    public function __construct(CategoriaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerTodas(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function obtenerPorId(int $id): array|false
    {
        return $this->repository->obtenerPorId($id);
    }

    public function crear(array $datos): int
    {
        return $this->repository->crear($datos);
    }

    public function actualizar(int $id, array $datos): bool
    {
        return $this->repository->actualizar($id, $datos);
    }

    public function eliminar(int $id): bool
    {
        return $this->repository->eliminar($id);
    }
}
