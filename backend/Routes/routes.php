<?php

/**
 * Enrutador simple para la API del Club Ciclista Maragato.
 * Cubre los endpoints definidos en 04-api.md / sección 4 del proyecto.
 */
function manejarRutas(string $method, string $uri, array $controladores): void
{
    $uri = trim(parse_url($uri, PHP_URL_PATH) ?? $uri, '/');
    $partes = explode('/', $uri);

    // Todas las rutas viven bajo /api/...
    if (($partes[0] ?? '') !== 'api') {
        responderNoEncontrado();
        return;
    }

    $recurso = $partes[1] ?? null;
    $sub1 = $partes[2] ?? null;
    $sub2 = $partes[3] ?? null;
    $sub3 = $partes[4] ?? null;

    /** @var AuthController $auth */
    $auth = $controladores['auth'];
    /** @var UsuarioController $usuarios */
    $usuarios = $controladores['usuarios'];
    /** @var SocioController $socios */
    $socios = $controladores['socios'];
    /** @var CategoriaController $categorias */
    $categorias = $controladores['categorias'];
    /** @var ReciboController $recibos */
    $recibos = $controladores['recibos'];
    /** @var PagoController $pagos */
    $pagos = $controladores['pagos'];
    /** @var CobranzaController $cobranzas */
    $cobranzas = $controladores['cobranzas'];
    /** @var ReporteController $reportes */
    $reportes = $controladores['reportes'];
    /** @var NotificacionController $notificaciones */
    $notificaciones = $controladores['notificaciones'];
    /** @var ConsultaController $consultas */
    $consultas = $controladores['consultas'];

    switch ($recurso) {

        // ---------- AUTENTICACIÓN ----------
        case 'auth':
            if ($sub1 === 'login' && $method === 'POST') {
                $auth->login();
                return;
            }
            if ($sub1 === 'logout' && $method === 'POST') {
                $auth->logout();
                return;
            }
            if ($sub1 === 'me' && $method === 'GET') {
                $auth->me();
                return;
            }
            responderNoEncontrado();
            return;

        // ---------- USUARIOS ----------
        case 'usuarios':
            $id = $sub1 !== null ? (int) $sub1 : null;

            match (true) {
                $method === 'GET' && $id !== null   => $usuarios->obtener($id),
                $method === 'GET'                   => $usuarios->listar(),
                $method === 'POST'                  => $usuarios->crear(),
                $method === 'PUT' && $id !== null    => $usuarios->actualizar($id),
                $method === 'DELETE' && $id !== null => $usuarios->eliminar($id),
                default => responderMetodoNoPermitido(),
            };
            return;

        // ---------- SOCIOS (y sub-recurso /socios/{id}/recibos) ----------
        case 'socios':
            $id = $sub1 !== null ? (int) $sub1 : null;

            if ($id !== null && $sub2 === 'recibos' && $method === 'GET') {
                $recibos->porSocio($id);
                return;
            }

            if ($id !== null && $sub2 === 'estado' && $method === 'PATCH') {
                $socios->cambiarEstado($id);
                return;
            }

            match (true) {
                $method === 'GET' && $id !== null   => $socios->obtener($id),
                $method === 'GET'                   => $socios->listar(),
                $method === 'POST'                  => $socios->crear(),
                $method === 'PUT' && $id !== null    => $socios->actualizar($id),
                $method === 'DELETE' && $id !== null => $socios->eliminar($id),
                default => responderMetodoNoPermitido(),
            };
            return;

        // ---------- CATEGORÍAS DE SOCIO ----------
        case 'categorias':
            $id = $sub1 !== null ? (int) $sub1 : null;

            match (true) {
                $method === 'GET' && $id !== null   => $categorias->obtener($id),
                $method === 'GET'                   => $categorias->listar(),
                $method === 'POST'                  => $categorias->crear(),
                $method === 'PUT' && $id !== null    => $categorias->actualizar($id),
                $method === 'DELETE' && $id !== null => $categorias->eliminar($id),
                default => responderMetodoNoPermitido(),
            };
            return;

        // ---------- RECIBOS ----------
        case 'recibos':
            if ($sub1 === 'generar-anuales' && $method === 'POST') {
                $recibos->generarAnuales();
                return;
            }

            $id = $sub1 !== null ? (int) $sub1 : null;

            if ($id !== null && $sub2 === 'anular' && $method === 'PATCH') {
                $recibos->anular($id);
                return;
            }

            match (true) {
                $method === 'GET' && $id !== null => $recibos->obtener($id),
                $method === 'GET'                 => $recibos->listar(),
                default => responderMetodoNoPermitido(),
            };
            return;

        // ---------- PAGOS ----------
        case 'pagos':
            $id = $sub1 !== null ? (int) $sub1 : null;

            match (true) {
                $method === 'GET' && $id !== null   => $pagos->obtener($id),
                $method === 'GET'                   => $pagos->listar(),
                $method === 'POST'                  => $pagos->crear(),
                $method === 'DELETE' && $id !== null => $pagos->eliminar($id),
                default => responderMetodoNoPermitido(),
            };
            return;

        // ---------- COBRANZA (panel del cobrador) ----------
        case 'cobranzas':
            if ($sub1 === 'pendientes' && $method === 'GET') {
                $cobranzas->pendientes();
                return;
            }
            if ($sub1 === 'registrar' && $method === 'POST') {
                // Registro directo de un cobro: se resuelve como un pago.
                $pagos->crear();
                return;
            }
            if ($sub1 === 'resultado-visita' && $method === 'POST') {
                $cobranzas->registrarVisita();
                return;
            }
            responderNoEncontrado();
            return;

        // ---------- REPORTES ----------
        case 'reportes':
            match (true) {
                $sub1 === 'ingresos' && $method === 'GET'             => $reportes->ingresos(),
                $sub1 === 'morosos' && $method === 'GET'               => $reportes->morosos(),
                $sub1 === 'cobranza-porcentaje' && $method === 'GET'   => $reportes->porcentajeCobranza(),
                default => responderNoEncontrado(),
            };
            return;

        // ---------- NOTIFICACIONES ----------
        case 'notificaciones':
            $id = $sub1 !== null ? (int) $sub1 : null;

            if ($id !== null && $sub2 === 'leer' && $method === 'PATCH') {
                $notificaciones->marcarLeida($id);
                return;
            }

            match (true) {
                $method === 'GET'  => $notificaciones->listar(),
                $method === 'POST' => $notificaciones->crear(),
                default => responderMetodoNoPermitido(),
            };
            return;

        // ---------- CONSULTAS DEL SOCIO ----------
        case 'consultas':
            $id = $sub1 !== null ? (int) $sub1 : null;

            match (true) {
                $method === 'GET' && $id !== null => $consultas->obtener($id),
                $method === 'GET'                 => $consultas->listar(),
                $method === 'POST'                => $consultas->crear(),
                default => responderMetodoNoPermitido(),
            };
            return;

        default:
            responderNoEncontrado();
    }
}

function responderNoEncontrado(): void
{
    http_response_code(404);
    echo json_encode(["mensaje" => "Ruta no encontrada"]);
}

function responderMetodoNoPermitido(): void
{
    http_response_code(405);
    echo json_encode(["mensaje" => "Método HTTP no permitido"]);
}
