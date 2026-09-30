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
            // POST /api/auth/login: inicia sesión con las credenciales recibidas.
            if ($sub1 === 'login' && $method === 'POST') {
                $auth->login();
                return;
            }
            // POST /api/auth/logout: cierra la sesión actual.
            if ($sub1 === 'logout' && $method === 'POST') {
                $auth->logout();
                return;
            }
            // GET /api/auth/me: consulta los datos del usuario autenticado.
            if ($sub1 === 'me' && $method === 'GET') {
                $auth->me();
                return;
            }
            responderNoEncontrado();
            return;

        // ---------- USUARIOS ----------
        case 'usuarios':
            $id = $sub1 !== null ? (int) $sub1 : null;

            // GET /api/usuarios y GET /api/usuarios/{id}: lista o consulta un usuario.
            // POST /api/usuarios: crea un usuario.
            // PUT /api/usuarios/{id}: actualiza un usuario.
            // DELETE /api/usuarios/{id}: elimina un usuario.
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

            // GET /api/socios/{id}/recibos: lista los recibos del socio indicado.
            if ($id !== null && $sub2 === 'recibos' && $method === 'GET') {
                $recibos->porSocio($id);
                return;
            }

            // PATCH /api/socios/{id}/estado: cambia el estado del socio.
            if ($id !== null && $sub2 === 'estado' && $method === 'PATCH') {
                $socios->cambiarEstado($id);
                return;
            }

            // GET /api/socios y GET /api/socios/{id}: lista o consulta socios.
            // POST /api/socios: crea un socio.
            // PUT /api/socios/{id}: actualiza un socio.
            // DELETE /api/socios/{id}: elimina un socio.
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

            // GET /api/categorias y GET /api/categorias/{id}: lista o consulta categorías.
            // POST /api/categorias: crea una categoría.
            // PUT /api/categorias/{id}: actualiza una categoría.
            // DELETE /api/categorias/{id}: elimina una categoría.
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
            // POST /api/recibos/generar-anuales: genera los recibos del año solicitado.
            if ($sub1 === 'generar-anuales' && $method === 'POST') {
                $recibos->generarAnuales();
                return;
            }

            $id = $sub1 !== null ? (int) $sub1 : null;

            // PATCH /api/recibos/{id}/anular: anula el recibo indicado.
            if ($id !== null && $sub2 === 'anular' && $method === 'PATCH') {
                $recibos->anular($id);
                return;
            }

            // GET /api/recibos y GET /api/recibos/{id}: lista o consulta recibos.
            match (true) {
                $method === 'GET' && $id !== null => $recibos->obtener($id),
                $method === 'GET'                 => $recibos->listar(),
                default => responderMetodoNoPermitido(),
            };
            return;

        // ---------- PAGOS ----------
        case 'pagos':
            $id = $sub1 !== null ? (int) $sub1 : null;

            // GET /api/pagos y GET /api/pagos/{id}: lista o consulta pagos.
            // POST /api/pagos: registra un pago y los recibos que cancela.
            // DELETE /api/pagos/{id}: anula el pago.
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
            // GET /api/cobranzas/pendientes: consulta socios pendientes de cobro.
            if ($sub1 === 'pendientes' && $method === 'GET') {
                $cobranzas->pendientes();
                return;
            }
            // POST /api/cobranzas/registrar: registra un cobro mediante el flujo de pagos.
            if ($sub1 === 'registrar' && $method === 'POST') {
                // Registro directo de un cobro: se resuelve como un pago.
                $pagos->crear();
                return;
            }
            // POST /api/cobranzas/resultado-visita: guarda el resultado de una visita.
            if ($sub1 === 'resultado-visita' && $method === 'POST') {
                $cobranzas->registrarVisita();
                return;
            }
            responderNoEncontrado();
            return;

        // ---------- REPORTES ----------
        case 'reportes':
            // GET /api/reportes/ingresos: obtiene el reporte de ingresos por periodo.
            // GET /api/reportes/morosos: lista los socios morosos.
            // GET /api/reportes/cobranza-porcentaje: calcula el porcentaje de cobranza.
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

            // PATCH /api/notificaciones/{id}/leer: marca una notificación como leída.
            if ($id !== null && $sub2 === 'leer' && $method === 'PATCH') {
                $notificaciones->marcarLeida($id);
                return;
            }

            // GET /api/notificaciones: lista notificaciones (puede filtrar por socio).
            // POST /api/notificaciones: crea una notificación.
            match (true) {
                $method === 'GET'  => $notificaciones->listar(),
                $method === 'POST' => $notificaciones->crear(),
                default => responderMetodoNoPermitido(),
            };
            return;

        // ---------- CONSULTAS DEL SOCIO ----------
        case 'consultas':
            $id = $sub1 !== null ? (int) $sub1 : null;

            // GET /api/consultas y GET /api/consultas/{id}: lista o consulta solicitudes.
            // POST /api/consultas: crea una consulta enviada por un socio.
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
