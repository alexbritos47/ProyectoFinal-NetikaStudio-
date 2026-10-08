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

