<?php

// Activa el modo de tipos estrictos de PHP.
// Ayuda a detectar errores cuando se utilizas
// un tipos de datos incorrectos.
//
declare(strict_types=1);


// ======================================================
// VARIABLES DE ENTORNO (.env)
// ======================================================

// Construye la ruta hacia el archivo .env.
//
// __DIR__ representa la carpeta donde se encuentra
// este archivo index.php.
$envFile = __DIR__ . '/.env';

// Comprueba si el archivo .env existe y se puede leer.
if (is_readable($envFile)) {

    // Lee todas las líneas del archivo .env.
    //
    // FILE_IGNORE_NEW_LINES:
    // elimina los saltos de línea.
    //
    // FILE_SKIP_EMPTY_LINES:
    // ignora las líneas vacías.
    foreach (
        file(
            $envFile,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        ) as $linea
    ) {

        // Elimina espacios innecesarios
        // al principio y al final de la línea.
        $linea = trim($linea);

        // Comprueba si la línea:
        //
        // 1. Está vacía.
        // 2. Comienza con #, por lo que es un comentario.
        // 3. No contiene el signo =.
        //
        // Si ocurre cualquiera de estas situaciones,
        // se ignora la línea.
        if (
            $linea === '' ||
            $linea[0] === '#' ||
            !str_contains($linea, '=')
        ) {

            // Salta esta línea y continúa
            // con la siguiente.
            continue;
        }

        // Separa la línea en dos partes:
        //
        // izquierda  = nombre de la variable
        // derecha    = valor de la variable
        //
        // El número 2 indica que solamente queremos
        // dividir como máximo en dos partes.
        [$clave, $valor] = explode('=', $linea, 2);

        // Elimina espacios de la clave.
        $clave = trim($clave);

        // Elimina espacios y comillas simples o dobles
        // que puedan rodear al valor.
        $valor = trim($valor, " \t\"'");

        // Comprueba si esa variable de entorno
        // todavía no existe.
        if (getenv($clave) === false) {

            // Guarda la variable de entorno.
            //
            // Ejemplo:
            // DB_HOST=localhost
            //
            // se convierte en:
            // DB_HOST = localhost
            putenv("$clave=$valor");
        }
    }
}


// ======================================================
// SESIÓN
// ======================================================

// Inicia la sesión de PHP.
//
// Permite utilizar $_SESSION para mantener información
// del usuario que inició sesión.
session_start();


// ======================================================
// CONFIGURACIÓN DE RESPUESTAS
// ======================================================

// Indica que la API devuelve datos en formato JSON
// y utiliza codificación UTF-8.
header('Content-Type: application/json; charset=utf-8');


// ======================================================
// CORS
// ======================================================

// Permite que los frontends puedan comunicarse
// con el backend aunque estén en otro origen
// durante el desarrollo.
header('Access-Control-Allow-Origin: *');

// Indica qué métodos HTTP permite la API.
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

// Indica qué encabezados HTTP puede recibir la API.
header('Access-Control-Allow-Headers: Content-Type, Authorization');


// ======================================================
// PETICIÓN OPTIONS
// ======================================================

// Comprueba si la petición HTTP es OPTIONS.
//
// El navegador puede utilizar OPTIONS para preguntar
// si tiene permiso para realizar una petición CORS.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

    // Devuelve HTTP 204.
//
// 204 significa que la petición fue procesada
// pero no hay contenido para devolver.
    http_response_code(204);

    // Detiene la ejecución de este archivo.
    return;
}


// ======================================================
// BASE DE DATOS
// ======================================================

// Carga la clase Database.
//
// require_once significa que el archivo
// se incluye una sola vez.
require_once __DIR__ . '/database/Database.php';


// ======================================================
// MODELOS
// ======================================================

// Carga el modelo Usuario.
require_once __DIR__ . '/models/Usuario.php';

// Carga el modelo Socio.
require_once __DIR__ . '/models/Socio.php';

// Carga el modelo Recibo.
require_once __DIR__ . '/models/Recibo.php';

// Carga el modelo Pago.
require_once __DIR__ . '/models/Pago.php';


// ======================================================
// REPOSITORIOS
// ======================================================

// Carga el repositorio de usuarios.
require_once __DIR__ . '/repositories/UsuarioRepository.php';

// Carga el repositorio de socios.
require_once __DIR__ . '/repositories/SocioRepository.php';

// Carga el repositorio de categorías.
require_once __DIR__ . '/repositories/CategoriaRepository.php';

// Carga el repositorio de recibos.
require_once __DIR__ . '/repositories/ReciboRepository.php';

// Carga el repositorio de pagos.
require_once __DIR__ . '/repositories/PagoRepository.php';

// Carga el repositorio de cobranzas.
require_once __DIR__ . '/repositories/CobranzaRepository.php';

// Carga el repositorio de notificaciones.
require_once __DIR__ . '/repositories/NotificacionRepository.php';

// Carga el repositorio de consultas.
require_once __DIR__ . '/repositories/ConsultaRepository.php';


// ======================================================
// SERVICIOS
// ======================================================

// Carga el servicio de autenticación.
require_once __DIR__ . '/services/AuthService.php';

// Carga el servicio de usuarios.
require_once __DIR__ . '/services/UsuarioService.php';

// Carga el servicio de socios.
require_once __DIR__ . '/services/SocioService.php';

// Carga el servicio de categorías.
require_once __DIR__ . '/services/CategoriaService.php';

// Carga el servicio de recibos.
require_once __DIR__ . '/services/ReciboService.php';

// Carga el servicio de pagos.
require_once __DIR__ . '/services/PagoService.php';

// Carga el servicio de cobranzas.
require_once __DIR__ . '/services/CobranzaService.php';

// Carga el servicio de notificaciones.
require_once __DIR__ . '/services/NotificacionService.php';

// Carga el servicio de consultas.
require_once __DIR__ . '/services/ConsultaService.php';

// Carga el servicio de reportes.
require_once __DIR__ . '/services/ReporteService.php';


// ======================================================
// VALIDADORES
// ======================================================

// Carga el validador de autenticación.
require_once __DIR__ . '/validators/AuthValidator.php';

// Carga el validador de usuarios.
require_once __DIR__ . '/validators/UsuarioValidator.php';

// Carga el validador de socios.
require_once __DIR__ . '/validators/SocioValidator.php';

// Carga el validador de categorías.
require_once __DIR__ . '/validators/CategoriaValidator.php';

// Carga el validador de pagos.
require_once __DIR__ . '/validators/PagoValidator.php';

// Carga el validador de cobranzas.
require_once __DIR__ . '/validators/CobranzaValidator.php';

// Carga el validador de notificaciones.
require_once __DIR__ . '/validators/NotificacionValidator.php';

// Carga el validador de consultas.
require_once __DIR__ . '/validators/ConsultaValidator.php';


// ======================================================
// CONTROLADORES
// ======================================================

// Carga el controlador de autenticación.
require_once __DIR__ . '/controllers/AuthController.php';

// Carga el controlador de usuarios.
require_once __DIR__ . '/controllers/UsuarioController.php';

// Carga el controlador de socios.
require_once __DIR__ . '/controllers/SocioController.php';

// Carga el controlador de categorías.
require_once __DIR__ . '/controllers/CategoriaController.php';

// Carga el controlador de recibos.
require_once __DIR__ . '/controllers/ReciboController.php';

// Carga el controlador de pagos.
require_once __DIR__ . '/controllers/PagoController.php';

// Carga el controlador de cobranzas.
require_once __DIR__ . '/controllers/CobranzaController.php';

// Carga el controlador de notificaciones.
require_once __DIR__ . '/controllers/NotificacionController.php';

// Carga el controlador de consultas.
require_once __DIR__ . '/controllers/ConsultaController.php';

// Carga el controlador de reportes.
require_once __DIR__ . '/controllers/ReporteController.php';


// ======================================================
// RUTAS
// ======================================================

// Carga el archivo que contiene las rutas
// de la API.
require_once __DIR__ . '/Routes/routes.php';


// ======================================================
// INICIO DEL BACKEND
// ======================================================

try {

    // Crea un objeto Database y ejecuta conectar().
    //
    // El método conectar() devuelve una conexión PDO
    // con la base de datos MySQL.
    $db = (new Database())->conectar();


    // ==================================================
    // REPOSITORIOS
    // ==================================================

    // Crea el repositorio de usuarios
    // utilizando la conexión PDO.
    $usuarioRepository = new UsuarioRepository($db);

    // Crea el repositorio de socios.
    $socioRepository = new SocioRepository($db);

    // Crea el repositorio de categorías.
    $categoriaRepository = new CategoriaRepository($db);

    // Crea el repositorio de recibos.
    $reciboRepository = new ReciboRepository($db);

    // Crea el repositorio de pagos.
    //
    // Recibe la conexión y el repositorio de recibos.
    $pagoRepository = new PagoRepository(
        $db,
        $reciboRepository
    );

    // Crea el repositorio de cobranzas.
    $cobranzaRepository = new CobranzaRepository($db);

    // Crea el repositorio de notificaciones.
    $notificacionRepository = new NotificacionRepository($db);

    // Crea el repositorio de consultas.
    $consultaRepository = new ConsultaRepository($db);


    // ==================================================
    // SERVICIOS
    // ==================================================

    // Crea el servicio de autenticación.
    // Recibe el repositorio de usuarios.
    $authService = new AuthService($usuarioRepository);

    // Crea el servicio de usuarios.
    $usuarioService = new UsuarioService($usuarioRepository);

    // Crea el servicio de socios.
    $socioService = new SocioService($socioRepository);

    // Crea el servicio de categorías.
    $categoriaService = new CategoriaService($categoriaRepository);

    // Crea el servicio de recibos.
    //
    // Necesita:
    // - repositorio de recibos
    // - repositorio de socios
    // - repositorio de categorías
    $reciboService = new ReciboService(
        $reciboRepository,
        $socioRepository,
        $categoriaRepository
    );

    // Crea el servicio de pagos.
    $pagoService = new PagoService($pagoRepository);

    // Crea el servicio de cobranzas.
    $cobranzaService = new CobranzaService($cobranzaRepository);

    // Crea el servicio de notificaciones.
    $notificacionService =
        new NotificacionService($notificacionRepository);

    // Crea el servicio de consultas.
    $consultaService =
        new ConsultaService($consultaRepository);

    // Crea el servicio de reportes.
    $reporteService =
        new ReporteService($reciboRepository);


    // ==================================================
    // CONTROLADORES
    // ==================================================

    // Crea un array con todos los controladores.
    //
    // Las claves del array serán utilizadas por
    // routes.php para encontrar el controlador correcto.
    $controladores = [

        // Controlador de autenticación.
        //
        // Recibe el servicio y el validador.
        'auth' => new AuthController(
            $authService,
            new AuthValidator()
        ),

        // Controlador de usuarios.
        'usuarios' => new UsuarioController(
            $usuarioService,
            new UsuarioValidator()
        ),

        // Controlador de socios.
        'socios' => new SocioController(
            $socioService,
            new SocioValidator()
        ),

        // Controlador de categorías.
        'categorias' => new CategoriaController(
            $categoriaService,
            new CategoriaValidator()
        ),

        // Controlador de recibos.
        'recibos' => new ReciboController(
            $reciboService
        ),

        // Controlador de pagos.
        'pagos' => new PagoController(
            $pagoService,
            new PagoValidator()
        ),

        // Controlador de cobranzas.
        'cobranzas' => new CobranzaController(
            $cobranzaService,
            new CobranzaValidator()
        ),

        // Controlador de reportes.
        'reportes' => new ReporteController(
            $reporteService
        ),

        // Controlador de notificaciones.
        'notificaciones' => new NotificacionController(
            $notificacionService,
            new NotificacionValidator()
        ),

        // Controlador de consultas.
        'consultas' => new ConsultaController(
            $consultaService,
            new ConsultaValidator()
        ),
    ];


    // Obtiene el método HTTP de la petición.
    //
    // Ejemplos:
    // GET, POST, PUT, PATCH o DELETE.
    $method = $_SERVER['REQUEST_METHOD'];

    // Obtiene la URL solicitada.
    $uri = $_SERVER['REQUEST_URI'];


    // Envía el método, la URL y los controladores
    // al sistema de rutas.
    //
    // routes.php decide qué controlador
    // y qué función deben ejecutarse.
    manejarRutas(
        $method,
        $uri,
        $controladores
    );


// ======================================================
// ERROR DE BASE DE DATOS
// ======================================================

// Captura errores específicos de PDO/MySQL.
} catch (PDOException $e) {

    // Devuelve código HTTP 500.
    http_response_code(500);

    // Devuelve el error en formato JSON.
    echo json_encode([

        // Mensaje general.
        "mensaje" =>
            "Error de conexión o de base de datos",

        // Mensaje específico de la excepción.
        "detalle" => $e->getMessage(),
    ]);


// ======================================================
// OTROS ERRORES
// ======================================================

// Captura cualquier otro error o excepción
// que llegue hasta este punto.
} catch (Throwable $e) {

    // Devuelve código HTTP 500.
    http_response_code(500);

    // Devuelve el error en formato JSON.
    echo json_encode([

        // Mensaje general.
        "mensaje" => "Error interno del servidor",

        // Detalle del error.
        "detalle" => $e->getMessage(),
    ]);
}

