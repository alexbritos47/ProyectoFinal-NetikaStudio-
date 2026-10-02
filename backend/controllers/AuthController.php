
<?php


class AuthController

// Esta clase se encarga de manejar las peticiones relacionadas con el inicio de sesión.

{
    // Guarda una instancia del servicio de autenticación.
    // El Service contiene la lógica relacionada con el login.
    private AuthService $service;

    // Guarda una instancia del validador de autenticación.
    // El Validator se encarga de comprobar que los datos recibidos sean correctos.
    private AuthValidator $validator;


    public function __construct(AuthService $service, AuthValidator $validator)
    // Constructor de la clase.
    // Se ejecuta automáticamente cuando se crea un nuevo AuthController.
    // Recibe el AuthService y el AuthValidator.
    {
        // Guarda el AuthService recibido dentro de la propiedad $service.
        $this->service = $service;

        // Guarda el AuthValidator recibido dentro de la propiedad $validator.
        $this->validator = $validator;
    }


    public function login(): void
    // Función encargada de realizar el inicio de sesión.
    // public significa que puede ser llamada desde otras partes del programa.
    // void significa que la función no devuelve un valor mediante return.
    {
        // Lee los datos enviados en el cuerpo de la petición HTTP.
        // php://input permite obtener los datos enviados por POST, por ejemplo.
        // json_decode convierte el JSON recibido en un array de PHP.
        // El true hace que el resultado sea un array asociativo.
        $datos = json_decode(file_get_contents("php://input"), true);


        // Comprueba si los datos recibidos NO son un array.
        // El signo ! significa "NO".
        if (!is_array($datos)) {

            // Devuelve el código HTTP 400.
            // 400 significa que la petición enviada es incorrecta.
            http_response_code(400);

            // Devuelve un mensaje en formato JSON indicando que los datos no son válidos.
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);

            // Detiene la ejecución de la función.
            return;
        }


        // Envía los datos recibidos al AuthValidator.
        // validarInicioSesion() comprueba que los datos necesarios para iniciar sesión estén correctos.
        // Los errores encontrados se guardan en la variable $errores.
        $errores = $this->validator->validarInicioSesion($datos);


        // Comprueba si el array $errores NO está vacío.
        // Si hay uno o más errores, entra en este if.
        if (!empty($errores)) {

            // Devuelve el código HTTP 400.
            // Significa que los datos enviados tienen algún problema.
            http_response_code(400);

            // Devuelve los errores en formato JSON.
            echo json_encode(["errores" => $errores]);

            // Detiene la ejecución.
            // No se intenta iniciar sesión porque los datos son incorrectos.
            return;
        }


        try {
            // Intenta realizar el inicio de sesión.
            // Llama al AuthService y ejecuta su método login().
            // Le pasa el nombre de usuario y la contraseña recibidos.
            $usuario = $this->service->login(
                $datos['nombre_usuario'],
                $datos['contrasena']
            );

            // Si el login fue correcto, devuelve una respuesta JSON.
            // Informa que el inicio de sesión fue correcto.
            // También devuelve los datos del usuario obtenidos por el Service.
            echo json_encode([
                "mensaje" => "Inicio de sesión correcto",
                "usuario" => $usuario
            ]);

        } catch (RuntimeException $e) {
            // Si durante el login ocurre un RuntimeException,
            // el programa entra en este bloque.
            // Por ejemplo, podría ocurrir si el usuario o la contraseña son incorrectos.

            // Devuelve el código HTTP 401.
            // 401 significa que la autenticación no fue autorizada.
            http_response_code(401);

            // Devuelve en formato JSON el mensaje de error.
            // getMessage() obtiene el mensaje de la excepción.
            echo json_encode(["mensaje" => $e->getMessage()]);
        }
    }


    public function logout(): void
    // Función encargada de cerrar la sesión del usuario.
    {
        // Llama al método logout() del AuthService.
        // El Service se encarga de realizar la lógica necesaria para cerrar la sesión.
        $this->service->logout();

        // Devuelve un mensaje en formato JSON indicando que la sesión se cerró correctamente.
        echo json_encode(["mensaje" => "Sesión cerrada correctamente"]);
    }


    public function me(): void
    // Función que permite consultar cuál es el usuario que tiene la sesión activa.
    {
        // Llama al AuthService para obtener el usuario actualmente conectado.
        // El resultado se guarda en la variable $usuario.
        $usuario = $this->service->usuarioActual();


        // Comprueba si NO existe un usuario con sesión activa.
        if (!$usuario) {

            // Devuelve el código HTTP 401.
            // Significa que no hay una autenticación válida.
            http_response_code(401);

            // Devuelve un mensaje indicando que no existe una sesión activa.
            echo json_encode(["mensaje" => "No hay una sesión activa"]);

            // Detiene la ejecución de la función.
            return;
        }


        // Si existe un usuario conectado,
        // devuelve sus datos directamente en formato JSON.
        echo json_encode($usuario);
    }
}


