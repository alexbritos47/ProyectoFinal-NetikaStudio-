<?php


class UsuarioController

// Este Controller se encarga de recibir y responder
// las peticiones relacionadas con los usuarios.
{
    // Guarda el Service que contiene la lógica de los usuarios.
    private UsuarioService $service;

    // Guarda el Validator que comprueba que los datos de los usuarios sean correctos.
    private UsuarioValidator $validator;


    public function __construct(UsuarioService $service, UsuarioValidator $validator)
    // Constructor de la clase.
    // Recibe el UsuarioService y el UsuarioValidator.
    {
        // Guarda el Service recibido dentro de $service.
        $this->service = $service;

        // Guarda el Validator recibido dentro de $validator.
        $this->validator = $validator;
    }


    public function listar(): void
    // Función que obtiene todos los usuarios.
    {
        // Llama al Service para obtener todos los usuarios.
        // json_encode convierte los datos de PHP a formato JSON.
        echo json_encode($this->service->obtenerUsuarios());
    }


    public function obtener(int $id): void
    // Función que busca un usuario específico.
    // Recibe el ID del usuario como número entero.
    {
        // Llama al Service para buscar el usuario por su ID.
        $usuario = $this->service->obtenerUsuario($id);

        // Comprueba si el usuario no fue encontrado.
        if (!$usuario) {

            // Código HTTP 404 significa que el recurso no fue encontrado.
            http_response_code(404);

            // Devuelve el mensaje de error en formato JSON.
            echo json_encode(["mensaje" => "Usuario no encontrado"]);

            // Detiene la ejecución de la función.
            return;
        }

        // Si el usuario existe, devuelve sus datos en formato JSON.
        echo json_encode($usuario);
    }


    public function crear(): void
    // Función encargada de crear un nuevo usuario.
    {
        // Obtiene los datos enviados en el cuerpo de la petición.
        // php://input permite leer los datos enviados.
        // json_decode convierte el JSON recibido en un array de PHP.
        $datos = json_decode(file_get_contents("php://input"), true);

        // Comprueba si los datos recibidos son un array válido.
        if (!is_array($datos)) {

            // Código 400 significa que los datos enviados son incorrectos.
            http_response_code(400);

            // Devuelve el mensaje de error.
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);

            // Detiene la función.
            return;
        }

        // Envía los datos al Validator para comprobar que sean correctos.
        $errores = $this->validator->validarCrear($datos);

        // Comprueba si el Validator encontró errores.
        if (!empty($errores)) {

            // Indica que la petición tiene datos incorrectos.
            http_response_code(400);

            // Devuelve los errores encontrados.
            echo json_encode(["errores" => $errores]);

            // Detiene la función.
            return;
        }

        // Comienza un bloque para controlar posibles errores.
        try {

            // Llama al Service para crear el usuario.
            // Guarda el ID del nuevo usuario en $id.
            $id = $this->service->crearUsuario($datos);

            // Código 201 significa que se creó correctamente.
            http_response_code(201);

            // Devuelve un mensaje de éxito y el ID del usuario creado.
            echo json_encode([
                "mensaje" => "Usuario creado correctamente",
                "id_usuario" => $id
            ]);

        // Captura un error de tipo RuntimeException.
        } catch (RuntimeException $e) {

            // Código 409 significa que existe un conflicto con la solicitud.
            // Por ejemplo, un usuario que ya existe.
            http_response_code(409);

            // Devuelve el mensaje contenido en la excepción.
            echo json_encode(["mensaje" => $e->getMessage()]);
        }
    }


    public function actualizar(int $id): void
    // Función encargada de actualizar un usuario.
    // Recibe el ID del usuario que se quiere modificar.
    {
        // Lee los datos enviados en el cuerpo de la petición.
        // json_decode convierte el JSON recibido en un array.
        $datos = json_decode(file_get_contents("php://input"), true);

        // Comprueba si los datos recibidos son válidos.
        if (!is_array($datos)) {

            // Código 400 significa que la petición tiene datos incorrectos.
            http_response_code(400);

            // Devuelve un mensaje de error.
            echo json_encode(["mensaje" => "Los datos enviados no son válidos"]);

            // Detiene la función.
            return;
        }

        // Envía los datos al Validator para comprobarlos.
        $errores = $this->validator->validarActualizar($datos);

        // Comprueba si existen errores de validación.
        if (!empty($errores)) {

            // Indica que los datos enviados son incorrectos.
            http_response_code(400);

            // Devuelve los errores encontrados.
            echo json_encode(["errores" => $errores]);

            // Detiene la función.
            return;
        }

        // Llama al Service para actualizar el usuario.
        // Le pasa el ID y los nuevos datos.
        $this->service->actualizarUsuario($id, $datos);

        // Devuelve un mensaje indicando que se actualizó correctamente.
        echo json_encode(["mensaje" => "Usuario actualizado correctamente"]);
    }


    public function eliminar(int $id): void
    // Función que elimina un usuario.
    // Recibe el ID del usuario que se quiere eliminar.
    {
        // Llama al Service para eliminar el usuario.
        $this->service->eliminarUsuario($id);

        // Devuelve un mensaje indicando que se eliminó correctamente.
        echo json_encode(["mensaje" => "Usuario eliminado correctamente"]);
    }
}

