<?php

// Clase encargada de manejar la lógica relacionada con los socios.
class SocioService
{
    // Guarda una instancia de SocioRepository.
    // El Repository es el encargado de comunicarse con la base de datos.
    private SocioRepository $repository;

    // Sirve para crear o comprobar el usuario con el que el socio
    // entra al portal. Es opcional para no romper código anterior.
    private ?UsuarioRepository $usuarioRepository;

    // Constructor de la clase.
    public function __construct(
        SocioRepository $repository,
        ?UsuarioRepository $usuarioRepository = null
    ) {
        $this->repository = $repository;
        $this->usuarioRepository = $usuarioRepository;
    }

    // Obtiene todos los socios.
    public function obtenerSocios(): array
    {
        return $this->repository->obtenerTodos();
    }

    // Obtiene un socio por su ID.
    public function obtenerSocio(int $id): array|false
    {
        return $this->repository->obtenerPorId($id);
    }

    // Registra un nuevo socio.
    //
    // Hay tres formas de usarlo:
    //  1. Solo el socio (sin acceso al portal).
    //  2. Con nombre_usuario + contrasena: crea también el usuario
    //     del portal (rol socio) y lo vincula. Todo en una transacción,
    //     así no queda un usuario suelto si falla el alta del socio.
    //  3. Con id_usuario: vincula un usuario que ya existe.
    //
    // Devuelve el ID del socio creado.
    // El usuario creado (si lo hay) queda en $this->ultimoIdUsuario.
    public function registrarSocio(array $datos): int
    {
        if (empty($datos['numero_socio'])) {

            $datos['numero_socio'] = $this->generarNumeroSocio();
        }

        $this->ultimoIdUsuario = null;

        // Caso 2: crear usuario y socio juntos.
        if (!empty($datos['nombre_usuario'])) {

            return $this->repository->transaccion(function () use ($datos) {

                $datos['id_usuario'] = $this->crearUsuarioDelSocio($datos);

                $this->ultimoIdUsuario = $datos['id_usuario'];

                return $this->repository->crear($datos);
            });
        }

        // Caso 3: vincular un usuario existente.
        if (!empty($datos['id_usuario'])) {

            $this->comprobarUsuarioVinculable((int) $datos['id_usuario'], null);

            $this->ultimoIdUsuario = (int) $datos['id_usuario'];
        }

        // Caso 1 (y 3 ya validado): solo se crea el socio.
        return $this->repository->crear($datos);
    }

    // ID del usuario del portal creado o vinculado en el último alta.
    public ?int $ultimoIdUsuario = null;

    // Actualiza los datos de un socio.
    // Si se manda id_usuario, se comprueba que se pueda vincular.
    // Si se manda id_usuario = null, el socio queda sin acceso al portal.
    public function actualizarSocio(int $id, array $datos): bool
    {
        if (!empty($datos['id_usuario'])) {

            $this->comprobarUsuarioVinculable((int) $datos['id_usuario'], $id);
        }

        return $this->repository->actualizar($id, $datos);
    }

    // Cambia el estado de un socio (activo, inactivo o moroso).
    public function cambiarEstado(int $id, string $estado): bool
    {
        $estadosValidos = ['activo', 'inactivo', 'moroso'];

        if (!in_array($estado, $estadosValidos, true)) {

            throw new InvalidArgumentException(
                'Estado inválido. Use: activo, inactivo o moroso.'
            );
        }

        return $this->repository->cambiarEstado($id, $estado);
    }

    // Elimina un socio.
    public function eliminarSocio(int $id): bool
    {
        return $this->repository->eliminar($id);
    }

    // Crea el usuario del portal para un socio nuevo y devuelve su ID.
    private function crearUsuarioDelSocio(array $datos): int
    {
        $usuarios = $this->exigirUsuarioRepository();

        if ($usuarios->obtenerPorNombreUsuario($datos['nombre_usuario'])) {

            throw new RuntimeException(
                'Ya existe un usuario con ese nombre de usuario.'
            );
        }

        $email = !empty($datos['email']) ? $datos['email'] : null;

        if ($email !== null && $usuarios->obtenerPorCorreo($email)) {

            throw new RuntimeException(
                'Ya existe un usuario con ese correo.'
            );
        }

        return $usuarios->crear([
            'nombre_usuario'  => $datos['nombre_usuario'],
            'contrasena'      => $datos['contrasena'],
            'nombre_completo' => trim($datos['nombre'] . ' ' . $datos['apellido']),
            'rol'             => 'socio',
            'email'           => $email,
            'estado'          => 'activo',
        ]);
    }

    // Comprueba que un usuario existente se pueda vincular a un socio:
    //  - tiene que existir,
    //  - tiene que tener rol socio,
    //  - no puede estar ya vinculado a OTRO socio.
    // $idSocioActual es el socio que se está editando (o null si es nuevo).
    private function comprobarUsuarioVinculable(
        int $idUsuario,
        ?int $idSocioActual
    ): void {

        $usuarios = $this->exigirUsuarioRepository();

        $usuario = $usuarios->obtenerPorId($idUsuario);

        if (!$usuario) {

            throw new InvalidArgumentException(
                'El usuario indicado no existe.'
            );
        }

        if ($usuario['rol'] !== 'socio') {

            throw new InvalidArgumentException(
                'El usuario indicado debe tener rol socio.'
            );
        }

        $vinculado = $this->repository->obtenerPorUsuario($idUsuario);

        if ($vinculado && (int) $vinculado['id_socio'] !== $idSocioActual) {

            throw new RuntimeException(
                'Ese usuario ya está vinculado a otro socio.'
            );
        }
    }

    private function exigirUsuarioRepository(): UsuarioRepository
    {
        if ($this->usuarioRepository === null) {

            throw new LogicException(
                'SocioService no recibió el UsuarioRepository.'
            );
        }

        return $this->usuarioRepository;
    }

    // Genera un número de socio automático.
    // Ejemplo: S-2026-0457
    private function generarNumeroSocio(): string
    {
        return 'S-' . date('Y') . '-' .
            str_pad(
                (string) random_int(1, 9999),
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}