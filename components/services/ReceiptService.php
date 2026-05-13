<?php
namespace app\components\services;

use app\models\Payment;
use app\models\User;
use app\models\Plan;

class ReceiptService
{
    /**
     * Obtiene los datos completos de un recibo para mostrar
     */
    public function getReceiptData(Payment $payment): array
    {
        $client = $payment->client;
        $admin = $payment->administrator;
        $membership = $payment->membership;
        $plan = $membership ? $membership->plan : null;

        return [
            'id_recibo' => $payment->ID_RECIBO,
            'codigo_pago' => $payment->CODIGO_PAGO,
            'nro_documento' => $payment->NRO_DOCUMENTO,
            'fecha' => $payment->FECHA,
            'monto' => $payment->MONTO,
            'tipo_pago' => $payment->TIPO_PAGO,
            'tipo_pago_label' => $payment->TIPO_PAGO === 'QR' ? 'QR' : 'Efectivo',
            'cliente' => [
                'ci' => $payment->CI_CLIENTE,
                'nombre' => $client ? $client->NOMBRE_COMPLETO : 'N/A',
                'correo' => $client ? $client->CORREO_ELECTRONICO : 'N/A',
                'tipo_cliente' => $client ? $client->TIPO_CLIENTE : 'N/A',
            ],
            'administrador' => $admin ? [
                'ci' => $admin->CI,
                'nombre' => $admin->NOMBRE_COMPLETO,
            ] : null,
            'plan' => $plan ? [
                'nombre' => $plan->NOMBRE_PLAN,
                'tipo_plan' => $plan->TIPO_PLAN,
                'tipo_cliente' => $plan->TIPO_CLIENTE,
                'monto' => $plan->MONTO,
            ] : null,
            'membresia' => $membership ? [
                'codigo' => $membership->CODIGO_MEMBRESIA,
                'fecha_inicio' => $membership->FECHA_INICIO,
                'fecha_fin' => $membership->FECHA_FIN,
                'dias_asignados' => $membership->DIAS_ASIGNADOS,
            ] : null,
        ];
    }

    /**
     * Genera el HTML del recibo para imprimir o exportar a PDF
     */
    public function generateReceiptHtml(array $receiptData): string
    {
        $fecha = date('d/m/Y H:i', strtotime($receiptData['fecha']));
        $monto = number_format((float)$receiptData['monto'], 2);
        
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago #' . $receiptData['id_recibo'] . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .receipt { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #0056b3; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #0056b3; margin: 0; font-size: 24px; }
        .header p { color: #666; margin: 5px 0 0; }
        .section { margin-bottom: 20px; }
        .section h3 { color: #0056b3; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .row:last-child { border-bottom: none; }
        .label { color: #666; font-weight: bold; }
        .value { color: #333; }
        .total { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; margin-top: 20px; }
        .total .amount { font-size: 28px; color: #28a745; font-weight: bold; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px; }
        @media print { body { background: white; } .receipt { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Gym Tecmec</h1>
            <p>Recibo de Pago #' . $receiptData['id_recibo'] . '</p>
            <p>' . $fecha . '</p>
        </div>

        <div class="section">
            <h3>Datos del Cliente</h3>
            <div class="row"><span class="label">Nombre:</span><span class="value">' . htmlspecialchars($receiptData['cliente']['nombre']) . '</span></div>
            <div class="row"><span class="label">CI:</span><span class="value">' . htmlspecialchars($receiptData['cliente']['ci']) . '</span></div>
            <div class="row"><span class="label">Tipo:</span><span class="value">' . htmlspecialchars($receiptData['cliente']['tipo_cliente']) . '</span></div>
        </div>

        <div class="section">
            <h3>Datos del Plan</h3>
            <div class="row"><span class="label">Plan:</span><span class="value">' . ($receiptData['plan'] ? htmlspecialchars($receiptData['plan']['nombre']) : 'N/A') . '</span></div>
            <div class="row"><span class="label">Tipo de Plan:</span><span class="value">' . ($receiptData['plan'] ? htmlspecialchars($receiptData['plan']['tipo_plan']) : 'N/A') . '</span></div>
            <div class="row"><span class="label">Vigencia:</span><span class="value">' . ($receiptData['membresia'] ? date('d/m/Y', strtotime($receiptData['membresia']['fecha_inicio'])) . ' - ' . date('d/m/Y', strtotime($receiptData['membresia']['fecha_fin'])) : 'N/A') . '</span></div>
        </div>

        <div class="section">
            <h3>Datos del Pago</h3>
            <div class="row"><span class="label">Metodo:</span><span class="value">' . htmlspecialchars($receiptData['tipo_pago_label']) . '</span></div>
            ' . ($receiptData['codigo_pago'] ? '<div class="row"><span class="label">Codigo Pago:</span><span class="value">' . htmlspecialchars($receiptData['codigo_pago']) . '</span></div>' : '') . '
            ' . ($receiptData['nro_documento'] ? '<div class="row"><span class="label">Nro Documento:</span><span class="value">' . htmlspecialchars($receiptData['nro_documento']) . '</span></div>' : '') . '
            ' . ($receiptData['administrador'] ? '<div class="row"><span class="label">Atendido por:</span><span class="value">' . htmlspecialchars($receiptData['administrador']['nombre']) . '</span></div>' : '<div class="row"><span class="label">Atendido por:</span><span class="value">Pago online (Cliente)</span></div>') . '
        </div>

        <div class="total">
            <p style="margin: 0; color: #666;">Total Pagado</p>
            <p class="amount">Bs. ' . $monto . '</p>
        </div>

        <div class="footer">
            <p>Este recibo es comprobante valido de pago</p>
            <p>Gym Tecmec - Sistema de Gestion de Gimnasio Universitario</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
}
