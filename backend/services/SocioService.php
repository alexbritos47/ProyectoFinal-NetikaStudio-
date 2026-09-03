```php
<?php

class SocioService
{
    private $socioRepository;

    public function __construct($socioRepository)
    {
        $this->socioRepository = $socioRepository;
    }

    // Listar todos los socios
    public function listarSocios()
    {
        return $this->socioRepository->obtenerTodos();
    }

    // Obtener un socio por ID
    public function obtenerSocio($id)
    {
        $socio = $this->socioRepository->obtenerPorId($id);

        if (!$socio) {
            throw new Exception("El socio no existe");
        }

        return $socio;
    }

    // Crear un socio
    public function crearSocio($datos)
    {
        // Comprobar documento duplicado
        $socio = $this->socioRepository
            ->obtenerPorDocumento($datos['documento']);

        if ($socio) {
            throw new Exception("El documento ya está registrado");
        }

        // Si no se envía estado, queda activo
        if (!isset($datos['estado'])) {
            $datos['estado'] = 'activo';
        }

        // Comprobar que el estado sea válido
        if (
            $datos['estado'] !== 'activo' &&
            $datos['estado'] !== 'inactivo'
        ) {
            throw new Exception("El estado no es válido");
        }

        // Crear socio
        $id = $this->socioRepository->crear($datos);

        // Obtener el socio creado
        return $this->socioRepository->obtenerPorId($id);
    }

    // Actualizar un socio
    public function actualizarSocio($id, $datos)
    {
        // Comprobar que el socio exista
        $socio = $this->socioRepository->obtenerPorId($id);

        if (!$socio) {
            throw new Exception("El socio no existe");
        }

        // Comprobar documento duplicado
        if (isset($datos['documento'])) {

            $socioDocumento = $this->socioRepository
                ->obtenerPorDocumento($datos['documento']);

            if (
                $socioDocumento &&
                $socioDocumento['id'] != $id
            ) {
                throw new Exception("El documento ya está registrado");
            }
        }

        // Comprobar estado
        if (isset($datos['estado'])) {

            if (
                $datos['estado'] !== 'activo' &&
                $datos['estado'] !== 'inactivo'
            ) {
                throw new Exception("El estado no es válido");
            }
        }

        // Actualizar socio
        $this->socioRepository->actualizar($id, $datos);

        // Devolver socio actualizado
        return $this->socioRepository->obtenerPorId($id);
    }

    // Eliminar un socio
    public function eliminarSocio($id)
    {
        // Comprobar que exista
        $socio = $this->socioRepository->obtenerPorId($id);

        if (!$socio) {
            throw new Exception("El socio no existe");
        }

        // Eliminar
        $this->socioRepository->eliminar($id);

        return true;
    }
}
