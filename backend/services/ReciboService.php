<?php

// Clase encargada de manejar la lógica relacionada con los recibos.
class ReciboService
{
    // Repository encargado de trabajar con la tabla de recibos.
    private ReciboRepository $repository;

    // Repository encargado de consultar los socios.
    private SocioRepository $socios;

    // Repository encargado de consultar las categorías.
    private CategoriaRepository $categorias;


    // Constructor de la clase.
    // Recibe los tres Repository que necesita este Service.
    public function __construct(
        ReciboRepository $repository,
        SocioRepository $socios,
        CategoriaRepository $categorias
    ) {
        // Guarda el ReciboRepository.
        $this->repository = $repository;

        // Guarda el SocioRepository.
        $this->socios = $socios;

        // Guarda el CategoriaRepository.
        $this->categorias = $categorias;
    }


    // Obtiene todos los recibos.
    // Devuelve un array.
    public function obtenerTodos(): array
    {
        // Llama al Repository para obtener todos los recibos.
        return $this->repository->obtenerTodos();
    }


    // Busca un recibo por su ID.
    // Puede devolver un array o false si no existe.
    // Lista recibos con filtros opcionales que llegan de la URL:
    //   ?id_socio=3  ?estado=pendiente  ?periodo=2026-09  ?anio=2026
    // Un filtro con formato inválido lanza InvalidArgumentException
    // (el controller lo convierte en un error 400).
    public function obtenerFiltrados(array $parametros): array
    {
        $filtros = [];

        $idSocio = $this->textoFiltro($parametros, 'id_socio');
        if ($idSocio !== null) {
            if (!ctype_digit($idSocio)) {
                throw new InvalidArgumentException('id_socio debe ser un número.');
            }
            $filtros['id_socio'] = (int) $idSocio;
        }

        $estado = $this->textoFiltro($parametros, 'estado');
        if ($estado !== null) {
            if (!in_array($estado, ['pendiente', 'pagado', 'anulado'], true)) {
                throw new InvalidArgumentException(
                    'estado debe ser pendiente, pagado o anulado.'
                );
            }
            $filtros['estado'] = $estado;
        }

        $periodo = $this->textoFiltro($parametros, 'periodo');
        if ($periodo !== null) {
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo)) {
                throw new InvalidArgumentException(
                    'periodo debe tener el formato AAAA-MM (ejemplo: 2026-09).'
                );
            }
            $filtros['periodo'] = $periodo;
        }

        $anio = $this->textoFiltro($parametros, 'anio');
        if ($anio !== null) {
            if (!preg_match('/^\d{4}$/', $anio)) {
                throw new InvalidArgumentException(
                    'anio debe tener 4 dígitos (ejemplo: 2026).'
                );
            }
            $filtros['anio'] = $anio;
        }

        return $this->repository->obtenerFiltrados($filtros);
    }

    // Devuelve el valor de un parámetro como texto, o null si no vino o está vacío.
    // Rechaza valores que no sean texto (por ejemplo ?estado[]=x).
    private function textoFiltro(array $parametros, string $clave): ?string
    {
        if (!isset($parametros[$clave]) || $parametros[$clave] === '') {
            return null;
        }

        if (!is_string($parametros[$clave])) {
            throw new InvalidArgumentException("El filtro $clave no es válido.");
        }

        return trim($parametros[$clave]);
    }


    public function obtenerPorId(int $id): array|false
    {
        // Envía el ID al Repository para realizar la búsqueda.
        return $this->repository->obtenerPorId($id);
    }


    // Obtiene todos los recibos correspondientes a un socio.
    // Recibe el ID del socio.
    // Devuelve un array.
    public function obtenerPorSocio(int $idSocio): array
    {
        // Llama al Repository para buscar los recibos del socio.
        return $this->repository->obtenerPorSocio($idSocio);
    }


    /**
     * Genera los 12 recibos mensuales del año para todos los socios activos.
     * Si ya existe un recibo para ese socio y período, no lo vuelve a crear.
     */
    public function generarAnuales(int $anio): array
    {
        // Obtiene todos los socios que tienen estado activo.
        $sociosActivos = $this->socios->obtenerActivos();

        // Crea un array vacío donde se guardarán las categorías
        // organizadas por su ID.
        $categoriasPorId = [];

        // Recorre todas las categorías existentes.
        foreach ($this->categorias->obtenerTodos() as $categoria) {

            // Guarda cada categoría utilizando su ID como clave.
            // Ejemplo:
            // $categoriasPorId[1] = categoría 1
            // $categoriasPorId[2] = categoría 2
            $categoriasPorId[$categoria['id_categoria']] = $categoria;
        }


        // Contador de recibos que fueron generados.
        $generados = 0;

        // Contador de recibos que no fueron creados
        // porque ya existían.
        $omitidos = 0;


        // Recorre todos los socios activos.
        foreach ($sociosActivos as $socio) {

            // Busca la categoría correspondiente al socio.
            // Si no encuentra la categoría, utiliza null.
            $categoria = $categoriasPorId[$socio['id_categoria']] ?? null;

            // Obtiene el monto de la cuota de la categoría.
            // Si no existe, utiliza 0.
            $importe = $categoria['monto_cuota'] ?? 0;


            // Recorre los 12 meses del año.
            // Comienza en enero (1) y termina en diciembre (12).
            for ($mes = 1; $mes <= 12; $mes++) {

                // Genera el período con formato:
                // YYYY-MM
                //
                // Ejemplo:
                // 2026-01
                // 2026-02
                // 2026-03
                $periodo = sprintf('%04d-%02d', $anio, $mes);


                // Comprueba si ya existe un recibo
                // para ese socio y ese período.
                if ($this->repository->existePeriodo((int) $socio['id_socio'], $periodo)) {

                    // Si ya existe, aumenta el contador de omitidos.
                    $omitidos++;

                    // Salta al siguiente mes sin crear otro recibo.
                    continue;
                }


                // Crea un nuevo recibo.
                $this->repository->crear([

                    // Genera automáticamente el número del recibo.
                    'numero_recibo' =>
                        $this->generarNumeroRecibo(
                            $anio,
                            (int) $socio['id_socio'],
                            $mes
                        ),

                    // Guarda el ID del socio.
                    'id_socio' => $socio['id_socio'],

                    // Guarda el período correspondiente.
                    'periodo' => $periodo,

                    // Guarda el importe de la cuota.
                    'importe' => $importe,

                    // Genera la fecha de vencimiento.
                    // En este caso, el vencimiento es el día 10 de cada mes.
                    'fecha_vencimiento' =>
                        sprintf('%04d-%02d-10', $anio, $mes),
                ]);


                // Aumenta el contador de recibos generados.
                $generados++;
            }
        }


        // Devuelve un array con el resultado de la operación.
        //
        // "generados" indica cuántos recibos se crearon.
        // "omitidos" indica cuántos no se crearon porque ya existían.
        return [
            'generados' => $generados,
            'omitidos' => $omitidos
        ];
    }


    /**
     * Genera los recibos de un socio recién creado.
     *
     * Crea un recibo por cada mes, desde el mes de su fecha de ingreso
     * hasta diciembre de ese mismo año. El importe es la cuota de su
     * categoría y el vencimiento es el día 10 de cada mes (igual que en
     * generarAnuales). Si ya existe un recibo para un período, lo omite.
     *
     * No abre transacción: la abre quien lo llama (SocioService), para que
     * el alta del socio y sus recibos se guarden juntos o no se guarde nada.
     */
    public function generarParaSocio(
        int $idSocio,
        int $idCategoria,
        string $fechaIngreso
    ): array {

        $categoria = $this->categorias->obtenerPorId($idCategoria);

        if (!$categoria) {

            throw new InvalidArgumentException(
                'La categoría indicada no existe.'
            );
        }

        $importe = $categoria['monto_cuota'];

        $marca = strtotime($fechaIngreso);

        if ($marca === false) {

            throw new InvalidArgumentException(
                'La fecha de ingreso no es válida.'
            );
        }

        $anio = (int) date('Y', $marca);
        $mesInicial = (int) date('n', $marca);

        $generados = 0;
        $omitidos = 0;

        for ($mes = $mesInicial; $mes <= 12; $mes++) {

            $periodo = sprintf('%04d-%02d', $anio, $mes);

            if ($this->repository->existePeriodo($idSocio, $periodo)) {

                $omitidos++;
                continue;
            }

            $this->repository->crear([
                'numero_recibo'     => $this->generarNumeroRecibo($anio, $idSocio, $mes),
                'id_socio'          => $idSocio,
                'periodo'           => $periodo,
                'importe'           => $importe,
                'fecha_vencimiento' => sprintf('%04d-%02d-10', $anio, $mes),
            ]);

            $generados++;
        }

        return [
            'generados' => $generados,
            'omitidos'  => $omitidos,
            'desde'     => sprintf('%04d-%02d', $anio, $mesInicial),
            'hasta'     => sprintf('%04d-12', $anio),
        ];
    }


    // Anula un recibo existente.
    // Recibe el ID del recibo.
    // Devuelve true o false.
    public function anular(int $id): bool
    {
        // Envía el ID al Repository para realizar la anulación.
        return $this->repository->anular($id);
    }


    // Función privada que genera automáticamente
    // el número de un recibo.
    //
    // Por ejemplo:
    // R-2026-01-S15
    private function generarNumeroRecibo(
        int $anio,
        int $idSocio,
        int $mes
    ): string
    {
        // Genera el número utilizando el año,
        // el mes y el ID del socio.
        return sprintf(
            'R-%04d-%02d-S%d',
            $anio,
            $mes,
            $idSocio
        );
    }
}