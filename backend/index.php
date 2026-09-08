<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');
// CORS básico para que los 3 frontends (admin, socio, cobrador) puedan
// consumir la API desde otro origen durante el desarrollo.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    return;
}

// ---------- Base de datos ----------
require_once __DIR__ . '/database/Database.php';

// ---------- Modelos ----------
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Socio.php';
require_once __DIR__ . '/models/Recibo.php';
require_once __DIR__ . '/models/Pago.php';

// ---------- Repositorios ----------
require_once __DIR__ . '/repositories/UsuarioRepository.php';
require_once __DIR__ . '/repositories/SocioRepository.php';
require_once __DIR__ . '/repositories/CategoriaRepository.php';
require_once __DIR__ . '/repositories/ReciboRepository.php';
require_once __DIR__ . '/repositories/PagoRepository.php';
require_once __DIR__ . '/repositories/CobranzaRepository.php';
require_once __DIR__ . '/repositories/NotificacionRepository.php';
require_once __DIR__ . '/repositories/ConsultaRepository.php';

// ---------- Servicios ----------
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/UsuarioService.php';
require_once __DIR__ . '/services/SocioService.php';
require_once __DIR__ . '/services/CategoriaService.php';
require_once __DIR__ . '/services/ReciboService.php';
require_once __DIR__ . '/services/PagoService.php';
require_once __DIR__ . '/services/CobranzaService.php';
require_once __DIR__ . '/services/NotificacionService.php';
require_once __DIR__ . '/services/ConsultaService.php';
require_once __DIR__ . '/services/ReporteService.php';

// ---------- Validadores ----------
require_once __DIR__ . '/validators/AuthValidator.php';
require_once __DIR__ . '/validators/UsuarioValidator.php';
require_once __DIR__ . '/validators/SocioValidator.php';
require_once __DIR__ . '/validators/CategoriaValidator.php';
require_once __DIR__ . '/validators/PagoValidator.php';
require_once __DIR__ . '/validators/CobranzaValidator.php';
require_once __DIR__ . '/validators/NotificacionValidator.php';
require_once __DIR__ . '/validators/ConsultaValidator.php';

// ---------- Controladores ----------
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/SocioController.php';
require_once __DIR__ . '/controllers/CategoriaController.php';
require_once __DIR__ . '/controllers/ReciboController.php';
require_once __DIR__ . '/controllers/PagoController.php';
require_once __DIR__ . '/controllers/CobranzaController.php';
require_once __DIR__ . '/controllers/NotificacionController.php';
require_once __DIR__ . '/controllers/ConsultaController.php';
require_once __DIR__ . '/controllers/ReporteController.php';

// ---------- Rutas ----------
require_once __DIR__ . '/Routes/routes.php';

try {
    // Conexión única de PDO, compartida por todos los repositorios.
    $db = (new Database())->conectar();

    // Repositorios
    $usuarioRepository = new UsuarioRepository($db);
    $socioRepository = new SocioRepository($db);
    $categoriaRepository = new CategoriaRepository($db);
    $reciboRepository = new ReciboRepository($db);
    $pagoRepository = new PagoRepository($db, $reciboRepository);
    $cobranzaRepository = new CobranzaRepository($db);
    $notificacionRepository = new NotificacionRepository($db);
    $consultaRepository = new ConsultaRepository($db);

    // Servicios
    $authService = new AuthService($usuarioRepository);
    $usuarioService = new UsuarioService($usuarioRepository);
    $socioService = new SocioService($socioRepository);
    $categoriaService = new CategoriaService($categoriaRepository);
    $reciboService = new ReciboService($reciboRepository, $socioRepository, $categoriaRepository);
    $pagoService = new PagoService($pagoRepository);
    $cobranzaService = new CobranzaService($cobranzaRepository);
    $notificacionService = new NotificacionService($notificacionRepository);
    $consultaService = new ConsultaService($consultaRepository);
    $reporteService = new ReporteService($reciboRepository);

    // Controladores
    $controladores = [
        'auth'            => new AuthController($authService, new AuthValidator()),
        'usuarios'        => new UsuarioController($usuarioService, new UsuarioValidator()),
        'socios'          => new SocioController($socioService, new SocioValidator()),
        'categorias'      => new CategoriaController($categoriaService, new CategoriaValidator()),
        'recibos'         => new ReciboController($reciboService),
        'pagos'           => new PagoController($pagoService, new PagoValidator()),
        'cobranzas'       => new CobranzaController($cobranzaService, new CobranzaValidator()),
        'reportes'        => new ReporteController($reporteService),
        'notificaciones'  => new NotificacionController($notificacionService, new NotificacionValidator()),
        'consultas'       => new ConsultaController($consultaService, new ConsultaValidator()),
    ];

    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    manejarRutas($method, $uri, $controladores);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "mensaje" => "Error de conexión o de base de datos",
        "detalle" => $e->getMessage(),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "mensaje" => "Error interno del servidor",
        "detalle" => $e->getMessage(),
    ]);
}
