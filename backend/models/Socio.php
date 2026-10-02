
<?php



class Socio

// Esta clase representa a un socio del Club Ciclista Maragato.
{
    // Guarda el ID del socio.
    // ?int significa que puede ser un número entero o null.
    // Comienza en null porque normalmente el ID lo genera la base de datos.
    public ?int $id_socio = null;


    // Guarda el ID del usuario relacionado con este socio.
    // Puede ser un número entero o null.
    public ?int $id_usuario = null;


    // Guarda el ID del cobrador asignado al socio.
    // Puede ser un número entero o null.
    public ?int $id_cobrador = null;


    // Guarda el número que identifica al socio dentro del club.
    public string $numero_socio;


    // Guarda el nombre del socio.
    public string $nombre;


    // Guarda el apellido del socio.
    public string $apellido;


    // Guarda el tipo de documento.
    // Por ejemplo: CI, pasaporte, etc.
    public string $tipo_documento;


    // Guarda el número del documento del socio.
    public string $numero_documento;


    // Guarda la dirección del socio.
    // Puede quedar vacía, por eso permite null.
    public ?string $direccion = null;


    // Guarda el número de teléfono del socio.
    // Puede quedar vacío.
    public ?string $telefono = null;


    // Guarda el correo electrónico del socio.
    // Puede quedar vacío.
    public ?string $email = null;


    // Guarda la fecha en la que el socio ingresó al club.
    public string $fecha_ingreso;


    // Guarda el estado actual del socio.
    // Por defecto comienza como "activo".
    // Puede ser: activo, inactivo o moroso.
    public string $estado = 'activo';


    // Guarda el ID de la categoría a la que pertenece el socio.
    public int $id_categoria;
}

