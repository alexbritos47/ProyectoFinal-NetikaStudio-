
<?php



class CobranzaController
// Esta clase se encarga de manejar las operaciones relacionadas con las cobranzas.

{
    // Guarda una instancia de CobranzaService.
    // El Service contiene la lógica relacionada con las cobranzas.
    private CobranzaService $service;

    // Guarda una instancia de CobranzaValidator.
    // El Validator se encarga de comprobar que los datos recibidos sean correctos.
    private CobranzaValidator $validator;


    public function __construct(CobranzaService $service, CobranzaValidator $validator)
    // Constructor de la clase.
    // Se ejecuta automáticamente cuando se crea un nuevo CobranzaController.
    // Recibe el Service y el Validator.
    {
        // Guarda el CobranzaService recibido en la propiedad $service.
        $this->service = $service;

        // Guarda el CobranzaValidator recibido en la propiedad $validator.
        $this->validator = $validator;
    }


    public function pendientes(): void
    // Función encargada de obtener las cobranzas pendientes.
    {
        // Comprueba si existe el parámetro "id_cobrador" en la URL.
        // Si existe, lo convierte a número entero con (int).
        // Si no existe, guarda null.
        $idCobrador = isset($_GET['id_cobrador'])
            ? (int) $_GET['id_cobrador']
            : null;


        // Obtiene el parámetro "fecha" enviado en la URL.
        // Si no existe, utiliza null.
        $fecha = $_GET['fecha'] ?? null;


        // Llama al método obtenerPendientes() del CobranzaService.
        // Le pasa el ID del cobrador y la fecha.
        // El resultado se convierte a formato JSON y se devuelve al cliente.
        echo json_encode(
            $this->service->obtenerPendientes($idCobrador, $fecha)
        );
    }


    public function registrarVisita(): void
    // Función encargada de registrar una visita realizada por un cobrador.
    {
        // Lee los datos enviados en el cuerpo de la petición HTTP.
        // php://input obtiene el contenido enviado.
        // json_decode convierte el JSON recibido en un array de PHP.
        // true hace que el resultado sea un array asociativo.
        $datos = json_decode(
            file_get_contents("php://input"),
            true
        );


        // Comprueba si los datos recibidos NO son un array.
        if (!is_array($datos)) {

            // Devuelve el código HTTP 400.
            // Significa que los datos enviados son incorrectos.
            http_response_code(400);

            // Devuelve un mensaje de error en formato JSON.
            echo json_encode([
                "mensaje" => "Los datos enviados no son válidos"
            ]);

            // Detiene la ejecución de la función.
            return;
        }


        // Envía los datos al CobranzaValidator.
        // validarVisita() comprueba que los datos necesarios
        // para registrar la visita sean correctos.
        // Los errores encontrados se guardan en $errores.
        $errores = $this->validator->validarVisita($datos);


        // Comprueba si existen errores de validación.
        if (!empty($errores)) {

            // Devuelve el código HTTP 400.
            http_response_code(400);

            // Devuelve los errores encontrados en formato JSON.
            echo json_encode([
                "errores" => $errores
            ]);

            // Detiene la ejecución.
            return;
        }


        try {
            // Intenta registrar la visita mediante el CobranzaService.
            // Se convierte id_socio a número entero.
            // Se convierte id_cobrador a número entero.
            // Se envía también el resultado de la visita.
            // La observación es opcional.
            $id = $this->service->registrarVisita(
                (int) $datos['id_socio'],
                (int) $datos['id_cobrador'],
                $datos['resultado'],
                $datos['observacion'] ?? null
            );


            // Devuelve el código HTTP 201.
            // 201 significa que un nuevo recurso fue creado correctamente.
            http_response_code(201);


            // Devuelve un mensaje indicando que la visita fue registrada.
            // También devuelve el ID de la visita creada.
            echo json_encode([
                "mensaje" => "Visita registrada correctamente",
                "id_visita" => $id
            ]);


        } catch (InvalidArgumentException $e) {
            // Si el Service genera un InvalidArgumentException,
            // significa que alguno de los argumentos recibidos no es válido.
            // El error se captura acá.


            // Devuelve el código HTTP 400.
            // Indica que los datos enviados tienen un problema.
            http_response_code(400);


            // Devuelve el mensaje específico de la excepción.
            echo json_encode([
                "mensaje" => $e->getMessage()
            ]);
        }
    }
}
```
