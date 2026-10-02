
<?php

// Clase encargada de manejar la lógica relacionada con los usuarios.
class UsuarioService
{
    // Guarda una instancia de UsuarioRepository.
    // El Repository es el encargado de comunicarse con la base de datos.
    private UsuarioRepository $repository;

    // Constructor de la clase.
    // Recibe un objeto de tipo UsuarioRepository.
    public function __construct(UsuarioRepository $repository)
    {
        // Guarda el Repository recibido dentro de la propiedad $repository.
        $this->repository = $repository;
    }

    // Obtiene todos los usuarios.
    // Devuelve un array con los usuarios.
    public function obtenerUsuarios(): array
    {
        // Llama al Repository para obtener todos los usuarios.
        return $this->repository->obtenerTodos();
    }

    // Busca un usuario por su ID.
    // Recibe un número entero.
    // Puede devolver un array o false si no encuentra el usuario.
    public function obtenerUsuario(int $id): array|false
    {
        // Envía el ID al Repository para buscar el usuario.
        return $this->repository->obtenerPorId($id);
    }

    // Crea un nuevo usuario.
    // Recibe los datos del usuario como un array.
    // Devuelve un número entero, normalmente el ID creado.
    public function crearUsuario(array $datos): int
    {
        // Busca en la base de datos si ya existe
        // un usuario con el mismo nombre de usuario.
        $existente = $this->repository->obtenerPorNombreUsuario(
            $datos['nombre_usuario']
        );

        // Comprueba si encontró un usuario existente.
        if ($existente) {

            // Si ya existe, lanza una excepción.
            // De esta forma evita crear usuarios duplicados.
            throw new RuntimeException(
                'Ya existe un usuario con ese nombre de usuario.'
            );
        }

        // Si no existe otro usuario con ese nombre,
        // envía los datos al Repository para crearlo.
        return $this->repository->crear($datos);
    }

    // Actualiza los datos de un usuario existente.
    // Recibe el ID y los nuevos datos.
    // Devuelve true o false.
    public function actualizarUsuario(int $id, array $datos): bool
    {
        // Envía el ID y los datos al Repository
        // para realizar la actualización.
        return $this->repository->actualizar($id, $datos);
    }

    // Elimina un usuario por su ID.
    // Devuelve true si se eliminó correctamente o false si no.
    public function eliminarUsuario(int $id): bool
    {
        // Envía el ID al Repository para eliminar el usuario.
        return $this->repository->eliminar($id);
    }
}

