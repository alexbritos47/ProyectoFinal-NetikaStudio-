<?php

class Socio
{
    public ?int $id_socio = null;
    public ?int $id_usuario = null;
    public ?int $id_cobrador = null;
    public string $numero_socio;
    public string $nombre;
    public string $apellido;
    public string $tipo_documento;
    public string $numero_documento;
    public ?string $direccion = null;
    public ?string $telefono = null;
    public ?string $email = null;
    public string $fecha_ingreso;
    public string $estado = 'activo'; // activo | inactivo | moroso
    public int $id_categoria;
}
