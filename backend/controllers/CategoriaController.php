<?php



class CategoriaController

// Esta clase se encarga de recibir y manejar las peticiones relacionadas con las categorías.

{
    // Guarda una instancia de CategoriaService.
    // El Service contiene la lógica de las categorías.
    private CategoriaService $service;

    // Guarda una instancia de CategoriaValidator.
    // El Validator se encarga de validar los datos de las categorías.
    private CategoriaValidator $validator;


    public function __construct(CategoriaService $service, CategoriaValidator $validator)
    // Constructor de la clase.
    // Se ejecuta automáticamente cuando se crea un nuevo CategoriaController.
    // Recibe el Service y el Validator.
    {
        // Guarda el CategoriaService recibido en la propiedad $service.
        $this->service = $service;

        // Guarda el CategoriaValidator recibido en la propiedad $validator.
        $this->validator = $validator;
    }


    public function listar(): void
    // Función encargada de obtener y mostrar todas las categorías.
    {
        // Llama al método obtenerTodas() del CategoriaService.
        // El Service se encarga de obtener las categorías.
        // json_encode convierte el resultado en formato JSON.
        echo json_encode($this->service->obtenerTodas());
    }


    public function obtener(int $id): void
    // Función encargada de obtener una categoría específica.
    // Recibe como parámetro $id.
    // int significa que el ID debe ser un número entero.
    {
        // Llama al Service para buscar una categoría utilizando su ID.
        // El resultado se guarda en $categoria.
        $categoria = $this->service->obtenerPorId($id);


        // Comprueba si no se encontró ninguna categoría.
        // El signo ! significa "NO".
        if (!$categoria) {

            // Devuelve el código HTTP 404.
            // 404 significa que el recurso solicitado no fue encontrado.
            http_response_code(404);

            // Devuelve un mensaje en formato JSON.
            echo json_encode(["mensaje" => "Categoría no encontrada"]);

            // Detiene la ejecución de la función.
            return;
        }


        // Si la categoría existe,
        // la devuelve en formato JSON.
        echo json_encode($categoria);
    }


    public function crear(): void
    // Función encargada de crear una nueva categoría.
    {
        // Lee los datos enviados en el cuerpo de la petición HTTP.
        // php://input obtiene los datos recibidos.
        // json_decode convierte el JSON recibido en un array de PHP.
        // true hace que se devuelva un array asociativo.
        $datos = json_decode(file_get_contents("php://input"), true);


        // Comprueba si los datos recibidos no son un array.
        if (!is_array($datos)) {

            // Devuelve el código HTTP 400.
            // Significa que la petición enviada es incorrecta.
            http_response_code(400);

            // Devuelve un mensaje indicando que los datos no son válidos.
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);

            // Detiene la ejecución.
            return;
        }


        // Envía los datos al CategoriaValidator.
        // validarCrear() comprueba si los datos necesarios para crear
        // una categoría son correctos.
        // Los errores encontrados se guardan en $errores.
        $errores = $this->validator->validarCrear($datos);


        // Comprueba si existen errores de validación.
        if (!empty($errores)) {

            // Devuelve el código HTTP 400.
            http_response_code(400);

            // Devuelve los errores en formato JSON.
            echo json_encode(["errores" => $errores]);

            // Detiene la ejecución.
            return;
        }


        // Llama al CategoriaService para crear la categoría.
        // Le pasa los datos recibidos.
        // El ID de la nueva categoría se guarda en $id.
        $id = $this->service->crear($datos);


        // Devuelve el código HTTP 201.
        // 201 significa que un nuevo recurso fue creado correctamente.
        http_response_code(201);


        // Devuelve un JSON indicando que la categoría fue creada.
        // También devuelve el ID de la categoría nueva.
        echo json_encode([
            "mensaje" => "Categoría creada correctamente",
            "id_categoria" => $id
        ]);
    }


    public function actualizar(int $id): void
    // Función encargada de actualizar una categoría existente.
    // Recibe el ID de la categoría que queremos modificar.
    {
        // Lee los datos enviados en el cuerpo de la petición.
        // Convierte el JSON recibido en un array de PHP.
        $datos = json_decode(file_get_contents("php://input"), true);


        // Comprueba si los datos recibidos no son un array.
        if (!is_array($datos)) {

            // Devuelve el código HTTP 400.
            http_response_code(400);

            // Devuelve un mensaje indicando que los datos no son válidos.
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);

            // Detiene la ejecución.
            return;
        }


        // Envía los datos al Validator.
        // validarActualizar() comprueba si los datos necesarios
        // para modificar una categoría son correctos.
        $errores = $this->validator->validarActualizar($datos);


        // Comprueba si existen errores de validación.
        if (!empty($errores)) {

            // Devuelve el código HTTP 400.
            http_response_code(400);

            // Devuelve los errores en formato JSON.
            echo json_encode(["errores" => $errores]);

            // Detiene la ejecución.
            return;
        }


        // Llama al CategoriaService para actualizar la categoría.
        // Le pasa el ID de la categoría y los nuevos datos.
        $this->service->actualizar($id, $datos);


        // Devuelve un mensaje indicando que la categoría fue actualizada correctamente.
        echo json_encode([
            "mensaje" => "Categoría actualizada correctamente"
        ]);
    }


    public function eliminar(int $id): void
    // Función encargada de eliminar una categoría.
    // Recibe el ID de la categoría que queremos eliminar.
    {
        // Llama al CategoriaService y le indica qué categoría debe eliminar.
        $this->service->eliminar($id);


        // Devuelve un mensaje indicando que la categoría fue eliminada correctamente.
        echo json_encode([
            "mensaje" => "Categoría eliminada correctamente"
        ]);
    }
}