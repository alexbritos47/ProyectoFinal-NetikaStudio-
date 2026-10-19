<?php


class SocioController

// Este Controller se encarga de recibir y responder
// las peticiones relacionadas con los socios.
{
    // Guarda el Service que contiene la lógica de los socios.
    private SocioService $service;

    // Guarda el Validator que comprueba que los datos de los socios sean correctos.
    private SocioValidator $validator;


    public function __construct(SocioService $service, SocioValidator $validator)
    // Constructor de la clase.
    // Recibe el SocioService y el SocioValidator.
    {
        // Guarda el Service recibido en la variable $service.
        $this->service = $service;

        // Guarda el Validator recibido en la variable $validator.
        $this->validator = $validator;
    }


    public function listar(): void
    // Función que obtiene todos los socios.
    {
        // Llama al Service para obtener la lista de socios.
        // json_encode convierte los datos de PHP a formato JSON.
        echo json_encode($this->service->obtenerSocios());
    }


    public function obtener(int $id): void
    // Función que busca un socio específico.
    // Recibe el ID del socio como número entero.
    {
        // Llama al Service para buscar el socio por su ID.
        $socio = $this->service->obtenerSocio($id);

        // Comprueba si no se encontró el socio.
        if (!$socio) {

            // Código HTTP 404 significa que el socio no fue encontrado.
            http_response_code(404);

            // Devuelve un mensaje de error en formato JSON.
            echo json_encode(["mensaje" => "Socio no encontrado"]);

            // Detiene la ejecución de la función.
            return;
        }

        // Si el socio existe, devuelve sus datos en formato JSON.
        echo json_encode($socio);
    }


    public function crear(): void
    // Función encargada de registrar un nuevo socio.
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
        $errores = $this->validator->validarCrear($datos);

        // Comprueba si el Validator encontró errores.
        if (!empty($errores)) {

            // Indica que los datos enviados son incorrectos.
            http_response_code(400);

            // Devuelve los errores encontrados.
            echo json_encode(["errores" => $errores]);

            // Detiene la función.
            return;
        }

        // Llama al Service para registrar el nuevo socio.
        // Si algo no se puede guardar (usuario repetido, documento
        // repetido, etc.) responde con un error claro en vez de un 500.
        try {

            $id = $this->service->registrarSocio($datos);

        } catch (InvalidArgumentException $e) {

            http_response_code(400);
            echo json_encode(["mensaje" => $e->getMessage()]);
            return;

        // OJO: PDOException va ANTES que RuntimeException porque es un
        // tipo de RuntimeException; si no, nunca se llegaría a este bloque.
        } catch (PDOException $e) {

            // 23000 = valor repetido en una columna UNIQUE.
            if ($e->getCode() === '23000') {

                http_response_code(409);
                echo json_encode([
                    "mensaje" => "Ya existe un socio con ese número de documento o número de socio."
                ]);
                return;
            }

            throw $e;

        } catch (RuntimeException $e) {

            http_response_code(409);
            echo json_encode(["mensaje" => $e->getMessage()]);
            return;
        }

        // Código 201 significa que se creó correctamente.
        http_response_code(201);

        // Devuelve un mensaje de éxito, el ID del socio y, si se creó
        // o vinculó un acceso al portal, el ID de ese usuario.
        echo json_encode([
            "mensaje" => "Socio registrado correctamente",
            "id_socio" => $id,
            "id_usuario" => $this->service->ultimoIdUsuario
        ]);
    }


    public function actualizar(int $id): void
    // Función encargada de actualizar los datos de un socio.
    // Recibe el ID del socio que se quiere modificar.
    {
        // Obtiene los datos enviados en el cuerpo de la petición.
        // json_decode convierte el JSON en un array de PHP.
        $datos = json_decode(file_get_contents("php://input"), true);

        // Comprueba si los datos recibidos son válidos.
        if (!is_array($datos)) {

            // Código 400 significa que la petición es incorrecta.
            http_response_code(400);

            // Devuelve un mensaje indicando el problema.
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);

            // Detiene la función.
            return;
        }

        // Valida los datos utilizando SocioValidator.
        $errores = $this->validator->validarActualizar($datos);

        // Comprueba si existen errores de validación.
        if (!empty($errores)) {

            // Indica que los datos enviados tienen errores.
            http_response_code(400);

            // Devuelve los errores encontrados.
            echo json_encode(["errores" => $errores]);

            // Detiene la función.
            return;
        }

        // Llama al Service para actualizar el socio.
        // Le pasa el ID y los nuevos datos.
        try {

            $this->service->actualizarSocio($id, $datos);

        } catch (InvalidArgumentException $e) {

            http_response_code(400);
            echo json_encode(["mensaje" => $e->getMessage()]);
            return;

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                http_response_code(409);
                echo json_encode([
                    "mensaje" => "Ya existe un socio con ese número de documento o número de socio."
                ]);
                return;
            }

            throw $e;

        } catch (RuntimeException $e) {

            http_response_code(409);
            echo json_encode(["mensaje" => $e->getMessage()]);
            return;
        }

        // Devuelve un mensaje indicando que se actualizó correctamente.
        echo json_encode(["mensaje" => "Socio actualizado correctamente"]);
    }


    public function cambiarEstado(int $id): void
    // Función que permite cambiar el estado de un socio.
    // Por ejemplo: activo o inactivo.
    {
        // Lee los datos enviados en el cuerpo de la petición.
        // json_decode convierte el JSON en un array.
        $datos = json_decode(file_get_contents("php://input"), true);

        // Comprueba dos cosas:
        // 1. Que los datos sean un array.
        // 2. Que exista un estado y que no esté vacío.
        if (!is_array($datos) || empty($datos['estado'])) {

            // Código 400 significa que los datos enviados son incorrectos.
            http_response_code(400);

            // Devuelve un mensaje indicando que falta el estado.
            echo json_encode(["mensaje" => "Debe indicar el nuevo estado"]);

            // Detiene la función.
            return;
        }

        // Llama al Service para cambiar el estado del socio.
        // Le pasa el ID del socio y el nuevo estado.
        $this->service->cambiarEstado($id, $datos['estado']);

        // Devuelve un mensaje indicando que el estado fue actualizado.
        echo json_encode([
            "mensaje" => "Estado del socio actualizado correctamente"
        ]);
    }


    public function eliminar(int $id): void
    // Función que elimina un socio.
    // Recibe el ID del socio que se quiere eliminar.
    {
        // Llama al Service para eliminar el socio.
        // Si el socio ya tiene recibos, pagos o cobranzas, la base de datos
        // no deja borrarlo (clave foránea): se responde con un mensaje claro.
        try {

            $this->service->eliminarSocio($id);

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                http_response_code(409);
                echo json_encode([
                    "mensaje" => "No se puede eliminar: el socio tiene recibos, pagos o cobranzas registrados."
                ]);
                return;
            }

            throw $e;
        }

        // Devuelve un mensaje indicando que fue eliminado correctamente.
        echo json_encode(["mensaje" => "Socio eliminado correctamente"]);
    }
}