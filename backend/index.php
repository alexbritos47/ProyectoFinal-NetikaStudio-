<?php

header('Content-Type: application/json; charset=utf-8');

// Database
require_once __DIR__ . '/database/Database.php';

// Models
require_once __DIR__ . '/models/Socio.php';

// Repositories
require_once __DIR__ . '/repositories/SocioRepository.php';

// Services
require_once __DIR__ . '/services/SocioService.php';

// Validators
require_once __DIR__ . '/validators/SocioValidator.php';

// Controllers
require_once __DIR__ . '/controllers/SocioController.php';

// Routes
require_once __DIR__ . '/routes/routes.php';


// Crear conexión
$database = new Database();

$db = $database->conectar();


// Crear Repository
$socioRepository = new SocioRepository($db);


// Crear Service
$socioService = new SocioService($socioRepository);


// Crear Validator
$socioValidator = new SocioValidator();


// Crear Controller
$socioController = new SocioController(
    $socioService,
    $socioValidator
);


// Obtener petición
$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);


// Ejecutar rutas
manejarRutas(
    $method,
    $uri,
    $socioController
);