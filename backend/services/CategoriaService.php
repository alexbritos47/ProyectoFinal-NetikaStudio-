<?php

// Clase que se encarga de manejar la lógica de las categorías.
class CategoriaService
{
    // Guarda el objeto CategoriaRepository.
    // Este Repository es el encargado de comunicarse con la base de datos.
    private CategoriaRepository $repository;

    // Constructor de la clase.
    // Recibe un objeto de tipo CategoriaRepository.
    public function __construct(CategoriaRepository $repository)
    {
        // Guarda el Repository recibido dentro de la propiedad $repository.
        $this->repository = $repository;
    }

    // Función para obtener todas las categorías.
    // Devuelve un array.
    public function obtenerTodas(): array
    {
        // Llama al método obtenerTodos() del Repository.
        // El Repository consulta la base de datos.
        return $this->repository->obtenerTodos();
    }

    // Función para obtener una categoría por su ID.
    // Recibe un número entero y puede devolver un array o false.
    public function obtenerPorId(int $id): array|false
    {
        // Le pasa el ID al Repository para buscar la categoría.
        return $this->repository->obtenerPorId($id);
    }

    // Función para crear una nueva categoría.
    // Recibe los datos como un array.
    // Devuelve un número entero que normalmente es el ID creado.
    public function crear(array $datos): int
    {
        // Envía los datos al Repository para guardarlos en la base de datos.
        return $this->repository->crear($datos);
    }

    // Función para actualizar una categoría existente.
    // Recibe el ID y los nuevos datos.
    // Devuelve true si se actualizó correctamente o false si no.
    public function actualizar(int $id, array $datos): bool
    {
        // Envía el ID y los datos al Repository para realizar la actualización.
        return $this->repository->actualizar($id, $datos);
    }

    // Función para eliminar una categoría.
    // Recibe el ID de la categoría.
    // Devuelve true si se eliminó correctamente o false si no.
    public function eliminar(int $id): bool
    {
        // Envía el ID al Repository para eliminar la categoría de la base de datos.
        return $this->repository->eliminar($id);
    }
}

