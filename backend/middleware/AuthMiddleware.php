<?php

// ======================================================
// AuthMiddleware
// ======================================================
//
// Se ejecuta ANTES de las rutas y decide si la petición
// puede continuar. Controla dos cosas:
//
//   1. Autenticación: ¿hay un usuario con sesión iniciada?
//      Si no, responde 401.
//
//   2. Autorización: ¿su rol puede usar este endpoint?
//      Si no, responde 403.
//
// Roles del sistema: administrador, cobrador y socio.
//
//   - administrador: puede usar todo.
//   - cobrador: socios y recibos (solo lectura), pagos y cobranzas.
//   - socio: únicamente SUS propios datos.
//
// Única ruta pública: POST /api/auth/login
// (también se deja pasar /api/auth/logout).

final class AuthMiddleware
{
    // Corta la petición si no está permitida.
    // Se llama desde index.php antes de manejarRutas().
    public static function proteger(string $method, string $uri, PDO $db): void
    {
        $usuario = $_SESSION['usuario'] ?? null;

        // Cuerpo JSON de la petición (puede ser null).
        $cuerpo = json_decode(file_get_contents('php://input'), true);
        $cuerpo = is_array($cuerpo) ? $cuerpo : [];

        $resultado = self::decidir($method, $uri, $usuario, $cuerpo, $db);

        if ($resultado['codigo'] !== 200) {
            http_response_code($resultado['codigo']);
            echo json_encode(["mensaje" => $resultado['mensaje']]);
            exit;
        }

        // Parámetros que el servidor fuerza para que el cliente
        // no pueda pedir datos de otra persona.
        foreach ($resultado['get'] as $clave => $valor) {
            $_GET[$clave] = (string) $valor;
        }
    }


    // Decide si la petición se permite. No envía nada ni usa exit,
    // por eso se puede probar fácilmente.
    //
    // Devuelve: ['codigo' => 200|401|403, 'mensaje' => string, 'get' => array]
    public static function decidir(
        string $method,
        string $uri,
        ?array $usuario,
        array $cuerpo,
        PDO $db
    ): array {

        $permitido = ['codigo' => 200, 'mensaje' => '', 'get' => []];

        // Divide la URL: /api/socios/5/recibos -> [api, socios, 5, recibos]
        $ruta = trim(parse_url($uri, PHP_URL_PATH) ?? $uri, '/');
        $partes = explode('/', $ruta);

        // Si no empieza con /api, que lo resuelva el enrutador (404).
        if (($partes[0] ?? '') !== 'api') {
            return $permitido;
        }

        $recurso = $partes[1] ?? '';
        $sub1 = $partes[2] ?? null;
        $sub2 = $partes[3] ?? null;

        // Rutas públicas.
        if ($recurso === 'auth' && in_array($sub1, ['login', 'logout'], true)) {
            return $permitido;
        }

        // Sin sesión: 401.
        if ($usuario === null || empty($usuario['id_usuario'])) {
            return self::respuesta(401, 'Debés iniciar sesión.');
        }

        $rol = (string) ($usuario['rol'] ?? '');

        // /api/auth/me lo puede usar cualquier usuario con sesión.
        if ($recurso === 'auth') {
            return $permitido;
        }

        // El administrador puede todo.
        if ($rol === 'administrador') {
            return $permitido;
        }

        if ($rol === 'cobrador') {
            return self::reglasCobrador(
                $method, $recurso, $sub1, $usuario, $cuerpo, $permitido
            );
        }

        if ($rol === 'socio') {
            return self::reglasSocio(
                $method, $recurso, $sub1, $sub2, $usuario, $cuerpo, $db, $permitido
            );
        }

        // Rol desconocido: no se permite nada.
        return self::respuesta(403, 'No tenés permiso para esta acción.');
    }


    // ---------------------------------------------------------
    // COBRADOR
    // ---------------------------------------------------------
    private static function reglasCobrador(
        string $method,
        string $recurso,
        ?string $sub1,
        array $usuario,
        array $cuerpo,
        array $permitido
    ): array {

        $idCobrador = (int) $usuario['id_usuario'];

        // Solo lectura de categorías, socios y recibos.
        if (in_array($recurso, ['categorias', 'socios', 'recibos'], true)) {
            // /socios/{id}/estado y similares son PATCH: quedan prohibidos.
            if ($method === 'GET') {
                return $permitido;
            }
        }

        // Pagos: listar, ver y registrar (no anular).
        if ($recurso === 'pagos' && in_array($method, ['GET', 'POST'], true)) {
            return $permitido;
        }

        // Cobranzas.
        if ($recurso === 'cobranzas') {

            // Solo ve SU cartera: se ignora el id_cobrador que mande el cliente.
            if ($sub1 === 'pendientes' && $method === 'GET') {
                $permitido['get']['id_cobrador'] = $idCobrador;
                return $permitido;
            }

            if ($sub1 === 'registrar' && $method === 'POST') {
                return $permitido;
            }

            // Solo puede registrar visitas a nombre propio.
            if ($sub1 === 'resultado-visita' && $method === 'POST') {
                if ((int) ($cuerpo['id_cobrador'] ?? 0) !== $idCobrador) {
                    return self::respuesta(403, 'Solo podés registrar visitas a tu nombre.');
                }
                return $permitido;
            }
        }

        return self::respuesta(403, 'No tenés permiso para esta acción.');
    }


    // ---------------------------------------------------------
    // SOCIO (solo sus propios datos)
    // ---------------------------------------------------------
    private static function reglasSocio(
        string $method,
        string $recurso,
        ?string $sub1,
        ?string $sub2,
        array $usuario,
        array $cuerpo,
        PDO $db,
        array $permitido
    ): array {

        $negado = self::respuesta(403, 'No tenés permiso para ver o modificar datos de otro socio.');

        // Busca cuál es la ficha de socio de este usuario.
        $idSocio = self::idSocioDelUsuario($db, (int) $usuario['id_usuario']);

        if ($idSocio === null) {
            return self::respuesta(403, 'Tu usuario no está vinculado a un socio.');
        }

        $id = ($sub1 !== null && ctype_digit($sub1)) ? (int) $sub1 : null;

        switch ($recurso) {

            // Ver las categorías (por ejemplo para mostrar su cuota).
            case 'categorias':
                return $method === 'GET' ? $permitido : $negado;

            // GET /api/socios/{su id} y GET /api/socios/{su id}/recibos
            // PUT /api/socios/{su id}: solo puede cambiar sus datos de contacto
            case 'socios':
                if (
                    $method === 'GET' && $id === $idSocio
                    && ($sub2 === null || $sub2 === 'recibos')
                ) {
                    return $permitido;
                }

                if ($method === 'PUT' && $id === $idSocio && $sub2 === null) {

                    // Campos que el socio puede cambiar por su cuenta.
                    // Todo lo demás (categoría, estado, documento, usuario...)
                    // lo cambia únicamente la administración.
                    $editables = ['email', 'telefono', 'direccion'];
                    $enviados = array_keys($cuerpo);

                    if ($enviados !== [] && array_diff($enviados, $editables) === []) {
                        return $permitido;
                    }

                    return self::respuesta(
                        403,
                        'Solo podés modificar tu email, teléfono y dirección.'
                    );
                }

                return $negado;

            // GET /api/recibos (con filtros): se fuerza su id_socio, así solo
            // ve SUS recibos aunque en la URL pida los de otro socio.
            // GET /api/recibos/{id}: solo si el recibo es suyo.
            case 'recibos':
                if ($method === 'GET' && $id === null && $sub1 === null) {
                    $permitido['get']['id_socio'] = $idSocio;
                    return $permitido;
                }
                if (
                    $method === 'GET' && $id !== null && $sub2 === null
                    && self::esDelSocio($db, 'RECIBO', 'id_recibo', $id, $idSocio)
                ) {
                    return $permitido;
                }
                return $negado;

            // GET /api/pagos (con filtros): se fuerza su id_socio.
            // GET /api/pagos/{id}: solo si el pago es suyo.
            case 'pagos':
                if ($method === 'GET' && $id === null && $sub1 === null) {
                    $permitido['get']['id_socio'] = $idSocio;
                    return $permitido;
                }
                if (
                    $method === 'GET' && $id !== null && $sub2 === null
                    && self::esDelSocio($db, 'PAGO', 'id_pago', $id, $idSocio)
                ) {
                    return $permitido;
                }
                return $negado;

            // Notificaciones propias.
            case 'notificaciones':
                // GET /api/notificaciones -> se fuerza su id_socio.
                if ($method === 'GET' && $id === null) {
                    $permitido['get']['id_socio'] = $idSocio;
                    return $permitido;
                }
                // PATCH /api/notificaciones/{id}/leer -> solo si es suya.
                if (
                    $method === 'PATCH' && $id !== null && $sub2 === 'leer'
                    && self::esDelSocio($db, 'NOTIFICACION', 'id_notificacion', $id, $idSocio)
                ) {
                    return $permitido;
                }
                return $negado;

            // Consultas propias.
            case 'consultas':
                // GET /api/consultas/{id} -> solo si es suya.
                if (
                    $method === 'GET' && $id !== null
                    && self::esDelSocio($db, 'CONSULTA', 'id_consulta', $id, $idSocio)
                ) {
                    return $permitido;
                }
                // POST /api/consultas -> solo a su nombre.
                if ($method === 'POST' && $id === null) {
                    if ((int) ($cuerpo['id_socio'] ?? 0) === $idSocio) {
                        return $permitido;
                    }
                }
                return $negado;
        }

        return $negado;
    }


    // ---------------------------------------------------------
    // AYUDAS
    // ---------------------------------------------------------

    // Devuelve el id_socio vinculado a un usuario, o null.
    private static function idSocioDelUsuario(PDO $db, int $idUsuario): ?int
    {
        $stmt = $db->prepare('SELECT id_socio FROM SOCIO WHERE id_usuario = :id');
        $stmt->execute(['id' => $idUsuario]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? (int) $fila['id_socio'] : null;
    }

    // Comprueba que un registro pertenezca a un socio.
    // $tabla y $columna son valores fijos escritos en este archivo
    // (nunca vienen del usuario), por eso se pueden concatenar.
    private static function esDelSocio(
        PDO $db,
        string $tabla,
        string $columna,
        int $id,
        int $idSocio
    ): bool {
        $stmt = $db->prepare(
            "SELECT 1 FROM {$tabla} WHERE {$columna} = :id AND id_socio = :socio"
        );
        $stmt->execute(['id' => $id, 'socio' => $idSocio]);

        return (bool) $stmt->fetchColumn();
    }

    private static function respuesta(int $codigo, string $mensaje): array
    {
        return ['codigo' => $codigo, 'mensaje' => $mensaje, 'get' => []];
    }
}