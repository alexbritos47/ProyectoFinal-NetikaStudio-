<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../validators/AuthValidator.php';

class AuthController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function iniciarSesion()
    {
        try {

            $datos = json_decode(
                file_get_contents('php://input'),
                true
            );

            if (!is_array($datos)) {
                http_response_code(400);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Los datos enviados no son válidos',
                    'errores' => []
                ]);

                return;
            }

            $errores = AuthValidator::validar($datos);

            if (!empty($errores)) {

                http_response_code(400);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Datos inválidos',
                    'errores' => $errores
                ]);

                return;
            }

            $resultado = $this->service->iniciarSesion(
                $datos['correo'],
                $datos['contrasena']
            );

            if (!$resultado) {

                http_response_code(401);

                echo json_encode([
                    'exito' => false,
                    'mensaje' =>
                        'Correo o contraseña incorrectos',
                    'errores' => []
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Inicio de sesión exitoso',
                'datos' => $resultado
            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error interno del servidor',
                'errores' => []
            ]);
        }
    }
}