<?php

class NotificacionController
{
    private NotificacionService $service;
    private NotificacionValidator $validator;

    public function __construct(NotificacionService $service, NotificacionValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    public function listar(): void
    {
        $idSocio = isset($_GET['id_socio']) ? (int) $_GET['id_socio'] : null;

        if (!$idSocio) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Debe indicar id_socio"]);
            return;
        }

        echo json_encode($this->service->obtenerPorSocio($idSocio));
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

        $id = $this->service->enviar((int) $datos['id_socio'], $datos['titulo'], $datos['mensaje']);

        http_response_code(201);
        echo json_encode(["mensaje" => "Notificación enviada correctamente", "id_notificacion" => $id]);
    }

    public function marcarLeida(int $id): void
    {
        $notificacion = $this->service->marcarLeida($id);

        if (!$notificacion) {
            http_response_code(404);
            echo json_encode(["mensaje" => "Notificación no encontrada"]);
            return;
        }

        echo json_encode($notificacion);
    }
}
