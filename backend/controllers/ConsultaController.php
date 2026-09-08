<?php

class ConsultaController
{
    private ConsultaService $service;
    private ConsultaValidator $validator;

    public function __construct(ConsultaService $service, ConsultaValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    public function listar(): void
    {
        echo json_encode($this->service->obtenerTodas());
    }

    public function obtener(int $id): void
    {
        $consulta = $this->service->obtenerPorId($id);

        if (!$consulta) {
            http_response_code(404);
            echo json_encode(["mensaje" => "Consulta no encontrada"]);
            return;
        }

        echo json_encode($consulta);
    }

    public function crear(): void
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);
            return;
        }

        $errores = $this->validator->validarCrear($datos);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(["errores" => $errores]);
            return;
        }

        $id = $this->service->crear((int) $datos['id_socio'], $datos['asunto'], $datos['mensaje']);

        http_response_code(201);
        echo json_encode(["mensaje" => "Consulta enviada correctamente", "id_consulta" => $id]);
    }
}
