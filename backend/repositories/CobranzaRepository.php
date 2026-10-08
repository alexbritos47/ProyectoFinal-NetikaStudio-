<?php



require_once __DIR__ . '/../database/Database.php';
// Incluye el archivo Database.php.
// __DIR__ indica la carpeta donde está este archivo.
// ../ significa subir una carpeta.
// Se incluye para trabajar con la conexión PDO a la base de datos.


class CobranzaRepository

// Esta clase se encarga de realizar las consultas de cobranza en MySQL.
{
    private PDO $conexion;
    // Guarda la conexión a la base de datos.
    // private significa que solamente se puede utilizar dentro de esta clase.
    // PDO es el sistema utilizado para conectarse a MySQL.


    public function __construct(PDO $conexion)
    // Constructor de la clase.
    // Recibe como parámetro una conexión PDO.
    {
        $this->conexion = $conexion;
        // Guarda la conexión recibida dentro de la propiedad $conexion.
        // $this representa al objeto actual.
        // -> permite acceder a una propiedad o método del objeto.
    }


    // Socios con recibos pendientes o en estado moroso, asignados a un cobrador.
    // Este comentario explica el objetivo del siguiente método.


    public function obtenerPendientes(?int $idCobrador = null, ?string $fecha = null): array
    // Busca los socios que tienen recibos pendientes.
    // $idCobrador permite filtrar por un cobrador.
    // $fecha permite filtrar por una fecha de vencimiento.
    // Ambos parámetros pueden ser null.
    // Si no se pasan, tienen null como valor predeterminado.
    // : array significa que la función devuelve un array.
    {
        $sql = "SELECT s.id_socio, s.numero_socio, s.nombre, s.apellido, s.direccion, s.telefono,
                       s.estado, COUNT(r.id_recibo) AS recibos_pendientes
                FROM SOCIO s
                JOIN RECIBO r ON r.id_socio = s.id_socio AND r.estado = 'pendiente'";
        // Crea la consulta SQL principal.
        //
        // SELECT indica qué información queremos obtener.
        //
        // s.id_socio → ID del socio.
        // s.numero_socio → número del socio.
        // s.nombre → nombre.
        // s.apellido → apellido.
        // s.direccion → dirección.
        // s.telefono → teléfono.
        // s.estado → estado del socio.
        //
        // COUNT(r.id_recibo) cuenta cuántos recibos pendientes tiene cada socio.
        //
        // FROM SOCIO s indica que comenzamos consultando la tabla SOCIO.
        // "s" es un alias para escribir SOCIO de forma más corta.
        //
        // JOIN RECIBO r relaciona la tabla SOCIO con RECIBO.
        // "r" es un alias para la tabla RECIBO.
        //
        // r.id_socio = s.id_socio relaciona el recibo con su socio.
        //
        // r.estado = 'pendiente' hace que solamente se tengan en cuenta
        // los recibos que todavía están pendientes.


        $condiciones = [];
        // Crea un array vacío.
        // Aquí se van a guardar las condiciones adicionales de búsqueda.


        $parametros = [];
        // Crea otro array vacío.
        // Aquí se van a guardar los valores de los parámetros de la consulta SQL.


        if ($fecha) {
            // Comprueba si se recibió una fecha.

            $condiciones[] = "r.fecha_vencimiento <= :fecha";
            // Agrega una condición para buscar recibos
            // cuya fecha de vencimiento sea menor o igual a la fecha indicada.

            $parametros['fecha'] = $fecha;
            // Guarda la fecha para reemplazar el parámetro :fecha.
        }


        if ($idCobrador) {
            // Comprueba si se recibió un ID de cobrador.

            $condiciones[] = "s.id_cobrador = :id_cobrador";
            // Agrega una condición para buscar solamente
            // los socios asignados a ese cobrador.

            $parametros['id_cobrador'] = $idCobrador;
            // Guarda el ID del cobrador para reemplazar :id_cobrador.
        }


        if ($condiciones) {
            // Comprueba si existe al menos una condición.

            $sql .= " WHERE " . implode(' AND ', $condiciones);
            // Agrega las condiciones a la consulta SQL.
            //
            // implode(' AND ', $condiciones) une las condiciones utilizando AND.
            //
            // Por ejemplo, podría generar:
            //
            // WHERE r.fecha_vencimiento <= :fecha
            // AND s.id_cobrador = :id_cobrador
        }


        $sql .= " GROUP BY s.id_socio, s.numero_socio, s.nombre, s.apellido, s.direccion, s.telefono, s.estado
                  ORDER BY recibos_pendientes DESC";
        // Agrega GROUP BY para agrupar los resultados por cada socio.
        //
        // Esto permite utilizar COUNT() para saber cuántos recibos pendientes
        // tiene cada socio.
        //
        // ORDER BY ordena los resultados.
        //
        // DESC significa de mayor a menor.
        //
        // Por lo tanto, los socios con más recibos pendientes aparecen primero.


        $stmt = $this->conexion->prepare($sql);
        // Prepara la consulta SQL.
        // prepare() permite utilizar parámetros de forma segura.


        $stmt->execute($parametros);
        // Ejecuta la consulta.
        // Los valores guardados en $parametros reemplazan los parámetros
        // utilizados en la consulta, como :fecha o :id_cobrador.


        return $stmt->fetchAll();
        // Obtiene todas las filas encontradas.
        // Devuelve los resultados como un array.
    }


    public function registrarVisita(
        int $idSocio,
        int $idCobrador,
        string $resultado,
        ?string $observacion
    ): int
    // Registra una visita realizada por un cobrador.
    //
    // Recibe:
    // idSocio → identifica al socio visitado.
    // idCobrador → identifica al cobrador.
    // resultado → indica qué ocurrió durante la visita.
    // observacion → comentario opcional del cobrador.
    //
    // : int significa que devuelve un número entero.
    {
        $resultadosValidos = [
            'no_estaba',
            'no_quiso_pagar',
            'direccion_incorrecta',
            'volver_a_visitar',
            'cobro_realizado'
        ];
        // Crea una lista con los resultados de visita permitidos.
        //
        // Solamente se pueden utilizar estos valores:
        // no_estaba
        // no_quiso_pagar
        // direccion_incorrecta
        // volver_a_visitar
        // cobro_realizado


        if (!in_array($resultado, $resultadosValidos, true)) {
            // Comprueba si el resultado recibido NO está dentro
            // de la lista de resultados permitidos.
            //
            // in_array() busca un valor dentro de un array.
            // true indica que también debe coincidir el tipo de dato.

            throw new InvalidArgumentException('Resultado de visita inválido.');
            // Si el resultado no es válido, genera una excepción.
            // Esto detiene la operación y devuelve un error
            // que puede ser capturado por otra parte del sistema.
        }


        $stmt = $this->conexion->prepare(
            "INSERT INTO COBRANZA_VISITA (id_socio, id_cobrador, resultado, observacion)
             VALUES (:id_socio, :id_cobrador, :resultado, :observacion)"
        );
        // Prepara una consulta INSERT.
        //
        // INSERT INTO sirve para agregar una nueva visita
        // en la tabla COBRANZA_VISITA.
        //
        // Se guardan:
        // id_socio
        // id_cobrador
        // resultado
        // observacion
        //
        // Los valores se pasan mediante parámetros para trabajar
        // de forma más segura.


        $stmt->execute([
            'id_socio'    => $idSocio,
            'id_cobrador' => $idCobrador,
            'resultado'   => $resultado,
            'observacion' => $observacion,
        ]);
        // Ejecuta el INSERT.
        //
        // Reemplaza:
        // :id_socio → ID del socio.
        // :id_cobrador → ID del cobrador.
        // :resultado → resultado de la visita.
        // :observacion → observación del cobrador.


        return (int) $this->conexion->lastInsertId();
        // Obtiene el ID generado por MySQL para la nueva visita.
        // (int) convierte el resultado a un número entero.
        // return devuelve ese ID.
    }
}

