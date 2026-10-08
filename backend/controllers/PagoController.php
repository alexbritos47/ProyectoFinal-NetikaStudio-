<?php


class PagoController

// Este Controller se encarga de recibir y responder
// las peticiones relacionadas con los pagos.
{
    // Guarda el servicio que contiene la lógica de los pagos.
    private PagoService $service;

    // Guarda el validador que comprueba los datos de los pagos.
    private PagoValidator $validator;


    public function __construct(PagoService $service, PagoValidator $validator)
    // Constructor de la clase.
    // Recibe el Service y el Validator.
    {
        // Guarda el Service recibido en la variable $service.
        $this->service = $service;

        // Guarda el Validator recibido en la variable $validator.
        $this->validator = $validator;
    }


    public function listar(): void
    // Función que obtiene todos los pagos.
    // : void significa que no devuelve un valor directamente.
    {
        // Llama al Service para obtener todos los pagos.
        // json_encode convierte los datos de PHP a formato JSON.
        echo json_encode($this->service->obtenerTodos());
    }


    public function obtener(int $id): void
    // Función que busca un pago específico.
    // Recibe el ID del pago como número entero.
    {
        // Llama al Service para buscar el pago por su ID.
        $pago = $this->service->obtenerPorId($id);

        // Comprueba si no se encontró el pago.
        if (!$pago) {

            // Código HTTP 404 significa que el recurso no fue encontrado.
            http_response_code(404);

            // Devuelve el mensaje de error en formato JSON.
            echo json_encode(["mensaje" => "Pago no encontrado"]);

            // Detiene la ejecución de la función.
            return;
        }

        // Si el pago existe, lo devuelve en formato JSON.
        echo json_encode($pago);
    }


    // Esta función registra un pago.
    // El pago puede cancelar uno o varios recibos.
    // Puede utilizarse desde administración o desde el panel del cobrador.
    public function crear(): void
    // Función encargada de registrar un nuevo pago.
    {
        // Obtiene los datos enviados en el cuerpo de la petición.
        // php://input permite leer los datos enviados.
        // json_decode convierte el JSON recibido en un array de PHP.
        $datos = json_decode(file_get_contents("php://input"), true);

        // Comprueba si los datos recibidos son un array válido.
        if (!is_array($datos)) {

            // Código 400 significa que la petición tiene datos incorrectos.
            http_response_code(400);

            // Devuelve el mensaje de error en formato JSON.
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);

            // Detiene la función.
            return;
        }

        // Envía los datos al Validator para comprobar que sean correctos.
        $errores = $this->validator->validarRegistrar($datos);

        // Comprueba si el Validator encontró errores.
        if (!empty($errores)) {

            // Indica que los datos enviados son incorrectos.
            http_response_code(400);

            // Devuelve los errores encontrados.
            echo json_encode(["errores" => $errores]);

            // Detiene la función.
            return;
        }

        // Comienza un bloque para controlar posibles errores.
        try {

            // Llama al Service para registrar el pago.
            $idPago = $this->service->registrarPago(

                // Convierte id_socio a número entero.
                (int) $datos['id_socio'],

                // Envía la lista de recibos que se están pagando.
                $datos['recibos'],

                // Comprueba si existe id_usuario.
                // Si existe, lo convierte a entero.
                // Si no existe, utiliza null.
                isset($datos['id_usuario']) ? (int) $datos['id_usuario'] : null,

                // Obtiene el método de pago.
                // Si no fue enviado, utiliza null.
                $datos['metodo_pago'] ?? null
            );

            // Código 201 significa que se creó correctamente.
            http_response_code(201);

            // Devuelve un mensaje de éxito y el ID del pago creado.
            echo json_encode([
                "mensaje" => "Pago registrado correctamente",
                "id_pago" => $idPago
            ]);

        // Captura dos tipos de errores que pueden ocurrir durante el registro.
        } catch (InvalidArgumentException | RuntimeException $e) {

            // Código 400 indica que la petición no pudo procesarse
            // debido a los datos enviados.
            http_response_code(400);

            // Devuelve el mensaje del error.
           // $e->getMessage() significa obtener el texto del error.
            echo json_encode(["mensaje" => $e->getMessage()]);
        }
    }


    public function eliminar(int $id): void
    // Función que anula un pago.
    // Recibe el ID del pago como número entero.
    {
        // Llama al Service para anular el pago.
        $this->service->anular($id);

        // Devuelve un mensaje indicando que el pago fue anulado.
        echo json_encode(["mensaje" => "Pago anulado correctamente"]);
    }
}

