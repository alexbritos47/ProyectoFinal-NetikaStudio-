
<?php

// Clase encargada de manejar la lógica relacionada con los socios.
class SocioService
{
    // Guarda una instancia de SocioRepository.
    // El Repository es el encargado de comunicarse con la base de datos.
    private SocioRepository $repository;

    // Constructor de la clase.
    // Recibe un objeto de tipo SocioRepository.
    public function __construct(SocioRepository $repository)
    {
        // Guarda el Repository recibido dentro de la propiedad $repository.
        $this->repository = $repository;
    }

    // Obtiene todos los socios.
    // Devuelve un array con los socios.
    public function obtenerSocios(): array
    {
        // Llama al Repository para obtener todos los socios.
        return $this->repository->obtenerTodos();
    }

    // Busca un socio por su ID.
    // Recibe un número entero.
    // Puede devolver un array o false si no encuentra el socio.
    public function obtenerSocio(int $id): array|false
    {
        // Envía el ID al Repository para buscar el socio.
        return $this->repository->obtenerPorId($id);
    }

    // Registra un nuevo socio.
    // Recibe los datos del socio como un array.
    // Devuelve un número entero, normalmente el ID del socio creado.
    public function registrarSocio(array $datos): int
    {
        // Comprueba si el número de socio está vacío o no fue enviado.
        if (empty($datos['numero_socio'])) {

            // Si está vacío, genera automáticamente un número de socio.
            $datos['numero_socio'] = $this->generarNumeroSocio();
        }

        // Envía los datos al Repository para crear el socio
        // en la base de datos.
        return $this->repository->crear($datos);
    }

    // Actualiza los datos de un socio existente.
    // Recibe el ID del socio y los nuevos datos.
    // Devuelve true si se actualizó correctamente o false si no.
    public function actualizarSocio(int $id, array $datos): bool
    {
        // Envía el ID y los datos al Repository.
        return $this->repository->actualizar($id, $datos);
    }

    // Cambia el estado de un socio.
    // El estado puede ser activo, inactivo o moroso.
    public function cambiarEstado(int $id, string $estado): bool
    {
        // Define los únicos estados permitidos para un socio.
        $estadosValidos = ['activo', 'inactivo', 'moroso'];

        // Comprueba si el estado recibido NO está dentro
        // de la lista de estados permitidos.
        //
        // in_array() busca un valor dentro de un array.
        // El true indica que también debe coincidir el tipo de dato.
        if (!in_array($estado, $estadosValidos, true)) {

            // Si el estado no es válido, lanza una excepción.
            throw new InvalidArgumentException(
                'Estado inválido. Use: activo, inactivo o moroso.'
            );
        }

        // Si el estado es válido, se lo envía al Repository
        // para actualizarlo en la base de datos.
        return $this->repository->cambiarEstado($id, $estado);
    }

    // Elimina un socio por su ID.
    // Devuelve true si se eliminó correctamente o false si no.
    public function eliminarSocio(int $id): bool
    {
        // Envía el ID al Repository para eliminar el socio.
        return $this->repository->eliminar($id);
    }

    // Función privada que genera automáticamente
    // un número de socio.
    //
    // Ejemplo:
    // S-2026-4837
    private function generarNumeroSocio(): string
    {
        // Genera un número con el siguiente formato:
        //
        // S-AAAA-NNNN
        //
        // S       = identifica que es un socio.
        // date('Y') = año actual.
        // random_int() = número aleatorio entre 1 y 9999.
        // str_pad() = completa con ceros hasta tener 4 números.
        return 'S-' . date('Y') . '-' .
            str_pad(
                (string) random_int(1, 9999),
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}

