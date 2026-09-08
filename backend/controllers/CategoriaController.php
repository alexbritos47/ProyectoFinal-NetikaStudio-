<?php

class CategoriaController
{
    private CategoriaService $service;
    private CategoriaValidator $validator;

    public function __construct(CategoriaService $service, CategoriaValidator $validator)
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
        $categoria = $this->service->obtenerPorId($id);

        if (!$categoria) {
            http_response_code(404);
            echo json_encode(["mensaje" => "Categoría no encontrada"]);
            return;
        }

        echo json_encode($categoria);
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

        $id = $this->service->crear($datos);

        http_response_code(201);
        echo json_encode(["mensaje" => "Categoría creada correctamente", "id_categoria" => $id]);
    }

    public function actualizar(int $id): void
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);
            return;
        }

        $errores = $this->validator->validarActualizar($datos);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(["errores" => $errores]);
            return;
        }

        $this->service->actualizar($id, $datos);
        echo json_encode(["mensaje" => "Categoría actualizada correctamente"]);
    }

    public function eliminar(int $id): void
    {
        $this->service->eliminar($id);
        echo json_encode(["mensaje" => "Categoría eliminada correctamente"]);
    }
}
