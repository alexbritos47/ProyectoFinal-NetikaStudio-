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

    public function registrarSocio(array $datos): int
    {
        if (empty($datos['numero_socio'])) {
            $datos['numero_socio'] = $this->generarNumeroSocio();
        }
        return $this->repository->crear($datos);
    }

    public function actualizarSocio(int $id, array $datos): bool
    {
        return $this->repository->actualizar($id, $datos);
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $estadosValidos = ['activo', 'inactivo', 'moroso'];

        if (!in_array($estado, $estadosValidos, true)) {
            throw new InvalidArgumentException('Estado inválido. Use: activo, inactivo o moroso.');
        }

        return $this->repository->cambiarEstado($id, $estado);
    }

    public function eliminarSocio(int $id): bool
    {
        return $this->repository->eliminar($id);
    }

    private function generarNumeroSocio(): string
    {
        return 'S-' . date('Y') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
