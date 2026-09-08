<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/ReciboRepository.php';

class PagoRepository
{
    private PDO $conexion;
    private ReciboRepository $recibos;

    public function __construct(PDO $conexion, ReciboRepository $recibos)
    {
        $this->conexion = $conexion;
        $this->recibos = $recibos;
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->conexion->query(
            "SELECT id_pago, id_socio, id_usuario, monto_total, metodo_pago, fecha_pago, anulado
             FROM PAGO ORDER BY fecha_pago DESC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_pago, id_socio, id_usuario, monto_total, metodo_pago, fecha_pago, anulado
             FROM PAGO WHERE id_pago = :id"
        );
        $stmt->execute(['id' => $id]);
        $pago = $stmt->fetch();

        if ($pago) {
            $stmtDetalle = $this->conexion->prepare(
                "SELECT id_recibo FROM PAGO_RECIBO WHERE id_pago = :id"
            );
            $stmtDetalle->execute(['id' => $id]);
            $pago['recibos'] = array_column($stmtDetalle->fetchAll(), 'id_recibo');
        }

        return $pago;
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
    public function registrarPago(int $idSocio, array $idsRecibos, ?int $idUsuario, ?string $metodoPago): int
    {
        if (empty($idsRecibos)) {
            throw new InvalidArgumentException('Debe indicar al menos un recibo a cancelar.');
        }

        $this->conexion->beginTransaction();

        try {
            $montoTotal = 0.0;
            $fechaPago = date('Y-m-d H:i:s');

            foreach ($idsRecibos as $idRecibo) {
                $recibo = $this->recibos->obtenerPorId((int) $idRecibo);

                if (!$recibo) {
                    throw new RuntimeException("El recibo {$idRecibo} no existe.");
                }
                if ((int) $recibo['id_socio'] !== $idSocio) {
                    throw new RuntimeException("El recibo {$idRecibo} no pertenece al socio indicado.");
                }
                if ($recibo['estado'] !== 'pendiente') {
                    throw new RuntimeException("El recibo {$idRecibo} no está pendiente de pago.");
                }

                $montoTotal += (float) $recibo['importe'];
            }

            $stmtPago = $this->conexion->prepare(
                "INSERT INTO PAGO (id_socio, id_usuario, monto_total, metodo_pago, fecha_pago)
                 VALUES (:id_socio, :id_usuario, :monto_total, :metodo_pago, :fecha_pago)"
            );
            $stmtPago->execute([
                'id_socio'    => $idSocio,
                'id_usuario'  => $idUsuario,
                'monto_total' => $montoTotal,
                'metodo_pago' => $metodoPago,
                'fecha_pago'  => $fechaPago,
            ]);

            $idPago = (int) $this->conexion->lastInsertId();

            $stmtDetalle = $this->conexion->prepare(
                "INSERT INTO PAGO_RECIBO (id_pago, id_recibo) VALUES (:id_pago, :id_recibo)"
            );

            foreach ($idsRecibos as $idRecibo) {
                $stmtDetalle->execute(['id_pago' => $idPago, 'id_recibo' => (int) $idRecibo]);
                $this->recibos->marcarComoPagado((int) $idRecibo, $fechaPago);
            }

            $this->conexion->commit();

            return $idPago;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function anular(int $idPago): bool
    {
        $stmt = $this->conexion->prepare(
            "UPDATE PAGO SET anulado = 1 WHERE id_pago = :id"
        );
        return $stmt->execute(['id' => $idPago]);
    }
}
