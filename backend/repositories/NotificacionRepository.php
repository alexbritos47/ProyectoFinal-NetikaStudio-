
<?php



require_once __DIR__ . '/../database/Database.php';
// Incluye el archivo Database.php.
// __DIR__ representa la carpeta donde está este archivo.
// ../ significa subir una carpeta.
// Se incluye para poder trabajar con la conexión PDO a MySQL.


class NotificacionRepository

// Esta clase se encarga de realizar las consultas SQL
// relacionadas con las notificaciones.
{
    private PDO $conexion;
    // Guarda la conexión con la base de datos.
    // private significa que solamente se puede utilizar dentro de esta clase.
    // PDO es la herramienta utilizada para conectarse con MySQL.


    public function __construct(PDO $conexion)
    // Constructor de la clase.
    // Recibe una conexión PDO como parámetro.
    {
        $this->conexion = $conexion;
        // Guarda la conexión recibida dentro de la propiedad $conexion.
        // $this representa al objeto actual.
        // -> permite acceder a propiedades o métodos del objeto.
    }


    public function obtenerPorSocio(int $idSocio): array
    // Busca todas las notificaciones pertenecientes a un socio.
    // Recibe el ID del socio como número entero.
    // : array significa que devuelve un array.
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_notificacion, titulo, mensaje, fecha_envio, leida
             FROM NOTIFICACION WHERE id_socio = :id_socio ORDER BY fecha_envio DESC"
        );
        // Prepara una consulta SQL.
        //
        // SELECT indica que queremos obtener información.
        //
        // Se obtienen:
        // id_notificacion → ID de la notificación.
        // titulo → título de la notificación.
        // mensaje → contenido de la notificación.
        // fecha_envio → fecha en la que fue enviada.
        // leida → indica si el socio ya la leyó.
        //
        // FROM NOTIFICACION indica que los datos salen de esa tabla.
        //
        // WHERE id_socio = :id_socio
        // busca solamente las notificaciones del socio indicado.
        //
        // ORDER BY fecha_envio DESC
        // ordena las notificaciones de la más reciente a la más antigua.


        $stmt->execute(['id_socio' => $idSocio]);
        // Ejecuta la consulta.
        // Reemplaza el parámetro :id_socio por el ID recibido.


        return $stmt->fetchAll();
        // Obtiene todas las notificaciones encontradas.
        // Devuelve los resultados como un array.
    }


    public function obtenerPorId(int $id): array|false
    // Busca una notificación específica utilizando su ID.
    // Recibe un número entero.
    // Puede devolver un array si encuentra la notificación
    // o false si no encuentra ninguna.
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_notificacion, id_socio, titulo, mensaje, fecha_envio, leida
             FROM NOTIFICACION WHERE id_notificacion = :id"
        );
        // Prepara la consulta SQL.
        //
        // Obtiene todos los datos principales de una notificación.
        //
        // WHERE id_notificacion = :id
        // indica que solamente queremos la notificación
        // cuyo ID coincida con el recibido.


        $stmt->execute(['id' => $id]);
        // Ejecuta la consulta.
        // Reemplaza :id por el ID recibido.


        return $stmt->fetch();
        // Obtiene una sola notificación.
        // Si existe, devuelve sus datos.
        // Si no existe, devuelve false.
    }


    public function crear(int $idSocio, string $titulo, string $mensaje): int
    // Crea una nueva notificación para un socio.
    //
    // Recibe:
    // $idSocio → ID del socio que recibirá la notificación.
    // $titulo → título de la notificación.
    // $mensaje → contenido de la notificación.
    //
    // : int significa que devuelve un número entero.
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO NOTIFICACION (id_socio, titulo, mensaje) VALUES (:id_socio, :titulo, :mensaje)"
        );
        // Prepara una consulta INSERT.
        //
        // INSERT INTO sirve para agregar una nueva notificación.
        //
        // Se guardan:
        // id_socio
        // titulo
        // mensaje
        //
        // Los valores se pasan mediante parámetros.


        $stmt->execute([
            'id_socio' => $idSocio,
            'titulo' => $titulo,
            'mensaje' => $mensaje
        ]);
        // Ejecuta el INSERT.
        //
        // :id_socio recibe el ID del socio.
        // :titulo recibe el título.
        // :mensaje recibe el contenido de la notificación.


        return (int) $this->conexion->lastInsertId();
        // Obtiene el ID generado automáticamente por MySQL.
        // (int) convierte el resultado a número entero.
        // return devuelve el ID de la nueva notificación.
    }


    public function marcarLeida(int $id): bool
    // Marca una notificación como leída.
    // Recibe el ID de la notificación.
    // : bool significa que devuelve true o false.
    {
        $stmt = $this->conexion->prepare(
            "UPDATE NOTIFICACION SET leida = 1 WHERE id_notificacion = :id"
        );
        // Prepara una consulta UPDATE.
        //
        // UPDATE modifica un registro existente.
        //
        // SET leida = 1
        // cambia el campo leida a 1.
        // En este sistema, 1 significa que la notificación fue leída.
        //
        // WHERE id_notificacion = :id
        // indica qué notificación se debe modificar.


        return $stmt->execute(['id' => $id]);
        // Ejecuta la consulta.
        // Reemplaza :id por el ID recibido.
        // Devuelve true si la operación se ejecutó correctamente
        // o false si hubo un problema.
    }
}

