<?php

class SocioController
{
    private SocioService $service;
    private SocioValidator $validator;

    public function __construct(
        SocioService $service,
        SocioValidator $validator
    ) {
        $this->service = $service;
        $this->validator = $validator;
    }

    public function listar(): void
    {
        $socios = $this->service->obtenerSocios();

        echo json_encode($socios);
    }

    public function obtener(int $id): void
    {
        $socio = $this->service->obtenerSocio($id);

        if (!$socio) {
            http_response_code(404);

            echo json_encode([
                "mensaje" => "Socio no encontrado"
            ]);

            return;
        }

        echo json_encode($socio);
    }

    public function crear(): void
    {
        $datos = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($datos)) {
            http_response_code(400);

            echo json_encode([
                "mensaje" => "Los datos enviados no son válidos"
            ]);

            return;
        }

        $errores = $this->validator->validar($datos);

        if (!empty($errores)) {
            http_response_code(400);

            echo json_encode([
                "errores" => $errores
            ]);

            return;
        }

        $this->service->registrarSocio($datos);

        http_response_code(201);

        echo json_encode([
            "mensaje" => "Socio registrado correctamente"
        ]);
    }

    public function actualizar(int $id): void
    {
        $datos = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($datos)) {
            http_response_code(400);

            echo json_encode([
                "mensaje" => "Los datos enviados no son válidos"
            ]);

            return;
        }

        $errores = $this->validator->validar($datos);

        if (!empty($errores)) {
            http_response_code(400);

            echo json_encode([
                "errores" => $errores
            ]);

            return;
        }

        $this->service->actualizarSocio($id, $datos);

        echo json_encode([
            "mensaje" => "Socio actualizado correctamente"
        ]);
    }

    public function eliminar(int $id): void
    {
        $this->service->eliminarSocio($id);

        echo json_encode([
            "mensaje" => "Socio eliminado correctamente"
        ]);
    }
}