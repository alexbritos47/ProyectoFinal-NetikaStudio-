<?php

class AuthController
{
    private AuthService $service;
    private AuthValidator $validator;

    public function __construct(AuthService $service, AuthValidator $validator)
    {
        $this->service = $service;
        $this->validator = $validator;
    }

    public function login(): void
    {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);
            return;
        }

        $errores = $this->validator->validarInicioSesion($datos);

        if (!empty($errores)) {
            http_response_code(400);
            echo json_encode(["errores" => $errores]);
            return;
        }

        try {
            $usuario = $this->service->login($datos['nombre_usuario'], $datos['contrasena']);
            echo json_encode(["mensaje" => "Inicio de sesión correcto", "usuario" => $usuario]);
        } catch (RuntimeException $e) {
            http_response_code(401);
            echo json_encode(["mensaje" => $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        $this->service->logout();
        echo json_encode(["mensaje" => "Sesión cerrada correctamente"]);
    }

    public function me(): void
    {
        $usuario = $this->service->usuarioActual();

        if (!$usuario) {
            http_response_code(401);
            echo json_encode(["mensaje" => "No hay una sesión activa"]);
            return;
        }

        echo json_encode($usuario);
    }
}
