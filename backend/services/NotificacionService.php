<?php

class NotificacionService
{
    private NotificacionRepository $repository;

    public function __construct(NotificacionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerPorSocio(int $idSocio): array
    {
        return $this->repository->obtenerPorSocio($idSocio);
    }

    public function enviar(int $idSocio, string $titulo, string $mensaje): int
    {
        return $this->repository->crear($idSocio, $titulo, $mensaje);
    }

    // Al visualizar la notificación, queda marcada como leída automáticamente.
    public function marcarLeida(int $id): array|false
    {
        $this->repository->marcarLeida($id);
        return $this->repository->obtenerPorId($id);
    }
}
