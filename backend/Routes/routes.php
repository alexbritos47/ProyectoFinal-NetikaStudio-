<?php

function manejarRutas(
    string $method,
    string $uri,
    SocioController $socioController
): void {

    $uri = trim($uri, '/');

    $partes = explode('/', $uri);

    if ($partes[0] !== 'api' || $partes[1] !== 'socios') {
        http_response_code(404);

        echo json_encode([
            "mensaje" => "Ruta no encontrada"
        ]);

        return;
    }

    $id = isset($partes[2]) ? (int) $partes[2] : null;

    switch ($method) {

        case 'GET':

            if ($id !== null) {
                $socioController->obtener($id);
            } else {
                $socioController->listar();
            }

            break;

        case 'POST':

            $socioController->crear();

            break;

        case 'PUT':

            if ($id === null) {
                http_response_code(400);

                echo json_encode([
                    "mensaje" => "Debe especificar un ID"
                ]);

                return;
            }

            $socioController->actualizar($id);

            break;

        case 'DELETE':

            if ($id === null) {
                http_response_code(400);

                echo json_encode([
                    "mensaje" => "Debe especificar un ID"
                ]);

                return;
            }

            $socioController->eliminar($id);

            break;

        default:

            http_response_code(405);

            echo json_encode([
                "mensaje" => "Método HTTP no permitido"
            ]);
    }
}