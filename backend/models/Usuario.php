<?php

class Usuario
{
    public ?int $id_usuario = null;
    public string $nombre_usuario;
    public string $contrasena;
    public string $nombre_completo;
    public string $rol; // administrador | socio | cobrador
    public ?string $email = null;
    public string $estado = 'activo';
}
