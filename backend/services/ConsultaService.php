
<?php

// Clase encargada de manejar la lógica relacionada con las consultas.
class ConsultaService
{
    // Guarda una instancia de ConsultaRepository.
    // El Repository es el encargado de comunicarse con la base de datos.
    private ConsultaRepository $repository;

    // Constructor de la clase.
    // Recibe un objeto de tipo ConsultaRepository.
    public function __construct(ConsultaRepository $repository)
    {
        // Guarda el Repository recibido en la propiedad $repository.
        $this->repository = $repository;
    }

    // Función que obtiene todas las consultas.
    // Devuelve un array con las consultas.
    public function obtenerTodas(): array
    {
        // Llama al Repository para obtener todas las consultas.
        return $this->repository->obtenerTodas();
    }

    // Función que busca una consulta por su ID.
    // Recibe un número entero.
    // Puede devolver un array o false si no encuentra la consulta.
    public function obtenerPorId(int $id): array|false
    {
        // Envía el ID al Repository para realizar la búsqueda.
        return $this->repository->obtenerPorId($id);
    }

    // Función que crea una nueva consulta.
    // Recibe el ID del socio, el asunto y el mensaje.
    // Devuelve un número entero, normalmente el ID de la consulta creada.
    public function crear(int $idSocio, string $asunto, string $mensaje): int
    {
        // Envía los datos al Repository para crear la consulta en la base de datos.
        return $this->repository->crear($idSocio, $asunto, $mensaje);
    }

    // Función que responde una consulta existente.
    // Recibe el ID de la consulta y el texto de la respuesta.
    // Devuelve true si se realizó correctamente o false si hubo un problema.
    public function responder(int $id, string $respuesta): bool
    {
        // Envía el ID y la respuesta al Repository.
        return $this->repository->responder($id, $respuesta);
    }
}

