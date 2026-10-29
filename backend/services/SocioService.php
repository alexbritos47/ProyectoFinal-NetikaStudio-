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

    // Sirve para generar los recibos del socio al darlo de alta.
    // Es opcional para no romper código anterior.
    private ?ReciboService $reciboService;

    // Constructor de la clase.
    public function __construct(
        SocioRepository $repository,
        ?UsuarioRepository $usuarioRepository = null,
        ?ReciboService $reciboService = null
    ) {
        $this->repository = $repository;
        $this->usuarioRepository = $usuarioRepository;
        $this->reciboService = $reciboService;
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
    //     del portal (rol socio) y lo vincula.
    //  3. Con id_usuario: vincula un usuario que ya existe.
    //
    // Todo ocurre en UNA transacción: el usuario (si se crea), el socio y
    // sus recibos. Si algo falla no queda nada a medias.
    //
    // Si el socio se crea como activo, se le generan automáticamente los
    // recibos desde su mes de ingreso hasta diciembre de ese año.
    //
    // Devuelve el ID del socio creado.
    // El usuario creado (si lo hay) queda en $this->ultimoIdUsuario.
    // El resumen de recibos queda en $this->ultimosRecibos.
    public function registrarSocio(array $datos): int
    {
        if (empty($datos['numero_socio'])) {

            $datos['numero_socio'] = $this->generarNumeroSocio();
        }

        // Se completan aquí los valores por defecto que usa el Repository,
        // para generar los recibos con los mismos datos que se guardan.
        $datos['fecha_ingreso'] = $datos['fecha_ingreso'] ?? date('Y-m-d');
        $datos['estado'] = $datos['estado'] ?? 'activo';

        $this->ultimoIdUsuario = null;
        $this->ultimosRecibos = null;

        return $this->repository->transaccion(function () use ($datos) {

            // Caso 2: crear usuario y socio juntos.
            if (!empty($datos['nombre_usuario'])) {

                $datos['id_usuario'] = $this->crearUsuarioDelSocio($datos);

                $this->ultimoIdUsuario = $datos['id_usuario'];

            // Caso 3: vincular un usuario existente.
            } elseif (!empty($datos['id_usuario'])) {

                $this->comprobarUsuarioVinculable((int) $datos['id_usuario'], null);

                $this->ultimoIdUsuario = (int) $datos['id_usuario'];
            }

            $idSocio = $this->repository->crear($datos);

            // Recibos automáticos (solo para socios activos).
            if ($this->reciboService !== null && $datos['estado'] === 'activo') {

                $this->ultimosRecibos = $this->reciboService->generarParaSocio(
                    $idSocio,
                    (int) $datos['id_categoria'],
                    $datos['fecha_ingreso']
                );
            }

            return $idSocio;
        });
    }

    // ID del usuario del portal creado o vinculado en el último alta.
    public ?int $ultimoIdUsuario = null;

    // Resumen de los recibos generados en el último alta
    // (null si no se generó ninguno).
    public ?array $ultimosRecibos = null;

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

        // MySQL no avisa si el UPDATE no encontró el socio, así que se comprueba antes.
        if (!$this->repository->obtenerPorId($id)) {

            throw new OutOfBoundsException('El socio no existe.');
        }

        return $this->repository->cambiarEstado($id, $estado);
    }

    // Arma el historial de un socio para la pantalla del administrador:
    // recibos, pagos y visitas de cobranza, más un resumen con totales.
    // Lanza OutOfBoundsException si el socio no existe (404).
    public function obtenerHistorial(int $id): array
    {
        if (!$this->repository->obtenerPorId($id)) {

            throw new OutOfBoundsException('El socio no existe.');
        }

        $historial = $this->repository->historial($id);

        $recibos = $historial['recibos'];
        $pagos   = $historial['pagos'];
        $visitas = $historial['visitas'];

        $pendientes = 0;
        $pagados = 0;

        foreach ($recibos as $recibo) {

            if ($recibo['estado'] === 'pendiente') {
                $pendientes++;
            }

            if ($recibo['estado'] === 'pagado') {
                $pagados++;
            }
        }

        // Los pagos anulados no suman al total cobrado.
        $totalCobrado = 0.0;

        foreach ($pagos as $pago) {

            if ((int) $pago['anulado'] === 0) {
                $totalCobrado += (float) $pago['monto_total'];
            }
        }

        return [
            'recibos' => $recibos,
            'pagos'   => $pagos,
            'visitas' => $visitas,
            'resumen' => [
                'recibos_pendientes' => $pendientes,
                'recibos_pagados'    => $pagados,
                'total_cobrado'      => $totalCobrado,
                'cantidad_pagos'     => count($pagos),
                'cantidad_visitas'   => count($visitas),
            ],
            // Misma regla que usa eliminarSocio().
            'puede_eliminarse' => !$this->repository->tieneMovimientos($id),
        ];
    }


    // Elimina un socio.
    //
    // Al dar de alta un socio activo se le generan recibos automáticos, y la
    // base no deja borrar un socio que tenga recibos. Por eso se hace todo en
    // UNA transacción:
    //   1. Si el socio ya tiene pagos, visitas de cobranza o recibos pagados,
    //      NO se borra (es historial contable): se avisa que lo pase a inactivo.
    //   2. Si no, se borran sus recibos sin cobrar, el socio y su usuario
    //      del portal (para que no quede un login huérfano).
    //
    // Lanza OutOfBoundsException si el socio no existe (404) y
    // RuntimeException si tiene historial (409).
    public function eliminarSocio(int $id): void
    {
        $socio = $this->repository->obtenerPorId($id);

        if (!$socio) {

            throw new OutOfBoundsException('El socio no existe.');
        }

        $this->repository->transaccion(function () use ($id, $socio) {

            if ($this->repository->tieneMovimientos($id)) {

                throw new RuntimeException(
                    'No se puede eliminar: el socio tiene pagos o cobranzas registrados. '
                    . 'Para conservar el historial, pasalo a estado inactivo.'
                );
            }

            $this->repository->eliminarRecibos($id);
            $this->repository->eliminar($id);

            // Borra también el usuario del portal, pero solo si tiene rol socio.
            if (!empty($socio['id_usuario']) && $this->usuarioRepository !== null) {

                $usuario = $this->usuarioRepository->obtenerPorId((int) $socio['id_usuario']);

                if ($usuario && $usuario['rol'] === 'socio') {

                    $this->usuarioRepository->eliminar((int) $socio['id_usuario']);
                }
            }
        });
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