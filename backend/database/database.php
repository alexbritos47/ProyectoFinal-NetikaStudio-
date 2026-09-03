<?php

class Database
{
    private string $host = "localhost";
    private string $db   = "margato_db";
    private string $user = "root";
    private string $password = "";

    public function conectar(): PDO
    {
        try {
            $conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
                $this->user,
                $this->password
            );

            $conexion->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $conexion->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

            $conexion->setAttribute(
                PDO::ATTR_EMULATE_PREPARES,
                false
            );

            return $conexion;

        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}