<?php

class PagoController
{
    private PagoService $service;
    private PagoValidator $validator;

    public function __construct(PagoService $service, PagoValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    public function listar(): void
    {
        echo json_encode($this->service->obtenerTodos());
    }

    public function obtener(int $id): void
    {
        $pago = $this->service->obtenerPorId($id);

        if (!$pago) {
            http_response_code(404);
            echo json_encode(["mensaje" => "Pago no encontrado"]);
            return;
        }

        echo json_encode($pago);
    }

    // Registra un pago que cancela uno o varios recibos (endpoint usado por
    // administración y por el panel del cobrador para cobros a domicilio).
    public function crear(): void
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);
            return;
        }

        $errores = $this->validator->validarRegistrar($datos);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(["errores" => $errores]);
            return;
        }

        try {
            $idPago = $this->service->registrarPago(
                (int) $datos['id_socio'],
                $datos['recibos'],
                isset($datos['id_usuario']) ? (int) $datos['id_usuario'] : null,
                $datos['metodo_pago'] ?? null
            );

            http_response_code(201);
            echo json_encode(["mensaje" => "Pago registrado correctamente", "id_pago" => $idPago]);
        } catch (InvalidArgumentException | RuntimeException $e) {
            http_response_code(400);
            echo json_encode(["mensaje" => $e->getMessage()]);
        }
    }

    public function eliminar(int $id): void
    {
        $this->service->anular($id);
        echo json_encode(["mensaje" => "Pago anulado correctamente"]);
    }
}
