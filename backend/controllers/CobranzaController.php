<?php

class CobranzaController
{
    private CobranzaService $service;
    private CobranzaValidator $validator;

    public function __construct(CobranzaService $service, CobranzaValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    public function pendientes(): void
    {
        $idCobrador = isset($_GET['id_cobrador']) ? (int) $_GET['id_cobrador'] : null;
        $fecha = $_GET['fecha'] ?? null;

        echo json_encode($this->service->obtenerPendientes($idCobrador, $fecha));
    }

    public function registrarVisita(): void
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);
            return;
        }

        $errores = $this->validator->validarVisita($datos);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(["errores" => $errores]);
            return;
        }

        try {
            $id = $this->service->registrarVisita(
                (int) $datos['id_socio'],
                (int) $datos['id_cobrador'],
                $datos['resultado'],
                $datos['observacion'] ?? null
            );

            http_response_code(201);
            echo json_encode(["mensaje" => "Visita registrada correctamente", "id_visita" => $id]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["mensaje" => $e->getMessage()]);
        }
    }
}
