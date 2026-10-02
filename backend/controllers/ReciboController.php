
<?php


class ReciboController

// Este Controller se encarga de gestionar las peticiones
// relacionadas con los recibos.
{
    // Guarda el Service que contiene la lógica de los recibos.
    private ReciboService $service;


    public function __construct(ReciboService $service)
    // Constructor de la clase.
    // Recibe el ReciboService cuando se crea el Controller.
    {
        // Guarda el Service recibido dentro de la variable $service.
        $this->service = $service;
    }


    public function listar(): void
    // Función que obtiene todos los recibos.
    // : void significa que no devuelve un valor directamente.
    {
        // Llama al Service para obtener todos los recibos.
        // json_encode convierte los datos de PHP a formato JSON.
        echo json_encode($this->service->obtenerTodos());
    }


    public function obtener(int $id): void
    // Función que busca un recibo específico.
    // Recibe el ID del recibo como número entero.
    {
        // Llama al Service para buscar el recibo por su ID.
        $recibo = $this->service->obtenerPorId($id);

        // Comprueba si no se encontró el recibo.
        if (!$recibo) {

            // Código HTTP 404 significa que el recurso no fue encontrado.
            http_response_code(404);

            // Devuelve un mensaje de error en formato JSON.
            echo json_encode(["mensaje" => "Recibo no encontrado"]);

            // Detiene la ejecución de la función.
            return;
        }

        // Si el recibo existe, lo devuelve en formato JSON.
        echo json_encode($recibo);
    }


    public function porSocio(int $idSocio): void
    // Función que obtiene los recibos correspondientes a un socio.
    // Recibe el ID del socio como número entero.
    {
        // Llama al Service para buscar los recibos de ese socio.
        echo json_encode($this->service->obtenerPorSocio($idSocio));
    }


    public function generarAnuales(): void
    // Función que genera los recibos correspondientes a un año.
    {
        // Lee los datos enviados en el cuerpo de la petición.
        // php://input obtiene los datos enviados.
        // json_decode convierte el JSON en un array de PHP.
        // ?? [] significa que si no hay datos, utiliza un array vacío.
        $datos = json_decode(file_get_contents("php://input"), true) ?? [];

        // Obtiene el año enviado en los datos.
        // Si no se envió un año, utiliza el año actual.
        // (int) convierte el valor a número entero.
        $anio = (int) ($datos['anio'] ?? date('Y'));

        // Llama al Service para generar los recibos del año indicado.
        $resultado = $this->service->generarAnuales($anio);

        // Código 201 significa que la operación creó recursos correctamente.
        http_response_code(201);

        // Devuelve un objeto JSON con el mensaje y el año.
        echo json_encode([

            // Mensaje indicando que los recibos fueron generados.
            "mensaje" => "Recibos generados correctamente",

            // Muestra el año utilizado para generar los recibos.
            "anio" => $anio,

        // El operador + agrega al resultado los datos que devolvió el Service.
        ] + $resultado);
    }


    public function anular(int $id): void
    // Función que permite anular un recibo.
    // Recibe el ID del recibo como número entero.
    {
        // Llama al Service para realizar la anulación.
        $this->service->anular($id);

        // Devuelve un mensaje indicando que el recibo fue anulado.
        echo json_encode(["mensaje" => "Recibo anulado correctamente"]);
    }
}

