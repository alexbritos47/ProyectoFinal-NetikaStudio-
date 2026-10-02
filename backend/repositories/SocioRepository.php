
<?php

// Incluye el archivo que contiene la clase Database.
// Se usa __DIR__ para indicar la carpeta actual de este archivo.
// ../ significa subir una carpeta.
require_once __DIR__ . '/../database/Database.php';


// Clase encargada de realizar las operaciones de socios en la base de datos.
class SocioRepository
{
    // Guarda la conexión PDO a la base de datos.
    // private significa que solo se puede usar dentro de esta clase.
    private PDO $conexion;


    // Constructor de la clase.
    // Recibe una conexión PDO cuando se crea el Repository.
    public function __construct(PDO $conexion)
    {
        // Guarda la conexión recibida en la propiedad $conexion.
        $this->conexion = $conexion;
    }


    // Obtiene todos los socios registrados.
    // : array significa que devuelve un arreglo.
    public function obtenerTodos(): array
    {
        // Ejecuta una consulta SQL para obtener todos los datos de los socios.
        $stmt = $this->conexion->query(
            "SELECT id_socio, id_usuario, id_cobrador, numero_socio, nombre, apellido,
                    tipo_documento, numero_documento, direccion, telefono, email,
                    fecha_ingreso, estado, id_categoria
             FROM SOCIO"
        );

        // fetchAll() obtiene todas las filas encontradas.
        // Se devuelve el resultado como un arreglo.
        return $stmt->fetchAll();
    }


    // Busca un socio por su ID.
    // Recibe un número entero y devuelve un arreglo o false si no existe.
    public function obtenerPorId(int $id): array|false
    {
        // Prepara una consulta SQL para buscar un socio por su ID.
        $stmt = $this->conexion->prepare(
            "SELECT id_socio, id_usuario, id_cobrador, numero_socio, nombre, apellido,
                    tipo_documento, numero_documento, direccion, telefono, email,
                    fecha_ingreso, estado, id_categoria
             FROM SOCIO WHERE id_socio = :id"
        );

        // Envía el valor del ID al parámetro :id.
        // Esto ayuda a evitar SQL Injection.
        $stmt->execute(['id' => $id]);

        // fetch() obtiene una sola fila.
        // Si no encuentra el socio, devuelve false.
        return $stmt->fetch();
    }


    // Busca un socio utilizando su número de documento.
    public function obtenerPorDocumento(string $numeroDocumento): array|false
    {
        // Prepara una consulta para buscar el documento.
        $stmt = $this->conexion->prepare(
            "SELECT * FROM SOCIO WHERE numero_documento = :numero_documento"
        );

        // Envía el número de documento al parámetro de la consulta.
        $stmt->execute(['numero_documento' => $numeroDocumento]);

        // Devuelve el socio encontrado o false si no existe.
        return $stmt->fetch();
    }


    // Obtiene todos los socios que están asignados a un determinado cobrador.
    public function obtenerPorCobrador(int $idCobrador): array
    {
        // Prepara una consulta para buscar socios por el ID del cobrador.
        $stmt = $this->conexion->prepare(
            "SELECT id_socio, numero_socio, nombre, apellido, direccion, telefono, estado
             FROM SOCIO WHERE id_cobrador = :id_cobrador"
        );

        // Envía el ID del cobrador a la consulta.
        $stmt->execute(['id_cobrador' => $idCobrador]);

        // Devuelve todos los socios encontrados.
        return $stmt->fetchAll();
    }


    // Crea un nuevo socio en la base de datos.
    // Recibe los datos del socio en un arreglo.
    // Devuelve el ID generado por la base de datos.
    public function crear(array $datos): int
    {
        // Prepara la consulta INSERT para agregar un nuevo socio.
        $stmt = $this->conexion->prepare(
            "INSERT INTO SOCIO (id_usuario, id_cobrador, numero_socio, nombre, apellido,
                                 tipo_documento, numero_documento, direccion, telefono, email,
                                 fecha_ingreso, estado, id_categoria)
             VALUES (:id_usuario, :id_cobrador, :numero_socio, :nombre, :apellido,
                     :tipo_documento, :numero_documento, :direccion, :telefono, :email,
                     :fecha_ingreso, :estado, :id_categoria)"
        );

        // Ejecuta el INSERT enviando los valores correspondientes.
        $stmt->execute([
            // ?? null significa que si no existe el dato,
            // se utiliza null.
            'id_usuario'       => $datos['id_usuario'] ?? null,

            // El socio puede no tener un cobrador asignado.
            'id_cobrador'      => $datos['id_cobrador'] ?? null,

            // Número que identifica al socio.
            'numero_socio'     => $datos['numero_socio'],

            // Nombre del socio.
            'nombre'           => $datos['nombre'],

            // Apellido del socio.
            'apellido'         => $datos['apellido'],

            // Tipo de documento, por ejemplo CI.
            'tipo_documento'   => $datos['tipo_documento'],

            // Número del documento.
            'numero_documento' => $datos['numero_documento'],

            // Dirección. Si no se envía, queda en null.
            'direccion'        => $datos['direccion'] ?? null,

            // Teléfono. Si no se envía, queda en null.
            'telefono'         => $datos['telefono'] ?? null,

            // Email. Si no se envía, queda en null.
            'email'            => $datos['email'] ?? null,

            // Fecha de ingreso.
            // Si no se envía, utiliza la fecha actual.
            'fecha_ingreso'    => $datos['fecha_ingreso'] ?? date('Y-m-d'),

            // Estado del socio.
            // Si no se envía, comienza como activo.
            'estado'           => $datos['estado'] ?? 'activo',

            // Categoría a la que pertenece el socio.
            'id_categoria'     => $datos['id_categoria'],
        ]);

        // Obtiene el ID generado automáticamente por MySQL.
        // Se convierte a entero con (int).
        return (int) $this->conexion->lastInsertId();
    }


    // Actualiza los datos de un socio.
    public function actualizar(int $id, array $datos): bool
    {
        // Guarda los campos que se van a modificar.
        $campos = [];

        // Guarda los parámetros que se enviarán a la consulta.
        // El ID siempre se incluye.
        $parametros = ['id' => $id];


        // Lista de campos que está permitido modificar.
        $permitidos = [
            'id_usuario', 'id_cobrador', 'numero_socio', 'nombre', 'apellido',
            'tipo_documento', 'numero_documento', 'direccion', 'telefono', 'email',
            'fecha_ingreso', 'estado', 'id_categoria'
        ];


        // Recorre todos los campos permitidos.
        foreach ($permitidos as $campo) {

            // Verifica si el campo existe dentro del arreglo $datos.
            if (array_key_exists($campo, $datos)) {

                // Agrega el campo a la consulta SQL.
                // Ejemplo: nombre = :nombre
                $campos[] = "$campo = :$campo";

                // Guarda el valor correspondiente al parámetro.
                $parametros[$campo] = $datos[$campo];
            }
        }


        // Si no hay ningún campo para modificar,
        // devuelve false y no realiza el UPDATE.
        if (empty($campos)) {
            return false;
        }


        // Construye dinámicamente la consulta UPDATE.
        // implode(', ', $campos) une todos los campos con coma.
        $sql = "UPDATE SOCIO SET " . implode(', ', $campos) . " WHERE id_socio = :id";


        // Prepara la consulta SQL.
        $stmt = $this->conexion->prepare($sql);


        // Ejecuta la consulta con todos los parámetros.
        // Devuelve true si se ejecutó correctamente.
        return $stmt->execute($parametros);
    }


    // Cambia solamente el estado de un socio.
    // Por ejemplo: activo o inactivo.
    public function cambiarEstado(int $id, string $estado): bool
    {
        // Prepara la consulta para modificar el estado.
        $stmt = $this->conexion->prepare(
            "UPDATE SOCIO SET estado = :estado WHERE id_socio = :id"
        );

        // Envía el estado y el ID a la consulta.
        // Devuelve true si la consulta se ejecuta correctamente.
        return $stmt->execute(['estado' => $estado, 'id' => $id]);
    }


    // Elimina un socio de la base de datos.
    public function eliminar(int $id): bool
    {
        // Prepara la consulta DELETE.
        $stmt = $this->conexion->prepare(
            "DELETE FROM SOCIO WHERE id_socio = :id"
        );

        // Envía el ID del socio a eliminar.
        // Devuelve true si se ejecuta correctamente.
        return $stmt->execute(['id' => $id]);
    }


    // Obtiene solamente los socios que están activos.
    public function obtenerActivos(): array
    {
        // Ejecuta una consulta que busca socios cuyo estado sea activo.
        $stmt = $this->conexion->query(
            "SELECT id_socio, id_categoria FROM SOCIO WHERE estado = 'activo'"
        );

        // Devuelve todos los socios activos encontrados.
        return $stmt->fetchAll();
    }
}

