<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';
require_once __DIR__ . '/../controllers/SocioController.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = array_values(
    array_filter(explode('/', trim($uri, '/')))
);

$count = count($parts);


// =====================================================
// AUTENTICACIÓN
// POST /api/iniciar-sesion
// =====================================================

if (
    $method === 'POST' &&
    $count === 2 &&
    $parts[0] === 'api' &&
    $parts[1] === 'iniciar-sesion'
) {
    (new AuthController())->iniciarSesion();
    exit;
}


// =====================================================
// USUARIOS
// =====================================================

// GET /api/usuarios
if (
    $method === 'GET' &&
    $count === 2 &&
    $parts[0] === 'api' &&
    $parts[1] === 'usuarios'
) {
    (new UsuarioController())->listar();
    exit;
}


// POST /api/usuarios
if (
    $method === 'POST' &&
    $count === 2 &&
    $parts[0] === 'api' &&
    $parts[1] === 'usuarios'
) {
    (new UsuarioController())->crear();
    exit;
}


// GET /api/usuarios/{id}
if (
    $method === 'GET' &&
    $count === 3 &&
    $parts[0] === 'api' &&
    $parts[1] === 'usuarios'
) {
    (new UsuarioController())->obtener($parts[2]);
    exit;
}


// PUT /api/usuarios/{id}
if (
    $method === 'PUT' &&
    $count === 3 &&
    $parts[0] === 'api' &&
    $parts[1] === 'usuarios'
) {
    (new UsuarioController())->actualizar($parts[2]);
    exit;
}


// DELETE /api/usuarios/{id}
if (
    $method === 'DELETE' &&
    $count === 3 &&
    $parts[0] === 'api' &&
    $parts[1] === 'usuarios'
) {
    (new UsuarioController())->eliminar($parts[2]);
    exit;
}


// =====================================================
// SOCIOS
// =====================================================

// GET /api/socios
if (
    $method === 'GET' &&
    $count === 2 &&
    $parts[0] === 'api' &&
    $parts[1] === 'socios'
) {
    (new SocioController())->listar();
    exit;
}


// POST /api/socios
if (
    $method === 'POST' &&
    $count === 2 &&
    $parts[0] === 'api' &&
    $parts[1] === 'socios'
) {
    (new SocioController())->crear();
    exit;
}


// GET /api/socios/{id}
if (
    $method === 'GET' &&
    $count === 3 &&
    $parts[0] === 'api' &&
    $parts[1] === 'socios'
) {
    (new SocioController())->obtener($parts[2]);
    exit;
}


// PUT /api/socios/{id}
if (
    $method === 'PUT' &&
    $count === 3 &&
    $parts[0] === 'api' &&
    $parts[1] === 'socios'
) {
    (new SocioController())->actualizar($parts[2]);
    exit;
}


// DELETE /api/socios/{id}
if (
    $method === 'DELETE' &&
    $count === 3 &&
    $parts[0] === 'api' &&
    $parts[1] === 'socios'
) {
    (new SocioController())->eliminar($parts[2]);
    exit;
}


// =====================================================
// ENDPOINT NO ENCONTRADO
// =====================================================

http_response_code(404);

echo json_encode([
    'exito' => false,
    'mensaje' => 'Endpoint no encontrado',
    'errores' => []
]);