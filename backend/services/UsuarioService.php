<?php

class UsuarioService
{
    private UsuarioRepository $repository;

    public function __construct(UsuarioRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerUsuarios(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function obtenerUsuario(int $id): array|false
    {
        return $this->repository->obtenerPorId($id);
    }

    public function crearUsuario(array $datos): int
    {
        $existente = $this->repository->obtenerPorNombreUsuario($datos['nombre_usuario']);

        if ($existente) {
            throw new RuntimeException('Ya existe un usuario con ese nombre de usuario.');
        }

        return $this->repository->crear($datos);
    }

    public function actualizarUsuario(int $id, array $datos): bool
    {
        return $this->repository->actualizar($id, $datos);
    }

    public function eliminarUsuario(int $id): bool
    {
        return $this->repository->eliminar($id);
    }
}
