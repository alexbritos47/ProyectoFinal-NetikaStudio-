<?php

// Incluye el archivo que contiene la clase Database.
// __DIR__ representa la carpeta donde está este archivo.
// ../ significa subir una carpeta.
require_once __DIR__ . '/../database/Database.php';


// Clase encargada de realizar las operaciones de usuarios
// directamente sobre la base de datos.
class UsuarioRepository
{
    // Guarda la conexión PDO con la base de datos.
    // private significa que solamente se puede utilizar dentro de esta clase.
    private PDO $conexion;


    // Constructor de la clase.
    // Recibe una conexión PDO cuando se crea el Repository.
    public function __construct(PDO $conexion)
    {
        // Guarda la conexión recibida en la propiedad $conexion.
        $this->conexion = $conexion;
    }


    // Obtiene todos los usuarios registrados.
    public function obtenerTodos(): array
    {
        // Ejecuta una consulta SQL para obtener los datos de los usuarios.
        // No se selecciona la contraseña por seguridad.
        $stmt = $this->conexion->query(
            "SELECT id_usuario, nombre_usuario, nombre_completo, rol, email, estado
             FROM USUARIO"
        );

        // fetchAll() obtiene todos los registros encontrados.
        return $stmt->fetchAll();
    }


    // Busca un usuario por su ID.
    // Devuelve un arreglo con los datos o false si no existe.
    public function obtenerPorId(int $id): array|false
    {
        // Prepara una consulta SQL para buscar el usuario por ID.
        $stmt = $this->conexion->prepare(
            "SELECT id_usuario, nombre_usuario, nombre_completo, rol, email, estado
             FROM USUARIO WHERE id_usuario = :id"
        );

        // Envía el ID al parámetro :id.
        // Al utilizar parámetros preparados se reduce el riesgo de SQL Injection.
        $stmt->execute(['id' => $id]);

        // fetch() obtiene un solo registro.
        return $stmt->fetch();
    }


    // Busca un usuario por su nombre de usuario.
    // Esta función incluye la contraseña almacenada como hash.
    // Se utiliza internamente para realizar la autenticación.
    public function obtenerPorNombreUsuario(string $nombreUsuario): array|false
    {
        // Prepara una consulta para buscar el usuario por nombre de usuario.
        $stmt = $this->conexion->prepare(
            "SELECT * FROM USUARIO WHERE nombre_usuario = :nombre_usuario"
        );

        // Envía el nombre de usuario al parámetro de la consulta.
        $stmt->execute(['nombre_usuario' => $nombreUsuario]);

        // Devuelve todos los datos del usuario encontrado.
        // Incluye el hash de la contraseña.
        return $stmt->fetch();
    }


    // Busca un usuario por su dirección de correo electrónico.
    public function obtenerPorCorreo(string $email): array|false
    {
        // Prepara una consulta para buscar por email.
        $stmt = $this->conexion->prepare(
            "SELECT * FROM USUARIO WHERE email = :email"
        );

        // Envía el email al parámetro :email.
        $stmt->execute(['email' => $email]);

        // Devuelve el usuario encontrado o false si no existe.
        return $stmt->fetch();
    }


    // Crea un nuevo usuario en la base de datos.
    // Recibe los datos en un arreglo.
    // Devuelve el ID generado por MySQL.
    public function crear(array $datos): int
    {
        // Prepara la consulta INSERT para crear el usuario.
        $stmt = $this->conexion->prepare(
            "INSERT INTO USUARIO (nombre_usuario, contrasena, nombre_completo, rol, email, estado)
             VALUES (:nombre_usuario, :contrasena, :nombre_completo, :rol, :email, :estado)"
        );


        // Ejecuta la consulta enviando los datos correspondientes.
        $stmt->execute([
            // Nombre utilizado para iniciar sesión.
            'nombre_usuario'  => $datos['nombre_usuario'],

            // Convierte la contraseña original en un hash seguro.
            // PASSWORD_DEFAULT utiliza el algoritmo recomendado por PHP.
            // La contraseña original no se guarda directamente en la base de datos.
            'contrasena'      => password_hash($datos['contrasena'], PASSWORD_DEFAULT),

            // Nombre completo del usuario.
            'nombre_completo' => $datos['nombre_completo'],

            // Rol del usuario.
            // Por ejemplo: administrador, socio o cobrador.
            'rol'             => $datos['rol'],

            // Email.
            // Si no se proporciona, se guarda null.
            'email'           => $datos['email'] ?? null,

            // Estado del usuario.
            // Si no se proporciona, comienza como activo.
            'estado'          => $datos['estado'] ?? 'activo',
        ]);


        // Obtiene el ID generado automáticamente por MySQL.
        // (int) convierte el resultado a entero.
        return (int) $this->conexion->lastInsertId();
    }


    // Actualiza los datos de un usuario.
    public function actualizar(int $id, array $datos): bool
    {
        // Guarda los campos que se van a modificar.
        $campos = [];

        // Guarda los valores que serán enviados a la consulta.
        // El ID siempre se incluye.
        $parametros = ['id' => $id];


        // Lista de campos normales que se pueden modificar.
        $permitidos = [
            'nombre_usuario',
            'nombre_completo',
            'rol',
            'email',
            'estado'
        ];


        // Recorre cada uno de los campos permitidos.
        foreach ($permitidos as $campo) {

            // Comprueba si ese campo existe dentro de $datos.
            if (array_key_exists($campo, $datos)) {

                // Agrega el campo a la consulta SQL.
                // Por ejemplo: nombre_completo = :nombre_completo
                $campos[] = "$campo = :$campo";

                // Guarda el valor del campo en los parámetros.
                $parametros[$campo] = $datos[$campo];
            }
        }


        // Comprueba si se envió una nueva contraseña.
        // array_key_exists() verifica que la clave exista.
        // !empty() comprueba que no esté vacía.
        if (array_key_exists('contrasena', $datos) && !empty($datos['contrasena'])) {

            // Agrega la contraseña como campo que se va a actualizar.
            $campos[] = "contrasena = :contrasena";

            // Hashea la nueva contraseña antes de guardarla.
            // Nunca se guarda la contraseña original directamente.
            $parametros['contrasena'] = password_hash(
                $datos['contrasena'],
                PASSWORD_DEFAULT
            );
        }


        // Si no hay ningún campo para modificar,
        // no realiza la consulta y devuelve false.
        if (empty($campos)) {
            return false;
        }


        // Construye dinámicamente la consulta UPDATE.
        // implode(', ', $campos) une los campos separados por coma.
        $sql = "UPDATE USUARIO SET " . implode(', ', $campos) . " WHERE id_usuario = :id";


        // Prepara la consulta SQL.
        $stmt = $this->conexion->prepare($sql);


        // Ejecuta la consulta con todos los parámetros.
        // Devuelve true si la operación se ejecuta correctamente.
        return $stmt->execute($parametros);
    }


    // Elimina un usuario de la base de datos.
    public function eliminar(int $id): bool
    {
        // Prepara la consulta DELETE para eliminar el usuario.
        $stmt = $this->conexion->prepare(
            "DELETE FROM USUARIO WHERE id_usuario = :id"
        );

        // Envía el ID del usuario a eliminar.
        // Devuelve true si la consulta se ejecuta correctamente.
        return $stmt->execute(['id' => $id]);
    }
}

