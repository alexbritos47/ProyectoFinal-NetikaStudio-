<?php


class ConsultaController

// Esta clase se encarga de manejar las peticiones relacionadas con las consultas de los socios.

{
    // Guarda una instancia de ConsultaService.
    // El Service contiene la lógica relacionada con las consultas.
    private ConsultaService $service;

    // Guarda una instancia de ConsultaValidator.
    // El Validator se encarga de comprobar que los datos de una consulta sean correctos.
    private ConsultaValidator $validator;


    public function __construct(ConsultaService $service, ConsultaValidator $validator)
    // Constructor de la clase.
    // Se ejecuta automáticamente cuando se crea un nuevo ConsultaController.
    // Recibe el Service y el Validator.
    {
        // Guarda el ConsultaService recibido dentro de la propiedad $service.
        $this->service = $service;

        // Guarda el ConsultaValidator recibido dentro de la propiedad $validator.
        $this->validator = $validator;
    }


    public function listar(): void
    // Función encargada de obtener todas las consultas.
    {
        // Llama al método obtenerTodas() del ConsultaService.
        // El Service obtiene las consultas.
        // json_encode convierte el resultado a formato JSON.
        echo json_encode($this->service->obtenerTodas());
    }


    public function obtener(int $id): void
    // Función encargada de obtener una consulta específica.
    // Recibe como parámetro el ID de la consulta.
    // int indica que el ID debe ser un número entero.
    {
        // Llama al ConsultaService para buscar una consulta por su ID.
        // El resultado se guarda en la variable $consulta.
        $consulta = $this->service->obtenerPorId($id);


        // Comprueba si no se encontró la consulta.
        // El signo ! significa "NO".
        if (!$consulta) {

            // Devuelve el código HTTP 404.
            // 404 significa que el recurso solicitado no fue encontrado.
            http_response_code(404);

            // Devuelve un mensaje de error en formato JSON.
            echo json_encode([
                "mensaje" => "Consulta no encontrada"
            ]);

            // Detiene la ejecución de la función.
            return;
        }


        // Si la consulta existe,
        // devuelve sus datos en formato JSON.
        echo json_encode($consulta);
    }


    public function crear(): void
    // Función encargada de crear una nueva consulta.
    {
        // Lee los datos enviados en el cuerpo de la petición HTTP.
        // php://input obtiene el contenido enviado.
        // json_decode convierte el JSON recibido en un array de PHP.
        // true hace que el resultado sea un array asociativo.
        $datos = json_decode(
            file_get_contents("php://input"),
            true
        );


        // Comprueba si los datos recibidos no son un array.
        if (!is_array($datos)) {

            // Devuelve el código HTTP 400.
            // Significa que la petición enviada es incorrecta.
            http_response_code(400);

            // Devuelve un mensaje indicando que los datos no son válidos.
            echo json_encode([
                "mensaje" => "Los datos enviados no son válidos"
            ]);

            // Detiene la ejecución.
            return;
        }


        // Envía los datos al ConsultaValidator.
        // validarCrear() comprueba que los datos necesarios
        // para crear una consulta sean correctos.
        // Los errores encontrados se guardan en $errores.
        $errores = $this->validator->validarCrear($datos);


        // Comprueba si existen errores de validación.
        if (!empty($errores)) {

            // Devuelve el código HTTP 400.
            // Indica que los datos enviados tienen algún problema.
            http_response_code(400);

            // Devuelve los errores encontrados en formato JSON.
            echo json_encode([
                "errores" => $errores
            ]);

            // Detiene la ejecución.
            return;
        }


        // Llama al ConsultaService para crear una nueva consulta.
        // Convierte id_socio a un número entero.
        // Envía también el asunto y el mensaje de la consulta.
        // El ID de la nueva consulta se guarda en $id.
        $id = $this->service->crear(
            (int) $datos['id_socio'],
            $datos['asunto'],
            $datos['mensaje']
        );


        // Devuelve el código HTTP 201.
        // 201 significa que el recurso fue creado correctamente.
        http_response_code(201);


        // Devuelve un mensaje en formato JSON.
        // También devuelve el ID de la consulta creada.
        echo json_encode([
            "mensaje" => "Consulta enviada correctamente",
            "id_consulta" => $id
        ]);
    }
}

