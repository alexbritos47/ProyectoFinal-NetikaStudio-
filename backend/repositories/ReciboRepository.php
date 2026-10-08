<?php



require_once __DIR__ . '/../database/Database.php';
// Incluye el archivo Database.php.
// __DIR__ representa la carpeta donde está este archivo.
// ../ significa subir una carpeta.
// Se utiliza para poder trabajar con la conexión PDO a MySQL.


class ReciboRepository

// Esta clase se encarga de realizar las operaciones relacionadas
// con los recibos directamente en la base de datos.
{
    private PDO $conexion;
    // Guarda la conexión con la base de datos.
    // private significa que solamente se puede utilizar dentro de esta clase.
    // PDO es la herramienta utilizada para comunicarse con MySQL.


    public function __construct(PDO $conexion)
    // Constructor de la clase.
    // Recibe una conexión PDO como parámetro.
    {
        $this->conexion = $conexion;
        // Guarda la conexión recibida dentro de la propiedad $conexion.
        // $this representa al objeto actual.
        // -> permite acceder a una propiedad o método del objeto.
    }


    public function obtenerTodos(): array
    // Obtiene todos los recibos registrados.
    // : array significa que devuelve un array.
    {
        $stmt = $this->conexion->query(
            "SELECT id_recibo, numero_recibo, id_socio, periodo, importe,
                    fecha_vencimiento, estado, fecha_pago
             FROM RECIBO ORDER BY fecha_vencimiento DESC"
        );
        // Ejecuta una consulta SQL para obtener todos los recibos.
        //
        // Se obtienen:
        // id_recibo → ID del recibo.
        // numero_recibo → número del recibo.
        // id_socio → socio al que pertenece.
        // periodo → período correspondiente.
        // importe → monto del recibo.
        // fecha_vencimiento → fecha límite para pagar.
        // estado → pendiente, pagado o anulado.
        // fecha_pago → fecha en la que fue pagado.
        //
        // ORDER BY fecha_vencimiento DESC
        // ordena los recibos desde el vencimiento más reciente
        // al más antiguo.


        return $stmt->fetchAll();
        // Obtiene todas las filas de la consulta.
        // Devuelve los resultados como un array.
    }


    public function obtenerPorId(int $id): array|false
    // Busca un recibo por su ID.
    // Recibe un número entero.
    // Devuelve un array si encuentra el recibo
    // o false si no existe.
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_recibo, numero_recibo, id_socio, periodo, importe,
                    fecha_vencimiento, estado, fecha_pago
             FROM RECIBO WHERE id_recibo = :id"
        );
        // Prepara una consulta SQL.
        //
        // WHERE id_recibo = :id
        // indica que solamente queremos el recibo
        // cuyo ID coincida con el recibido.


        $stmt->execute(['id' => $id]);
        // Ejecuta la consulta.
        // Reemplaza :id por el ID recibido.


        return $stmt->fetch();
        // Obtiene una sola fila.
        // Si encuentra el recibo, devuelve sus datos.
        // Si no encuentra nada, devuelve false.
    }


    public function obtenerPorSocio(int $idSocio): array
    // Busca todos los recibos pertenecientes a un socio.
    // Recibe el ID del socio.
    // Devuelve un array.
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_recibo, numero_recibo, periodo, importe, fecha_vencimiento, estado, fecha_pago
             FROM RECIBO WHERE id_socio = :id_socio ORDER BY periodo DESC"
        );
        // Prepara una consulta para obtener los recibos del socio.
        //
        // WHERE id_socio = :id_socio
        // busca solamente los recibos de ese socio.
        //
        // ORDER BY periodo DESC
        // ordena los períodos desde el más reciente al más antiguo.


        $stmt->execute(['id_socio' => $idSocio]);
        // Ejecuta la consulta.
        // Reemplaza :id_socio por el ID recibido.


        return $stmt->fetchAll();
        // Obtiene todos los recibos encontrados.
    }


    public function obtenerPendientesPorSocio(int $idSocio): array
    // Busca solamente los recibos pendientes de un socio.
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_recibo, numero_recibo, periodo, importe, fecha_vencimiento
             FROM RECIBO WHERE id_socio = :id_socio AND estado = 'pendiente'
             ORDER BY fecha_vencimiento ASC"
        );
        // Prepara la consulta.
        //
        // Busca los recibos que:
        // pertenecen al socio indicado.
        // Y tienen estado "pendiente".
        //
        // ORDER BY fecha_vencimiento ASC
        // los ordena desde el vencimiento más antiguo al más reciente.
        //
        // ASC significa de menor a mayor.


        $stmt->execute(['id_socio' => $idSocio]);
        // Ejecuta la consulta con el ID del socio.


        return $stmt->fetchAll();
        // Devuelve todos los recibos pendientes encontrados.
    }


    public function contarPendientesPorSocio(int $idSocio): int
    // Cuenta cuántos recibos pendientes tiene un socio.
    // Devuelve un número entero.
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM RECIBO WHERE id_socio = :id_socio AND estado = 'pendiente'"
        );
        // Prepara una consulta que cuenta los recibos.
        //
        // COUNT(*) cuenta la cantidad de registros.
        //
        // Solamente cuenta los recibos del socio indicado
        // cuyo estado sea "pendiente".


        $stmt->execute(['id_socio' => $idSocio]);
        // Ejecuta la consulta.


        return (int) $stmt->fetchColumn();
        // fetchColumn() obtiene el primer valor de la primera fila.
        // En este caso obtiene el número de recibos.
        // (int) convierte el resultado a entero.
    }


    public function existePeriodo(int $idSocio, string $periodo): bool
    // Comprueba si un socio ya tiene un recibo para determinado período.
    // Devuelve true o false.
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM RECIBO WHERE id_socio = :id_socio AND periodo = :periodo"
        );
        // Prepara una consulta que cuenta los recibos.
        //
        // Busca por:
        // ID del socio.
        // Período del recibo.


        $stmt->execute([
            'id_socio' => $idSocio,
            'periodo' => $periodo
        ]);
        // Ejecuta la consulta con los parámetros recibidos.


        return (int) $stmt->fetchColumn() > 0;
        // Obtiene la cantidad de recibos encontrados.
        // (int) convierte el resultado a entero.
        //
        // > 0 comprueba si existe al menos un recibo.
        //
        // Si encuentra uno o más → true.
        // Si no encuentra ninguno → false.
    }


    public function crear(array $datos): int
    // Crea un nuevo recibo.
    // Recibe los datos en un array.
    // Devuelve el ID del recibo creado.
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO RECIBO (numero_recibo, id_socio, periodo, importe, fecha_vencimiento, estado, fecha_pago)
             VALUES (:numero_recibo, :id_socio, :periodo, :importe, :fecha_vencimiento, :estado, :fecha_pago)"
        );
        // Prepara una consulta INSERT.
        //
        // INSERT INTO agrega un nuevo recibo a la tabla RECIBO.
        //
        // Se guardan:
        // numero_recibo
        // id_socio
        // periodo
        // importe
        // fecha_vencimiento
        // estado
        // fecha_pago


        $stmt->execute([
            'numero_recibo'     => $datos['numero_recibo'],
            'id_socio'          => $datos['id_socio'],
            'periodo'           => $datos['periodo'],
            'importe'           => $datos['importe'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'estado'            => $datos['estado'] ?? 'pendiente',
            'fecha_pago'        => $datos['fecha_pago'] ?? null,
        ]);
        // Ejecuta el INSERT.
        //
        // Obtiene los valores desde el array $datos.
        //
        // ?? significa que si el valor no existe o es null,
        // se utiliza el valor de la derecha.
        //
        // Por eso:
        // estado → si no se indica, queda "pendiente".
        // fecha_pago → si no se indica, queda null.


        return (int) $this->conexion->lastInsertId();
        // Obtiene el ID generado automáticamente por MySQL.
        // (int) convierte el resultado a entero.
        // Devuelve el ID del nuevo recibo.
    }


    // Marca un conjunto de recibos como pagados.
    // Este método se utiliza dentro de una transacción de PagoRepository.


    public function marcarComoPagado(int $id, string $fechaPago): bool
    // Cambia un recibo pendiente a pagado.
    // Recibe el ID del recibo y la fecha del pago.
    // Devuelve true o false.
    {
        $stmt = $this->conexion->prepare(
            "UPDATE RECIBO SET estado = 'pagado', fecha_pago = :fecha_pago
             WHERE id_recibo = :id AND estado = 'pendiente'"
        );
        // Prepara una consulta UPDATE.
        //
        // Cambia:
        // estado → "pagado".
        // fecha_pago → fecha en que se realizó el pago.
        //
        // WHERE indica que solamente se modifica:
        // el recibo indicado.
        // Y solamente si todavía está pendiente.


        return $stmt->execute([
            'fecha_pago' => $fechaPago,
            'id' => $id
        ]);
        // Ejecuta la actualización.
        // Reemplaza los parámetros por sus valores.
        // Devuelve true si se ejecutó correctamente.
    }


    public function anular(int $id): bool
    // Anula un recibo utilizando su ID.
    // Devuelve true o false.
    {
        $stmt = $this->conexion->prepare(
            "UPDATE RECIBO SET estado = 'anulado' WHERE id_recibo = :id"
        );
        // Prepara una consulta UPDATE.
        //
        // Cambia el estado del recibo a "anulado".
        // WHERE indica qué recibo se debe modificar.


        return $stmt->execute(['id' => $id]);
        // Ejecuta la consulta.
        // Reemplaza :id por el ID del recibo.
    }


    public function obtenerMorosos(): array
    // Obtiene los socios considerados morosos según la regla del proyecto.
    // Devuelve un array.
    {
        // Regla del proyecto: moroso = más de un recibo pendiente.
        // Esta es la regla utilizada para identificar morosos.


        $stmt = $this->conexion->query(
            "SELECT s.id_socio, s.numero_socio, s.nombre, s.apellido,
                    COUNT(r.id_recibo) AS recibos_pendientes
             FROM SOCIO s
             JOIN RECIBO r ON r.id_socio = s.id_socio AND r.estado = 'pendiente'
             GROUP BY s.id_socio, s.numero_socio, s.nombre, s.apellido
             HAVING COUNT(r.id_recibo) > 1"
        );
        // Ejecuta una consulta SQL para buscar socios morosos.
        //
        // SELECT obtiene los datos del socio.
        //
        // COUNT(r.id_recibo) cuenta los recibos pendientes.
        //
        // FROM SOCIO s comienza desde la tabla SOCIO.
        //
        // JOIN RECIBO r relaciona los socios con sus recibos.
        //
        // r.estado = 'pendiente'
        // hace que solamente se cuenten recibos pendientes.
        //
        // GROUP BY agrupa los recibos por socio.
        //
        // HAVING COUNT(r.id_recibo) > 1
        // solamente deja los socios que tienen más de un recibo pendiente.


        return $stmt->fetchAll();
        // Obtiene todos los socios encontrados.
    }


    public function porcentajeCobranza(string $fecha): array
    // Calcula el porcentaje de recibos cobrados hasta una determinada fecha.
    // Recibe una fecha.
    // Devuelve los resultados en un array.
    {
        $stmt = $this->conexion->prepare(
            "SELECT
                COUNT(*) AS total_recibos,
                SUM(CASE WHEN estado = 'pagado' THEN 1 ELSE 0 END) AS recibos_pagados
             FROM RECIBO
             WHERE fecha_vencimiento <= :fecha AND estado != 'anulado'"
        );
        // Prepara una consulta para calcular estadísticas de cobranza.
        //
        // COUNT(*) cuenta todos los recibos incluidos.
        //
        // SUM(CASE WHEN ...)
        // cuenta solamente los recibos cuyo estado sea "pagado".
        //
        // ELSE 0 significa que los que no estén pagados no suman.
        //
        // WHERE fecha_vencimiento <= :fecha
        // considera recibos cuyo vencimiento sea hasta la fecha indicada.
        //
        // AND estado != 'anulado'
        // excluye los recibos anulados.
        //
        // != significa "diferente de".


        $stmt->execute(['fecha' => $fecha]);
        // Ejecuta la consulta utilizando la fecha recibida.


        $fila = $stmt->fetch();
        // Obtiene el resultado de la consulta.
        // Se guarda en el array $fila.


        $total = (int) ($fila['total_recibos'] ?? 0);
        // Obtiene la cantidad total de recibos.
        //
        // Si el valor es null o no existe, utiliza 0.
        // Después lo convierte a entero.


        $pagados = (int) ($fila['recibos_pagados'] ?? 0);
        // Obtiene la cantidad de recibos pagados.
        // Si no existe el valor, utiliza 0.
        // Lo convierte a entero.


        return [
            'fecha' => $fecha,
            // Devuelve la fecha utilizada para el cálculo.

            'total_recibos' => $total,
            // Devuelve la cantidad total de recibos.

            'recibos_pagados' => $pagados,
            // Devuelve cuántos recibos fueron pagados.

            'porcentaje' => $total > 0
                ? round(($pagados / $total) * 100, 2)
                : 0.0,
            // Calcula el porcentaje de cobranza.
            //
            // Si hay recibos:
            // pagados / total × 100.
            //
            // round(..., 2) redondea el resultado a 2 decimales.
            //
            // Si no hay recibos, devuelve 0.0.
        ];
    }


    public function totalIngresos(?string $desde = null, ?string $hasta = null): float
    // Calcula el total de ingresos obtenidos mediante recibos pagados.
    //
    // $desde → fecha inicial opcional.
    // $hasta → fecha final opcional.
    //
    // Ambos pueden ser null.
    //
    // : float significa que devuelve un número decimal.
    {
        $sql = "SELECT COALESCE(SUM(importe), 0) FROM RECIBO WHERE estado = 'pagado'";
        // Crea la consulta SQL para sumar los importes.
        //
        // SUM(importe) suma todos los importes.
        //
        // WHERE estado = 'pagado'
        // solamente considera recibos pagados.
        //
        // COALESCE(..., 0)
        // hace que el resultado sea 0 si no hay registros para sumar.


        $parametros = [];
        // Crea un array vacío para guardar los parámetros de la consulta.


        if ($desde) {
            // Comprueba si se recibió una fecha inicial.

            $sql .= " AND fecha_pago >= :desde";
            // Agrega una condición para considerar pagos
            // desde esa fecha en adelante.

            $parametros['desde'] = $desde;
            // Guarda la fecha inicial para reemplazar :desde.
        }


        if ($hasta) {
            // Comprueba si se recibió una fecha final.

            $sql .= " AND fecha_pago <= :hasta";
            // Agrega una condición para considerar pagos
            // hasta esa fecha.

            $parametros['hasta'] = $hasta;
            // Guarda la fecha final para reemplazar :hasta.
        }


        $stmt = $this->conexion->prepare($sql);
        // Prepara la consulta SQL completa.


        $stmt->execute($parametros);
        // Ejecuta la consulta utilizando los parámetros correspondientes.


        return (float) $stmt->fetchColumn();
        // Obtiene el primer valor de la primera fila.
        // En este caso es el total de ingresos.
        // (float) convierte el resultado a número decimal.
    }
}

