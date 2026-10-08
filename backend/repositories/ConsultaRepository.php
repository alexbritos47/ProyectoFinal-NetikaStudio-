<?php



require_once __DIR__ . '/../database/Database.php';
// Incluye el archivo Database.php.
// __DIR__ representa la carpeta donde está este archivo.
// ../ significa subir una carpeta.
// Se utiliza para tener disponible la conexión con la base de datos.


class ConsultaRepository

// Esta clase se encarga de realizar las consultas SQL relacionadas
// con las consultas enviadas por los socios.
{
    private PDO $conexion;
    // Guarda la conexión con la base de datos.
    // private significa que solamente se puede utilizar dentro de esta clase.
    // PDO es la herramienta utilizada para conectarse con MySQL.


    public function __construct(PDO $conexion)
    // Constructor de la clase.
    // Recibe una conexión PDO cuando se crea el Repository.
    {
        $this->conexion = $conexion;
        // Guarda la conexión recibida en la propiedad $conexion.
        // $this representa al objeto actual.
        // -> permite acceder a propiedades o métodos del objeto.
    }


    public function obtenerTodas(): array
    // Función que obtiene todas las consultas.
    // : array significa que devuelve un array.
    {
        $stmt = $this->conexion->query(
            "SELECT id_consulta, id_socio, asunto, mensaje, respuesta, estado, fecha
             FROM CONSULTA ORDER BY fecha DESC"
        );
        // Ejecuta una consulta SQL.
        //
        // SELECT indica que queremos obtener información.
        //
        // Se solicitan los siguientes campos:
        // id_consulta → ID de la consulta.
        // id_socio → socio que realizó la consulta.
        // asunto → tema de la consulta.
        // mensaje → mensaje enviado por el socio.
        // respuesta → respuesta del administrador.
        // estado → estado de la consulta.
        // fecha → fecha en que se realizó.
        //
        // FROM CONSULTA indica que los datos vienen de la tabla CONSULTA.
        //
        // ORDER BY fecha DESC ordena las consultas por fecha.
        // DESC significa de más reciente a más antigua.


        return $stmt->fetchAll();
        // fetchAll() obtiene todas las filas encontradas.
        // return devuelve todos esos datos.
    }


    public function obtenerPorId(int $id): array|false
    // Busca una consulta específica utilizando su ID.
    // Recibe un número entero llamado $id.
    // Puede devolver un array si encuentra la consulta
    // o false si no existe.
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_consulta, id_socio, asunto, mensaje, respuesta, estado, fecha
             FROM CONSULTA WHERE id_consulta = :id"
        );
        // Prepara la consulta SQL.
        //
        // WHERE id_consulta = :id indica que solamente queremos
        // la consulta cuyo ID coincida con el parámetro :id.
        //
        // prepare() permite utilizar una consulta preparada
        // y trabajar de forma más segura con los valores.


        $stmt->execute(['id' => $id]);
        // Ejecuta la consulta.
        // Reemplaza el parámetro :id por el valor recibido.


        return $stmt->fetch();
        // fetch() obtiene una sola fila.
        // Si encuentra la consulta, devuelve sus datos.
        // Si no encuentra ninguna, devuelve false.
    }


    public function crear(int $idSocio, string $asunto, string $mensaje): int
    // Crea una nueva consulta.
    //
    // Recibe:
    // $idSocio → ID del socio que realiza la consulta.
    // $asunto → tema de la consulta.
    // $mensaje → contenido de la consulta.
    //
    // : int significa que devuelve un número entero.
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO CONSULTA (id_socio, asunto, mensaje) VALUES (:id_socio, :asunto, :mensaje)"
        );
        // Prepara una consulta INSERT.
        // INSERT INTO sirve para agregar una nueva consulta a la tabla.
        //
        // Se guardan:
        // id_socio
        // asunto
        // mensaje
        //
        // Los valores se pasan mediante parámetros.


        $stmt->execute([
            'id_socio' => $idSocio,
            'asunto' => $asunto,
            'mensaje' => $mensaje
        ]);
        // Ejecuta el INSERT.
        //
        // :id_socio recibe el ID del socio.
        // :asunto recibe el asunto.
        // :mensaje recibe el mensaje.


        return (int) $this->conexion->lastInsertId();
        // Obtiene el ID generado automáticamente por MySQL.
        // (int) convierte el resultado a número entero.
        // return devuelve el ID de la nueva consulta.
    }


    public function responder(int $id, string $respuesta): bool
    // Permite responder una consulta existente.
    //
    // $id → ID de la consulta que queremos responder.
    // $respuesta → texto de la respuesta.
    //
    // : bool significa que devuelve true o false.
    {
        $stmt = $this->conexion->prepare(
            "UPDATE CONSULTA SET respuesta = :respuesta, estado = 'respondida' WHERE id_consulta = :id"
        );
        // Prepara una consulta UPDATE.
        //
        // UPDATE sirve para modificar información existente.
        //
        // respuesta = :respuesta
        // guarda la respuesta enviada.
        //
        // estado = 'respondida'
        // cambia automáticamente el estado de la consulta a "respondida".
        //
        // WHERE id_consulta = :id
        // indica qué consulta se debe modificar.


        return $stmt->execute([
            'respuesta' => $respuesta,
            'id' => $id
        ]);
        // Ejecuta la consulta.
        //
        // :respuesta recibe el texto de la respuesta.
        // :id recibe el ID de la consulta.
        //
        // Devuelve true si la operación se ejecutó correctamente
        // o false si hubo un problema.
    }
}

