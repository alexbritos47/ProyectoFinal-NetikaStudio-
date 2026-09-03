<?php

require_once __DIR__ . '/../services/UsuarioService.php';
require_once __DIR__ . '/../validators/UsuarioValidator.php';

class UsuarioController
{
    private UsuarioService $service;

    public function __construct()
    {
        $this->service = new UsuarioService();
    }


    // GET /api/usuarios
    public function listar()
    {
        try {

            $resultado = $this->service->listarUsuarios();

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Usuarios obtenidos correctamente',
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


    // GET /api/usuarios/{id}
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

            $resultado = $this->service->obtenerUsuario($id);

            if (!$resultado) {

                http_response_code(404);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Usuario no encontrado',
                    'errores' => []
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Usuario obtenido correctamente',
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


    // POST /api/usuarios
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
                UsuarioValidator::validarCrear($datos);

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
                $this->service->crearUsuario($datos);

            http_response_code(201);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Usuario creado correctamente',
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


    // PUT /api/usuarios/{id}
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
                UsuarioValidator::validarActualizar($datos);

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
                $this->service->actualizarUsuario(
                    $id,
                    $datos
                );

            if (!$resultado) {

                http_response_code(404);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Usuario no encontrado',
                    'errores' => []
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' =>
                    'Usuario actualizado correctamente',
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


    // DELETE /api/usuarios/{id}
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
                $this->service->eliminarUsuario($id);

            if (!$resultado) {

                http_response_code(404);

                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Usuario no encontrado',
                    'errores' => []
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'exito' => true,
                'mensaje' =>
                    'Usuario eliminado correctamente',
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