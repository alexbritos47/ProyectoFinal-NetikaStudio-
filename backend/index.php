<?php

header('Content-Type: application/json; charset=utf-8');

// ======================================================
// DATABASE
// ======================================================

require_once __DIR__ . '/database/Database.php';


// ======================================================
// MODELS
// ======================================================

require_once __DIR__ . '/models/Socio.php';
require_once __DIR__ . '/models/Usuario.php';


// ======================================================
// REPOSITORIES
// ======================================================

require_once __DIR__ . '/repositories/SocioRepository.php';
require_once __DIR__ . '/repositories/UsuarioRepository.php';


// ======================================================
// SERVICES
// ======================================================

require_once __DIR__ . '/services/SocioService.php';
require_once __DIR__ . '/services/UsuarioService.php';
require_once __DIR__ . '/services/AuthService.php';


// ======================================================
// VALIDATORS
// ======================================================

require_once __DIR__ . '/validators/SocioValidator.php';
require_once __DIR__ . '/validators/UsuarioValidator.php';
require_once __DIR__ . '/validators/AuthValidator.php';


// ======================================================
// CONTROLLERS
// ======================================================

require_once __DIR__ . '/controllers/SocioController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/AuthController.php';


// ======================================================
// ROUTES
// ======================================================

require_once __DIR__ . '/routes/routes.php';


// ======================================================
// CREAR CONEXIÓN
// ======================================================

$database = new Database();

$db = $database->conectar();


// ======================================================
// SOCIO
// ======================================================

$socioRepository = new SocioRepository($db);

$socioService = new SocioService($socioRepository);

$socioValidator = new SocioValidator();

$socioController = new SocioController(
    $socioService,
    $socioValidator
);


// ======================================================
// USUARIO
// ======================================================

$usuarioRepository = new UsuarioRepository($db);

$usuarioService = new UsuarioService($usuarioRepository);

$usuarioValidator = new UsuarioValidator();

$usuarioController = new UsuarioController(
    $usuarioService,
    $usuarioValidator
);


// ======================================================
// AUTENTICACIÓN
// ======================================================

$authService = new AuthService($usuarioRepository);

$authValidator = new AuthValidator();

$authController = new AuthController(
    $authService,
    $authValidator
);


// ======================================================
// OBTENER PETICIÓN
// ======================================================

$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);


// ======================================================
// EJECUTAR RUTAS
// ======================================================

manejarRutas(
    $method,
    $uri,
    $socioController,
    $usuarioController,
    $authController
);