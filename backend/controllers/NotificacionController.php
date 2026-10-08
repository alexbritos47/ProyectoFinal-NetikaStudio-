<?php


class NotificacionController

// El Controller se encarga de recibir las peticiones y devolver respuestas.

{
    // Guarda el servicio que contiene la lógica de las notificaciones.
    private NotificacionService $service;

    // Guarda el validador que controla que los datos recibidos sean correctos.
    private NotificacionValidator $validator;


    public function __construct(NotificacionService $service, NotificacionValidator $validator)
    // Constructor de la clase.
    // Recibe el Service y el Validator cuando se crea el Controller.
    {
        // Guarda el Service recibido dentro de la variable $service.
        $this->service = $service;

        // Guarda el Validator recibido dentro de la variable $validator.
        $this->validator = $validator;
    }


    public function listar(): void
    // Función que permite obtener las notificaciones de un socio.
    // : void significa que la función no devuelve un valor directamente.
    {
        // Comprueba si existe id_socio en la URL.
        // Si existe, lo convierte a número entero.
        // Si no existe, guarda null.
        $idSocio = isset($_GET['id_socio']) ? (int) $_GET['id_socio'] : null;

        // Comprueba si no se recibió un id_socio válido.
        if (!$idSocio) {

            // Indica que la petición tiene un error del cliente.
            // 400 significa "Bad Request".
            http_response_code(400);

            // Devuelve un mensaje en formato JSON.
            echo json_encode(["mensaje" => "Debe indicar id_socio"]);

            // Detiene la función para que no continúe.
            return;
        }

        // Llama al Service para obtener las notificaciones
        // correspondientes al socio indicado.
        // json_encode convierte los datos de PHP a formato JSON.
        echo json_encode($this->service->obtenerPorSocio($idSocio));
    }


    public function crear(): void
    // Función encargada de crear/enviar una nueva notificación.
    {
        // Obtiene los datos enviados en el cuerpo de la petición.
        // php://input permite leer los datos enviados por POST, por ejemplo.
        // json_decode convierte el JSON recibido en un array de PHP.
        $datos = json_decode(file_get_contents("php://input"), true);

        // Comprueba que los datos recibidos sean realmente un array.
        if (!is_array($datos)) {

            // Indica que los datos enviados no son válidos.
            http_response_code(400);

            // Devuelve el mensaje de error en JSON.
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);

            // Detiene la ejecución de la función.
            return;
        }

        // Envía los datos al Validator para comprobar
        // que tengan los campos necesarios y sean correctos.
        $errores = $this->validator->validarCrear($datos);

        // Comprueba si el Validator encontró errores.
        if (!empty($errores)) {

            // Indica que la petición tiene datos incorrectos.
            http_response_code(400);

            // Devuelve los errores encontrados en formato JSON.
            echo json_encode(["errores" => $errores]);

            // Detiene la función.
            return;
        }

        // Llama al Service para enviar la notificación.
        // Convierte id_socio a número entero.
        // También envía el título y el mensaje.
        $id = $this->service->enviar(
            (int) $datos['id_socio'],
            $datos['titulo'],
            $datos['mensaje']
        );

        // 201 significa que se creó correctamente un recurso.
        http_response_code(201);

        // Devuelve un mensaje de éxito y el ID de la nueva notificación.
        echo json_encode([
            "mensaje" => "Notificación enviada correctamente",
            "id_notificacion" => $id
        ]);
    }


    public function marcarLeida(int $id): void
    // Función que marca una notificación como leída.
    // Recibe el ID de la notificación como número entero.
    {
        // Llama al Service para marcar la notificación como leída.
        // Guarda el resultado en $notificacion.
        $notificacion = $this->service->marcarLeida($id);

        // Comprueba si no se encontró la notificación.
        if (!$notificacion) {

            // 404 significa que el recurso no fue encontrado.
            http_response_code(404);

            // Devuelve un mensaje indicando que no existe.
            echo json_encode(["mensaje" => "Notificación no encontrada"]);

            // Detiene la función.
            return;
        }

        // Si la notificación existe y fue marcada como leída,
        // devuelve la información en formato JSON.
        echo json_encode($notificacion);
    }
}

