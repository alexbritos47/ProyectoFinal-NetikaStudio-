<?php


/**
 * Enrutador simple para la API del Club Ciclista Maragato.
 * Este archivo se encarga de decidir qué Controller debe ejecutarse
 * dependiendo de la URL y del método HTTP recibido.
 */
function manejarRutas(string $method, string $uri, array $controladores): void
{
    // Obtiene solamente la parte de la ruta de la URL.
    // parse_url() permite separar la ruta de otros elementos como ?parametros.
    // PHP_URL_PATH indica que queremos solamente el camino de la URL.
    // trim(..., '/') elimina las barras "/" del comienzo y del final.
    $uri = trim(parse_url($uri, PHP_URL_PATH) ?? $uri, '/');


    // Divide la URL utilizando "/" como separador.
    // Ejemplo: api/socios/5
    // queda: ["api", "socios", "5"]
    $partes = explode('/', $uri);


    // Todas las rutas de nuestra API deben comenzar con "api".
    // Si no comienza con api, se responde que la ruta no existe.
    if (($partes[0] ?? '') !== 'api') {
        responderNoEncontrado();
        return;
    }


    // Obtiene el recurso principal de la URL.
    // Ejemplo: en /api/socios/5, el recurso es "socios".
    $recurso = $partes[1] ?? null;


    // Obtiene la primera parte después del recurso.
    // Ejemplo: en /api/socios/5, $sub1 sería "5".
    $sub1 = $partes[2] ?? null;


    // Obtiene la segunda parte después del recurso.
    // Ejemplo: /api/socios/5/recibos
    // $sub2 sería "recibos".
    $sub2 = $partes[3] ?? null;


    // Obtiene una tercera parte de la URL si existe.
    $sub3 = $partes[4] ?? null;


    // Indica que $auth será un objeto AuthController.
    // /** @var */ es solamente una indicación para el editor/IDE.
    /** @var AuthController $auth */
    $auth = $controladores['auth'];


    // Obtiene el UsuarioController desde el arreglo de Controllers.
    /** @var UsuarioController $usuarios */
    $usuarios = $controladores['usuarios'];


    // Obtiene el SocioController.
    /** @var SocioController $socios */
    $socios = $controladores['socios'];


    // Obtiene el CategoriaController.
    /** @var CategoriaController $categorias */
    $categorias = $controladores['categorias'];


    // Obtiene el ReciboController.
    /** @var ReciboController $recibos */
    $recibos = $controladores['recibos'];


    // Obtiene el PagoController.
    /** @var PagoController $pagos */
    $pagos = $controladores['pagos'];


    // Obtiene el CobranzaController.
    /** @var CobranzaController $cobranzas */
    $cobranzas = $controladores['cobranzas'];


    // Obtiene el ReporteController.
    /** @var ReporteController $reportes */
    $reportes = $controladores['reportes'];


    // Obtiene el NotificacionController.
    /** @var NotificacionController $notificaciones */
    $notificaciones = $controladores['notificaciones'];


    // Obtiene el ConsultaController.
    /** @var ConsultaController $consultas */
    $consultas = $controladores['consultas'];


    // switch analiza qué recurso se está solicitando.
    // Por ejemplo: auth, usuarios, socios, pagos, etc.
    switch ($recurso) {


        // =====================================================
        // AUTENTICACIÓN
        // =====================================================
        case 'auth':

            // Comprueba si la ruta es:
            // POST /api/auth/login
            if ($sub1 === 'login' && $method === 'POST') {

                // Ejecuta el método login() del AuthController.
                $auth->login();

                // Termina la función porque la ruta ya fue atendida.
                return;
            }


            // Comprueba si la ruta es:
            // POST /api/auth/logout
            if ($sub1 === 'logout' && $method === 'POST') {

                // Ejecuta el método logout().
                $auth->logout();

                // Termina la función.
                return;
            }


            // Comprueba si la ruta es:
            // GET /api/auth/me
            if ($sub1 === 'me' && $method === 'GET') {

                // Ejecuta el método me().
                $auth->me();

                // Termina la función.
                return;
            }


            // Si ninguna de las rutas anteriores coincide,
            // significa que la ruta no existe.
            responderNoEncontrado();

            // Termina la función.
            return;


        // =====================================================
        // USUARIOS
        // =====================================================
        case 'usuarios':

            // Si existe un ID en la URL, lo convierte a entero.
            // Si no existe, queda como null.
            $id = $sub1 !== null ? (int) $sub1 : null;


            // match(true) permite comprobar diferentes condiciones.
            // Se ejecuta la primera condición que resulte verdadera.
            match (true) {

                // GET /api/usuarios/{id}
                // Busca un usuario específico.
                $method === 'GET' && $id !== null
                    => $usuarios->obtener($id),

                // GET /api/usuarios
                // Obtiene todos los usuarios.
                $method === 'GET'
                    => $usuarios->listar(),

                // POST /api/usuarios
                // Crea un usuario.
                $method === 'POST'
                    => $usuarios->crear(),

                // PUT /api/usuarios/{id}
                // Actualiza un usuario.
                $method === 'PUT' && $id !== null
                    => $usuarios->actualizar($id),

                // DELETE /api/usuarios/{id}
                // Elimina un usuario.
                $method === 'DELETE' && $id !== null
                    => $usuarios->eliminar($id),

                // Si ninguna condición coincide,
                // devuelve error 405.
                default
                    => responderMetodoNoPermitido(),
            };

            // Termina la función.
            return;


        // =====================================================
        // SOCIOS
        // =====================================================
        case 'socios':

            // Obtiene el ID del socio desde la URL.
            $id = $sub1 !== null ? (int) $sub1 : null;


            // Comprueba la ruta:
            // GET /api/socios/{id}/recibos
            if ($id !== null && $sub2 === 'recibos' && $method === 'GET') {

                // Llama al Controller de recibos.
                // Busca los recibos pertenecientes a ese socio.
                $recibos->porSocio($id);

                // Termina la función.
                return;
            }


            // Comprueba la ruta:
            // PATCH /api/socios/{id}/estado
            if ($id !== null && $sub2 === 'estado' && $method === 'PATCH') {

                // Llama al método que cambia el estado del socio.
                $socios->cambiarEstado($id);

                // Termina la función.
                return;
            }


            // Rutas normales de socios.
            match (true) {

                // GET /api/socios/{id}
                // Obtiene un socio específico.
                $method === 'GET' && $id !== null
                    => $socios->obtener($id),

                // GET /api/socios
                // Lista todos los socios.
                $method === 'GET'
                    => $socios->listar(),

                // POST /api/socios
                // Crea un socio.
                $method === 'POST'
                    => $socios->crear(),

                // PUT /api/socios/{id}
                // Actualiza un socio.
                $method === 'PUT' && $id !== null
                    => $socios->actualizar($id),

                // DELETE /api/socios/{id}
                // Elimina un socio.
                $method === 'DELETE' && $id !== null
                    => $socios->eliminar($id),

                // Método no permitido.
                default
                    => responderMetodoNoPermitido(),
            };

            // Termina la función.
            return;


        // =====================================================
        // CATEGORÍAS
        // =====================================================
        case 'categorias':

            // Obtiene el ID de la categoría si existe.
            $id = $sub1 !== null ? (int) $sub1 : null;


            // Define las operaciones disponibles para categorías.
            match (true) {

                // GET /api/categorias/{id}
                $method === 'GET' && $id !== null
                    => $categorias->obtener($id),

                // GET /api/categorias
                $method === 'GET'
                    => $categorias->listar(),

                // POST /api/categorias
                $method === 'POST'
                    => $categorias->crear(),

                // PUT /api/categorias/{id}
                $method === 'PUT' && $id !== null
                    => $categorias->actualizar($id),

                // DELETE /api/categorias/{id}
                $method === 'DELETE' && $id !== null
                    => $categorias->eliminar($id),

                // Método no permitido.
                default
                    => responderMetodoNoPermitido(),
            };

            // Termina la función.
            return;


        // =====================================================
        // RECIBOS
        // =====================================================
        case 'recibos':

            // Comprueba la ruta:
            // POST /api/recibos/generar-anuales
            if ($sub1 === 'generar-anuales' && $method === 'POST') {

                // Genera los recibos anuales.
                $recibos->generarAnuales();

                // Termina la función.
                return;
            }


            // Obtiene el ID del recibo si existe.
            $id = $sub1 !== null ? (int) $sub1 : null;


            // Comprueba la ruta:
            // PATCH /api/recibos/{id}/anular
            if ($id !== null && $sub2 === 'anular' && $method === 'PATCH') {

                // Anula el recibo indicado.
                $recibos->anular($id);

                // Termina la función.
                return;
            }


            // Rutas GET de recibos.
            match (true) {

                // GET /api/recibos/{id}
                // Obtiene un recibo específico.
                $method === 'GET' && $id !== null
                    => $recibos->obtener($id),

                // GET /api/recibos
                // Lista todos los recibos.
                $method === 'GET'
                    => $recibos->listar(),

                // Cualquier otro método no permitido.
                default
                    => responderMetodoNoPermitido(),
            };

            // Termina la función.
            return;


        // =====================================================
        // PAGOS
        // =====================================================
        case 'pagos':

            // Obtiene el ID del pago si existe.
            $id = $sub1 !== null ? (int) $sub1 : null;


            // Define las operaciones disponibles para pagos.
            match (true) {

                // GET /api/pagos/{id}
                // Obtiene un pago específico.
                $method === 'GET' && $id !== null
                    => $pagos->obtener($id),

                // GET /api/pagos
                // Lista todos los pagos.
                $method === 'GET'
                    => $pagos->listar(),

                // POST /api/pagos
                // Registra un nuevo pago.
                $method === 'POST'
                    => $pagos->crear(),

                // DELETE /api/pagos/{id}
                // Anula el pago.
                $method === 'DELETE' && $id !== null
                    => $pagos->eliminar($id),

                // Método no permitido.
                default
                    => responderMetodoNoPermitido(),
            };

            // Termina la función.
            return;


        // =====================================================
        // COBRANZAS
        // =====================================================
        case 'cobranzas':

            // Comprueba la ruta:
            // GET /api/cobranzas/pendientes
            if ($sub1 === 'pendientes' && $method === 'GET') {

                // Obtiene los socios que tienen cobros pendientes.
                $cobranzas->pendientes();

                // Termina la función.
                return;
            }


            // Comprueba la ruta:
            // POST /api/cobranzas/registrar
            if ($sub1 === 'registrar' && $method === 'POST') {

                // El registro de un cobro se maneja mediante
                // el mismo flujo utilizado para registrar un pago.
                $pagos->crear();

                // Termina la función.
                return;
            }


            // Comprueba la ruta:
            // POST /api/cobranzas/resultado-visita
            if ($sub1 === 'resultado-visita' && $method === 'POST') {

                // Registra el resultado de una visita del cobrador.
                $cobranzas->registrarVisita();

                // Termina la función.
                return;
            }


            // Si ninguna ruta coincide, devuelve 404.
            responderNoEncontrado();

            // Termina la función.
            return;


        // =====================================================
        // REPORTES
        // =====================================================
        case 'reportes':

            // Define las rutas disponibles para los reportes.
            match (true) {

                // GET /api/reportes/ingresos
                // Obtiene el reporte de ingresos.
                $sub1 === 'ingresos' && $method === 'GET'
                    => $reportes->ingresos(),

                // GET /api/reportes/morosos
                // Obtiene los socios morosos.
                $sub1 === 'morosos' && $method === 'GET'
                    => $reportes->morosos(),

                // GET /api/reportes/cobranza-porcentaje
                // Obtiene el porcentaje de cobranza.
                $sub1 === 'cobranza-porcentaje' && $method === 'GET'
                    => $reportes->porcentajeCobranza(),

                // Si la ruta no existe, devuelve 404.
                default
                    => responderNoEncontrado(),
            };

            // Termina la función.
            return;


        // =====================================================
        // NOTIFICACIONES
        // =====================================================
        case 'notificaciones':

            // Obtiene el ID de la notificación si existe.
            $id = $sub1 !== null ? (int) $sub1 : null;


            // Comprueba la ruta:
            // PATCH /api/notificaciones/{id}/leer
            if ($id !== null && $sub2 === 'leer' && $method === 'PATCH') {

                // Marca la notificación como leída.
                $notificaciones->marcarLeida($id);

                // Termina la función.
                return;
            }


            // Define las operaciones normales de notificaciones.
            match (true) {

                // GET /api/notificaciones
                // Lista las notificaciones.
                $method === 'GET'
                    => $notificaciones->listar(),

                // POST /api/notificaciones
                // Crea una notificación.
                $method === 'POST'
                    => $notificaciones->crear(),

                // Método no permitido.
                default
                    => responderMetodoNoPermitido(),
            };

            // Termina la función.
            return;


        // =====================================================
        // CONSULTAS
        // =====================================================
        case 'consultas':

            // Obtiene el ID de la consulta si existe.
            $id = $sub1 !== null ? (int) $sub1 : null;


            // Define las operaciones disponibles para consultas.
            match (true) {

                // GET /api/consultas/{id}
                // Obtiene una consulta específica.
                $method === 'GET' && $id !== null
                    => $consultas->obtener($id),

                // GET /api/consultas
                // Lista todas las consultas.
                $method === 'GET'
                    => $consultas->listar(),

                // POST /api/consultas
                // Crea una nueva consulta.
                $method === 'POST'
                    => $consultas->crear(),

                // Método no permitido.
                default
                    => responderMetodoNoPermitido(),
            };

            // Termina la función.
            return;


        // =====================================================
        // RECURSO NO EXISTENTE
        // =====================================================
        default:

            // Si el recurso no coincide con ninguno de los casos,
            // responde que la ruta no existe.
            responderNoEncontrado();
    }
}


// Función que responde cuando la ruta solicitada no existe.
function responderNoEncontrado(): void
{
    // HTTP 404 significa "Not Found".
    http_response_code(404);

    // Devuelve una respuesta JSON indicando que la ruta no existe.
    echo json_encode(["mensaje" => "Ruta no encontrada"]);
}


// Función que responde cuando la ruta existe,
// pero el método HTTP utilizado no está permitido.
function responderMetodoNoPermitido(): void
{
    // HTTP 405 significa "Method Not Allowed".
    http_response_code(405);

    // Devuelve una respuesta JSON indicando que el método no está permitido.
    echo json_encode(["mensaje" => "Método HTTP no permitido"]);
}

