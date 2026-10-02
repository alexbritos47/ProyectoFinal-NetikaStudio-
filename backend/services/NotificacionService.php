
<?php

// Clase encargada de manejar la lógica relacionada con las notificaciones.
class NotificacionService
{
    // Guarda una instancia de NotificacionRepository.
    // El Repository es el encargado de comunicarse con la base de datos.
    private NotificacionRepository $repository;

    // Constructor de la clase.
    // Recibe un objeto de tipo NotificacionRepository.
    public function __construct(NotificacionRepository $repository)
    {
        // Guarda el Repository recibido en la propiedad $repository.
        $this->repository = $repository;
    }

    // Obtiene todas las notificaciones de un socio.
    // Recibe el ID del socio.
    // Devuelve un array con las notificaciones.
    public function obtenerPorSocio(int $idSocio): array
    {
        // Llama al Repository para buscar las notificaciones
        // relacionadas con ese socio.
        return $this->repository->obtenerPorSocio($idSocio);
    }

    // Envía una nueva notificación a un socio.
    // Recibe el ID del socio, el título y el mensaje.
    // Devuelve un número entero, normalmente el ID de la notificación creada.
    public function enviar(int $idSocio, string $titulo, string $mensaje): int
    {
        // Llama al método crear() del Repository.
        // Le pasa los datos para guardar la notificación.
        return $this->repository->crear($idSocio, $titulo, $mensaje);
    }

    // Marca una notificación como leída.
    // Después de marcarla, devuelve la notificación actualizada.
    // Puede devolver un array o false si no existe.
    public function marcarLeida(int $id): array|false
    {
        // Primero le indica al Repository que marque
        // la notificación como leída.
        $this->repository->marcarLeida($id);

        // Después vuelve a buscar la notificación por su ID
        // para obtener sus datos actualizados.
        return $this->repository->obtenerPorId($id);
    }
}

