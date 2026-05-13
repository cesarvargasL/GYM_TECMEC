<?php
namespace app\components\services;

class UniversityService
{
    /**
     * Simula el servicio de la universidad que valida si un CI es estudiante activo
     * En produccion, esto seria una llamada API real a la universidad
     */
    public function validateStudent(string $ci): ?array
    {
        // Simulacion: para CI que existen en la base de datos como UNIVERSITARIO, devuelve datos
        // En la vida real, esto haria una llamada HTTP a la API de la universidad

        $mockDatabase = [
            '13722192' => [
                'nombre_completo' => 'Cesar Vargas',
                'telefono' => '63755909',
                'correo_electronico' => 'cesarluisvargaslezano@gmail.com',
                'es_activo' => true,
                'tipo_cliente' => 'UNIVERSITARIO',
            ],
            '1234567' => [
                'nombre_completo' => 'Admin Principal',
                'telefono' => '77889923',
                'correo_electronico' => 'admin@tecm.com',
                'es_activo' => true,
                'tipo_cliente' => 'UNIVERSITARIO',
            ],
        ];

        // Simular delay de red
        usleep(500000); // 500ms

        if (isset($mockDatabase[$ci])) {
            return $mockDatabase[$ci];
        }

        // Si no esta en la "base de datos" de la universidad, es externo
        return null;
    }

    /**
     * Simula la generacion de factura electronica por la universidad
     */
    public function generateElectronicInvoice(array $paymentData): array
    {
        // Simulacion de factura electronica
        $invoiceNumber = 'FACT-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        
        return [
            'success' => true,
            'invoice_number' => $invoiceNumber,
            'sent_to' => $paymentData['client_email'],
            'message' => "Factura emitida a {$paymentData['client_email']}",
            'invoice_data' => [
                'numero_factura' => $invoiceNumber,
                'fecha_emision' => date('Y-m-d H:i:s'),
                'cliente' => $paymentData['client_name'],
                'ci_cliente' => $paymentData['client_ci'],
                'monto' => $paymentData['amount'],
                'concepto' => $paymentData['plan_name'],
                'tipo_pago' => $paymentData['payment_type'],
            ],
        ];
    }
}
