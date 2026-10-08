<?php



class Usuario

// Esta clase representa a los usuarios que utilizan el sistema.
{
    // Guarda el ID único del usuario.
    // ?int significa que puede ser un número entero o null.
    // Comienza en null porque normalmente el ID lo genera la base de datos.
    public ?int $id_usuario = null;


    // Guarda el nombre de usuario utilizado para iniciar sesión.
    public string $nombre_usuario;


    // Guarda la contraseña del usuario.
    // En el sistema debería almacenarse de forma segura utilizando hash.
    public string $contrasena;


    // Guarda el nombre completo de la persona.
    public string $nombre_completo;


    // Guarda el rol que tiene el usuario dentro del sistema.
    // Puede ser: administrador, socio o cobrador.
    public string $rol;


    // Guarda el correo electrónico del usuario.
    // El ? significa que puede ser un texto o null.
    // Se inicializa en null porque el email puede no estar registrado.
    public ?string $email = null;


    // Guarda el estado de la cuenta.
    // Por defecto, cuando se crea el usuario, queda como "activo".
    public string $estado = 'activo';
}
