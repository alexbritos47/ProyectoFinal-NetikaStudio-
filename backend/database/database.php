<?php

class Database
{
    private string $host;
    private string $db;
    private string $user;
    private string $password;

    public function __construct()
    {
        // Permite configurar la conexión por variables de entorno;
        // si no están definidas, usa valores por defecto de desarrollo local.
        $this->host     = getenv('DB_HOST') ?: 'localhost';
        $this->db       = getenv('DB_NAME') ?: 'margato_db';
        $this->user     = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
    }

    public function conectar(): PDO
    {
        $conexion = new PDO(
            "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
            $this->user,
            $this->password
        );

        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $conexion;
    }
}
