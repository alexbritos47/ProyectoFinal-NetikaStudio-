<?php

class ReciboController
{
    private ReciboService $service;

    public function __construct(ReciboService $service)
    {
        $this->service = $service;
    }

    public function listar(): void
    {
        echo json_encode($this->service->obtenerTodos());
    }

    public function obtener(int $id): void
    {
        $recibo = $this->service->obtenerPorId($id);

        if (!$recibo) {
            http_response_code(404);
            echo json_encode(["mensaje" => "Recibo no encontrado"]);
            return;
        }

        echo json_encode($recibo);
    }

    public function porSocio(int $idSocio): void
    {
        echo json_encode($this->service->obtenerPorSocio($idSocio));
    }

    public function generarAnuales(): void
    {
        $datos = json_decode(file_get_contents("php://input"), true) ?? [];
        $anio = (int) ($datos['anio'] ?? date('Y'));

        $resultado = $this->service->generarAnuales($anio);

        http_response_code(201);
        echo json_encode([
            "mensaje" => "Recibos generados correctamente",
            "anio" => $anio,
        ] + $resultado);
    }

    public function anular(int $id): void
    {
        $this->service->anular($id);
        echo json_encode(["mensaje" => "Recibo anulado correctamente"]);
    }
}
