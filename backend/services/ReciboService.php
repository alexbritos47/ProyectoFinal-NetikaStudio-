<?php

class ReciboService
{
    private ReciboRepository $repository;
    private SocioRepository $socios;
    private CategoriaRepository $categorias;

    public function __construct(
        ReciboRepository $repository,
        SocioRepository $socios,
        CategoriaRepository $categorias
    ) {
        $this->repository = $repository;
        $this->socios = $socios;
        $this->categorias = $categorias;
    }

    public function obtenerTodos(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function obtenerPorId(int $id): array|false
    {
        return $this->repository->obtenerPorId($id);
    }

    public function obtenerPorSocio(int $idSocio): array
    {
        return $this->repository->obtenerPorSocio($idSocio);
    }

    /**
     * Genera los 12 recibos mensuales del año para todos los socios activos.
     * Si ya existe un recibo para ese socio/período, no se duplica.
     */
    public function generarAnuales(int $anio): array
    {
        $sociosActivos = $this->socios->obtenerActivos();
        $categoriasPorId = [];
        foreach ($this->categorias->obtenerTodos() as $categoria) {
            $categoriasPorId[$categoria['id_categoria']] = $categoria;
        }

        $generados = 0;
        $omitidos = 0;

        foreach ($sociosActivos as $socio) {
            $categoria = $categoriasPorId[$socio['id_categoria']] ?? null;
            $importe = $categoria['monto_cuota'] ?? 0;

            for ($mes = 1; $mes <= 12; $mes++) {
                $periodo = sprintf('%04d-%02d', $anio, $mes);

                if ($this->repository->existePeriodo((int) $socio['id_socio'], $periodo)) {
                    $omitidos++;
                    continue;
                }

                $this->repository->crear([
                    'numero_recibo'     => $this->generarNumeroRecibo($anio, (int) $socio['id_socio'], $mes),
                    'id_socio'          => $socio['id_socio'],
                    'periodo'           => $periodo,
                    'importe'           => $importe,
                    'fecha_vencimiento' => sprintf('%04d-%02d-10', $anio, $mes),
                ]);

                $generados++;
            }
        }

        return ['generados' => $generados, 'omitidos' => $omitidos];
    }

    public function anular(int $id): bool
    {
        return $this->repository->anular($id);
    }

    private function generarNumeroRecibo(int $anio, int $idSocio, int $mes): string
    {
        return sprintf('R-%04d-%02d-S%d', $anio, $mes, $idSocio);
    }
}
