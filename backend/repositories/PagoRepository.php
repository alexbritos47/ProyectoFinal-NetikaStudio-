<?php


require_once __DIR__ . '/../database/Database.php';
// Incluye el archivo Database.php.
// Permite utilizar la conexión PDO con MySQL.


require_once __DIR__ . '/ReciboRepository.php';

// Se necesita porque los pagos trabajan directamente con los recibos.


class PagoRepository

// Esta clase se encarga de realizar las operaciones de pagos en MySQL.
{
    private PDO $conexion;
    // Guarda la conexión con la base de datos.
    // PDO es la herramienta utilizada para conectarse con MySQL.
    // private significa que solamente se puede utilizar dentro de esta clase.


    private ReciboRepository $recibos;
    // Guarda un objeto de ReciboRepository.
    // Se utiliza para consultar y actualizar los recibos relacionados con los pagos.


    public function __construct(PDO $conexion, ReciboRepository $recibos)
    // Constructor de la clase.
    // Recibe:
    // una conexión PDO.
    // un objeto ReciboRepository.
    {
        $this->conexion = $conexion;
        // Guarda la conexión recibida dentro del objeto.


        $this->recibos = $recibos;
        // Guarda el ReciboRepository recibido.
        // Así PagoRepository puede utilizar sus métodos.
    }


    public function obtenerTodos(): array
    // Obtiene todos los pagos registrados.
    // : array significa que devuelve un array.
    {
        $stmt = $this->conexion->query(
            "SELECT id_pago, id_socio, id_usuario, monto_total, metodo_pago, fecha_pago, anulado
             FROM PAGO ORDER BY fecha_pago DESC"
        );
        // Ejecuta una consulta SQL para obtener los pagos.
        //
        // Se obtienen:
        // id_pago → ID del pago.
        // id_socio → socio que realizó el pago.
        // id_usuario → usuario que registró el pago.
        // monto_total → dinero total pagado.
        // metodo_pago → forma de pago.
        // fecha_pago → fecha y hora del pago.
        // anulado → indica si el pago fue anulado.
        //
        // ORDER BY fecha_pago DESC
        // ordena los pagos del más reciente al más antiguo.


        return $stmt->fetchAll();
        // Obtiene todas las filas encontradas.
        // Devuelve los pagos como un array.
    }


    public function obtenerPorId(int $id): array|false
    // Busca un pago específico por su ID.
    // Puede devolver un array si lo encuentra
    // o false si no existe.
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_pago, id_socio, id_usuario, monto_total, metodo_pago, fecha_pago, anulado
             FROM PAGO WHERE id_pago = :id"
        );
        // Prepara una consulta para buscar un pago.
        // WHERE id_pago = :id indica que buscamos solamente
        // el pago cuyo ID coincida con el recibido.


        $stmt->execute(['id' => $id]);
        // Ejecuta la consulta.
        // Reemplaza :id por el ID recibido.


        $pago = $stmt->fetch();
        // Obtiene el pago encontrado.
        // Si no existe, devuelve false.
        // El resultado se guarda en $pago.


        if ($pago) {
            // Comprueba si se encontró el pago.


            $stmtDetalle = $this->conexion->prepare(
                "SELECT id_recibo FROM PAGO_RECIBO WHERE id_pago = :id"
            );
            // Prepara otra consulta.
            // Busca los recibos relacionados con ese pago.
            //
            // PAGO_RECIBO es una tabla intermedia que relaciona
            // pagos con recibos.


            $stmtDetalle->execute(['id' => $id]);
            // Ejecuta la consulta utilizando el ID del pago.


            $pago['recibos'] = array_column($stmtDetalle->fetchAll(), 'id_recibo');
            // Obtiene todos los recibos relacionados con el pago.
            //
            // fetchAll() obtiene todas las filas.
            //
            // array_column(..., 'id_recibo') obtiene solamente
            // la columna id_recibo.
            //
            // El resultado se guarda dentro de:
            // $pago['recibos']
        }


        return $pago;
        // Devuelve el pago encontrado junto con sus recibos.
    }


    /**
     * Registra un pago que cancela uno o varios recibos pendientes de un socio.
     * Los pagos son completos (no parciales): sólo se cancelan recibos en estado 'pendiente'.
     *
     * @param int $idSocio
     * @param array $idsRecibos IDs de recibos a cancelar
     * @param int|null $idUsuario quién registró el cobro (admin o cobrador)
     * @param string|null $metodoPago
     * @return int id del pago creado
     * @throws RuntimeException si algún recibo no existe, no pertenece al socio o no está pendiente
     */
    // Este bloque de comentarios explica qué hace registrarPago().
    //
    // El método registra un pago que puede cancelar uno o varios recibos.
    //
    // @param indica los parámetros que recibe la función.
    // @return indica qué devuelve.
    // @throws indica qué tipo de error puede generar.


    public function registrarPago(
        int $idSocio,
        array $idsRecibos,
        ?int $idUsuario,
        ?string $metodoPago
    ): int
    // Registra un nuevo pago.
    //
    // $idSocio → ID del socio.
    // $idsRecibos → array con los IDs de los recibos que se van a pagar.
    // $idUsuario → usuario que registró el pago, puede ser null.
    // $metodoPago → método utilizado para pagar, puede ser null.
    //
    // : int significa que devuelve el ID del pago creado.
    {
        if (empty($idsRecibos)) {
            // Comprueba si no se indicó ningún recibo.

            throw new InvalidArgumentException(
                'Debe indicar al menos un recibo a cancelar.'
            );
            // Genera un error si no se indicó ningún recibo.
        }


        $this->conexion->beginTransaction();
        // Inicia una transacción en la base de datos.
        //
        // Una transacción permite realizar varias operaciones
        // como si fueran una sola.
        //
        // Si algo falla, se pueden deshacer todas las operaciones.


        try {
            // Comienza un bloque donde se intentará realizar
            // toda la operación del pago.


            $montoTotal = 0.0;
            // Inicializa el monto total del pago en cero.
            // 0.0 indica que se trabaja con un número decimal.


            $fechaPago = date('Y-m-d H:i:s');
            // Obtiene la fecha y hora actual.
            //
            // El formato es:
            // Año-Mes-Día Hora:Minuto:Segundo
            //
            // Ejemplo:
            // 2026-10-02 11:30:00


            foreach ($idsRecibos as $idRecibo) {
                // Recorre todos los IDs de recibos recibidos.
                // Se analiza cada recibo antes de registrar el pago.


                $recibo = $this->recibos->obtenerPorId((int) $idRecibo);
                // Busca el recibo utilizando ReciboRepository.
                //
                // (int) convierte el ID a número entero.


                if (!$recibo) {
                    // Comprueba si el recibo no existe.

                    throw new RuntimeException(
                        "El recibo {$idRecibo} no existe."
                    );
                    // Genera un error indicando que el recibo no existe.
                }


                if ((int) $recibo['id_socio'] !== $idSocio) {
                    // Comprueba que el recibo pertenezca al socio
                    // que está realizando el pago.
                    //
                    // (int) convierte el ID del recibo a entero.
                    // !== significa "diferente de forma estricta".

                    throw new RuntimeException(
                        "El recibo {$idRecibo} no pertenece al socio indicado."
                    );
                    // Si pertenece a otro socio, genera un error.
                }


                if ($recibo['estado'] !== 'pendiente') {
                    // Comprueba que el recibo todavía esté pendiente.
                    //
                    // Si ya está pagado o anulado, no puede volver a pagarse.

                    throw new RuntimeException(
                        "El recibo {$idRecibo} no está pendiente de pago."
                    );
                    // Genera un error si el recibo no está pendiente.
                }


                $montoTotal += (float) $recibo['importe'];
                // Suma el importe del recibo al monto total.
                //
                // += significa "sumar y guardar".
                //
                // (float) convierte el importe a número decimal.
            }


            $stmtPago = $this->conexion->prepare(
                "INSERT INTO PAGO (id_socio, id_usuario, monto_total, metodo_pago, fecha_pago)
                 VALUES (:id_socio, :id_usuario, :monto_total, :metodo_pago, :fecha_pago)"
            );
            // Prepara la consulta para crear el pago.
            //
            // INSERT INTO agrega un nuevo registro en la tabla PAGO.
            //
            // Se guardan:
            // id_socio
            // id_usuario
            // monto_total
            // metodo_pago
            // fecha_pago


            $stmtPago->execute([
                'id_socio'    => $idSocio,
                'id_usuario'  => $idUsuario,
                'monto_total' => $montoTotal,
                'metodo_pago' => $metodoPago,
                'fecha_pago'  => $fechaPago,
            ]);
            // Ejecuta el INSERT.
            // Reemplaza los parámetros SQL por sus valores correspondientes.


            $idPago = (int) $this->conexion->lastInsertId();
            // Obtiene el ID que MySQL generó para el nuevo pago.
            // (int) convierte el resultado a entero.


            $stmtDetalle = $this->conexion->prepare(
                "INSERT INTO PAGO_RECIBO (id_pago, id_recibo) VALUES (:id_pago, :id_recibo)"
            );
            // Prepara la consulta para relacionar el pago con cada recibo.
            //
            // PAGO_RECIBO es la tabla intermedia.
            // Guarda qué recibos fueron cancelados por ese pago.


            foreach ($idsRecibos as $idRecibo) {
                // Recorre nuevamente todos los recibos que fueron pagados.


                $stmtDetalle->execute([
                    'id_pago' => $idPago,
                    'id_recibo' => (int) $idRecibo
                ]);
                // Guarda la relación entre el pago y el recibo.
                //
                // Por ejemplo:
                // id_pago = 10
                // id_recibo = 25


                $this->recibos->marcarComoPagado(
                    (int) $idRecibo,
                    $fechaPago
                );
                // Cambia el estado del recibo a pagado.
                // También registra la fecha del pago.
                //
                // Se utiliza ReciboRepository para realizar esta operación.
            }


            $this->conexion->commit();
            // Confirma la transacción.
            //
            // Esto significa que todas las operaciones realizadas
            // se guardan definitivamente en la base de datos.


            return $idPago;
            // Devuelve el ID del pago creado.
        } catch (Throwable $e) {
            // Si ocurre cualquier error dentro del try,
            // se ejecuta este bloque.
            //
            // Throwable permite capturar errores y excepciones.


            $this->conexion->rollBack();
            // Deshace todas las operaciones realizadas
            // desde el comienzo de la transacción.
            //
            // Esto evita dejar la base de datos a medias.


            throw $e;
            // Vuelve a lanzar el mismo error
            // para que otra parte del sistema pueda manejarlo.
        }
    }


    public function anular(int $idPago): bool
    // Anula un pago utilizando su ID.
    // : bool significa que devuelve true o false.
    {
        $stmt = $this->conexion->prepare(
            "UPDATE PAGO SET anulado = 1 WHERE id_pago = :id"
        );
        // Prepara una consulta UPDATE.
        //
        // Cambia el campo anulado a 1.
        // Esto indica que el pago fue anulado.
        //
        // WHERE id_pago = :id indica qué pago se debe modificar.


        return $stmt->execute(['id' => $idPago]);
        // Ejecuta la consulta.
        // Reemplaza :id por el ID del pago.
        // Devuelve true si se ejecutó correctamente
        // o false si hubo un problema.
    }
}

