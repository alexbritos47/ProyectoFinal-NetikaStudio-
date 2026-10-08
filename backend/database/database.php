<?php



class Database

// Esta clase se encarga de configurar y realizar
// la conexión con la base de datos.
{
    // Guarda el nombre o dirección del servidor donde está MySQL.
    private string $host;

    // Guarda el nombre de la base de datos.
    private string $db;

    // Guarda el usuario utilizado para conectarse a MySQL.
    private string $user;

    // Guarda la contraseña utilizada para conectarse a MySQL.
    private string $password;


    public function __construct()
    // Constructor de la clase.
    // Se ejecuta automáticamente cuando se crea un objeto Database.
    {
        // Obtiene la dirección del servidor desde una variable de entorno.
        // Si DB_HOST no está definida, utiliza "localhost".
        $this->host = getenv('DB_HOST') ?: 'localhost';

        // Obtiene el nombre de la base de datos desde una variable de entorno.
        // Si DB_NAME no está definida, utiliza "margato_db".
        $this->db = getenv('DB_NAME') ?: 'margato_db';

        // Obtiene el usuario desde una variable de entorno.
        // Si DB_USER no está definido, utiliza "root".
        $this->user = getenv('DB_USER') ?: 'root';

        // Obtiene la contraseña desde una variable de entorno.
        // Si DB_PASSWORD no está definida, utiliza una contraseña vacía.
        $this->password = getenv('DB_PASSWORD') ?: '';
    }


    public function conectar(): PDO
    // Función que realiza la conexión con MySQL.
    // : PDO significa que esta función devuelve un objeto PDO.
    {
        // Crea una nueva conexión utilizando PDO.
        $conexion = new PDO(

            // Cadena de conexión.
            // mysql indica que utilizaremos MySQL.
            // host indica dónde está el servidor.
            // dbname indica qué base de datos vamos a utilizar.
            // charset=utf8mb4 permite trabajar correctamente con caracteres especiales.
            "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",

            // Usuario utilizado para conectarse a MySQL.
            $this->user,

            // Contraseña utilizada para conectarse a MySQL.
            $this->password
        );


        // Configura PDO para que lance excepciones cuando ocurra un error.
        // Esto permite detectar y controlar errores de la base de datos.
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        // Indica que los resultados de las consultas serán devueltos
        // como arrays asociativos.
        // Por ejemplo: ["nombre" => "Juan"].
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


        // Desactiva la emulación de consultas preparadas.
        // Esto permite utilizar consultas preparadas reales de MySQL
        // y ayuda a mejorar la seguridad frente a SQL Injection.
        $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


        // Devuelve la conexión para que pueda ser utilizada
        // por los Repository y otras partes del sistema.
        return $conexion;
    }
}

