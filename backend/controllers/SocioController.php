<?php

require_once __DIR__ . '/../services/SocioService.php';
require_once __DIR__ . '/../validators/SocioValidator.php';

class SocioController
{
    private SocioService $service;

    public function __construct()
    {
        $this->service = new SocioService();
    }


    // GET /api/socios
    public function listar()
    {
        try {

            $resultado = $this->service->listarSocios();

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Socios obtenidos correctamente',
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


    // GET /api/socios/{id}
    public function obtener($id)
    {
        try {

            if (!is_numeric($id)) {

                http_response_code(400);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'El ID no es válido',
                    'errores' => []
                ]);

                return;
            }

            $resultado = $this->service->obtenerSocio($id);

            if (!$resultado) {

                http_response_code(404);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Socio no encontrado',
                    'errores' => []
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Socio obtenido correctamente',
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


    // POST /api/socios
    public function crear()
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
                    'mensaje' =>
                        'Los datos enviados no son válidos',
                    'errores' => []
                ]);

                return;
            }

            $errores =
                SocioValidator::validarCrear($datos);

            if (!empty($errores)) {

                http_response_code(400);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Datos inválidos',
                    'errores' => $errores
                ]);

                return;
            }

            $resultado =
                $this->service->crearSocio($datos);

            http_response_code(201);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Socio creado correctamente',
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


    // PUT /api/socios/{id}
    public function actualizar($id)
    {
        try {

            if (!is_numeric($id)) {

                http_response_code(400);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'El ID no es válido',
                    'errores' => []
                ]);

                return;
            }

            $datos = json_decode(
                file_get_contents('php://input'),
                true
            );

            if (!is_array($datos)) {

                http_response_code(400);

                echo json_encode([
                    'exito' => false,
                    'mensaje' =>
                        'Los datos enviados no son válidos',
                    'errores' => []
                ]);

                return;
            }

            $errores =
                SocioValidator::validarActualizar($datos);

            if (!empty($errores)) {

                http_response_code(400);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Datos inválidos',
                    'errores' => $errores
                ]);

                return;
            }

            $resultado =
                $this->service->actualizarSocio(
                    $id,
                    $datos
                );

            if (!$resultado) {

                http_response_code(404);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Socio no encontrado',
                    'errores' => []
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' =>
                    'Socio actualizado correctamente',
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


    // DELETE /api/socios/{id}
    public function eliminar($id)
    {
        try {

            if (!is_numeric($id)) {

                http_response_code(400);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'El ID no es válido',
                    'errores' => []
                ]);

                return;
            }

            $resultado =
                $this->service->eliminarSocio($id);

            if (!$resultado) {

                http_response_code(404);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Socio no encontrado',
                    'errores' => []
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' =>
                    'Socio eliminado correctamente',
                'datos' => []
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