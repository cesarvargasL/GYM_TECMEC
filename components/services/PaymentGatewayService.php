<?php
namespace app\components\services;

use Yii;
use app\models\Payment;
use app\models\Membership;

class PaymentGatewayService
{
    public function processQRPayment(Membership $membership, string $adminCi = null): ?Payment
    {
        $mockData = $this->simulateQRPayment();

        $payment = new Payment();
        $payment->CODIGO_MEMBRESIA = $membership->CODIGO_MEMBRESIA;
        $payment->CI_CLIENTE = $membership->CI_CLIENTE;
        $payment->CI_ADMINISTRADOR = $adminCi;
        $payment->MONTO = $membership->plan->MONTO ?? 0;
        $payment->FECHA = date('Y-m-d H:i:s');
        $payment->TIPO_PAGO = 'QR';
        $payment->CODIGO_PAGO = $mockData['codigo_pago'];
        $payment->NRO_DOCUMENTO = $mockData['nro_documento'];

        if ($payment->save(false)) {
            return $payment;
        }

        return null;
    }

    public function processCashPayment(Membership $membership, string $adminCi = null): ?Payment
    {
        $payment = new Payment();
        $payment->CODIGO_MEMBRESIA = $membership->CODIGO_MEMBRESIA;
        $payment->CI_CLIENTE = $membership->CI_CLIENTE;
        $payment->CI_ADMINISTRADOR = $adminCi;
        $payment->MONTO = $membership->plan->MONTO ?? 0;
        $payment->FECHA = date('Y-m-d H:i:s');
        $payment->TIPO_PAGO = 'EFECTIVO';
        $payment->CODIGO_PAGO = null;
        $payment->NRO_DOCUMENTO = null;

        if ($payment->save(false)) {
            return $payment;
        }

        return null;
    }

    public function processClientSelfPayment(Membership $membership): ?Payment
    {
        $mockData = $this->simulateQRPayment();

        $payment = new Payment();
        $payment->CODIGO_MEMBRESIA = $membership->CODIGO_MEMBRESIA;
        $payment->CI_CLIENTE = $membership->CI_CLIENTE;
        $payment->CI_ADMINISTRADOR = null;
        $payment->MONTO = $membership->plan->MONTO ?? 0;
        $payment->FECHA = date('Y-m-d H:i:s');
        $payment->TIPO_PAGO = 'QR';
        $payment->CODIGO_PAGO = $mockData['codigo_pago'];
        $payment->NRO_DOCUMENTO = $mockData['nro_documento'];

        if ($payment->save(false)) {
            return $payment;
        }

        return null;
    }

    private function simulateQRPayment(): array
    {
        $codigoPago = 'QR-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $nroDocumento = 'DOC-' . date('Ymd') . '-' . rand(1000, 9999);

        return [
            'codigo_pago' => $codigoPago,
            'nro_documento' => $nroDocumento,
        ];
    }
}
