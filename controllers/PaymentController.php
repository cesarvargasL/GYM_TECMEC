<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\shared\enums\Roles;
use app\models\User;
use app\models\Payment;
use app\models\Plan;

class PaymentController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'process-qr' => ['POST'],
                    'process-cash' => ['POST'],
                    'search-clients' => ['GET'],
                    'validate-student' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/payment/index');
    }

    public function actionSearchClients()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $search = Yii::$app->request->get('q', '');
        
        $clientsQuery = User::find()
            ->where(['ROL' => 'CLIENTE', 'ES_BORRADO' => 0]);

        if (!empty($search)) {
            $clientsQuery->andWhere(['or',
                ['like', 'NOMBRE_COMPLETO', $search],
                ['like', 'CI', $search],
                ['like', 'CORREO_ELECTRONICO', $search],
            ]);
        }

        $clients = $clientsQuery->limit(50)->all();

        $results = [];
        foreach ($clients as $client) {
            $results[] = [
                'ci' => $client->CI,
                'nombre' => $client->NOMBRE_COMPLETO,
                'correo' => $client->CORREO_ELECTRONICO,
                'tipo_cliente' => $client->TIPO_CLIENTE,
            ];
        }

        return ['results' => $results];
    }

    public function actionValidateStudent()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ci = Yii::$app->request->post('ci');
        if (empty($ci)) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'CI requerido'];
        }

        $universityService = new \app\components\services\UniversityService();
        $studentData = $universityService->validateStudent($ci);

        if ($studentData) {
            return [
                'status' => 'success',
                'es_universitario' => true,
                'es_activo' => $studentData['es_activo'],
                'datos' => $studentData,
            ];
        }

        return [
            'status' => 'success',
            'es_universitario' => false,
            'es_activo' => false,
        ];
    }

    public function actionProcessQr()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $clientCi = $request->post('client_ci');
        $planId = (int)$request->post('plan_id');

        if (empty($clientCi) || empty($planId)) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Datos incompletos'];
        }

        $membershipService = new \app\components\services\MembershipService();
        $adminCi = Yii::$app->user->identity->isAdmin() ? Yii::$app->user->identity->CI : null;

        $membership = $membershipService->createMembership($clientCi, $planId, $adminCi);
        if (!$membership) {
            Yii::$app->response->statusCode = 500;
            return ['status' => 'error', 'message' => 'Error al crear membresia'];
        }

        $paymentGateway = new \app\components\services\PaymentGatewayService();
        $payment = $paymentGateway->processQRPayment($membership, $adminCi);
        if (!$payment) {
            Yii::$app->response->statusCode = 500;
            return ['status' => 'error', 'message' => 'Error al procesar pago QR'];
        }

        $receiptService = new \app\components\services\ReceiptService();
        $receiptData = $receiptService->getReceiptData($payment);

        $universityService = new \app\components\services\UniversityService();
        $invoiceResult = $universityService->generateElectronicInvoice([
            'client_email' => $receiptData['cliente']['correo'],
            'client_name' => $receiptData['cliente']['nombre'],
            'client_ci' => $receiptData['cliente']['ci'],
            'amount' => $receiptData['monto'],
            'plan_name' => $receiptData['plan']['nombre'] ?? 'N/A',
            'payment_type' => $receiptData['tipo_pago_label'],
        ]);

        return [
            'status' => 'success',
            'message' => 'Pago QR procesado exitosamente',
            'membership_code' => $membership->CODIGO_MEMBRESIA,
            'payment_id' => $payment->ID_RECIBO,
            'receipt' => $receiptData,
            'invoice' => $invoiceResult,
        ];
    }

    public function actionProcessCash()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $clientCi = $request->post('client_ci');
        $planId = (int)$request->post('plan_id');

        if (empty($clientCi) || empty($planId)) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Datos incompletos'];
        }

        $membershipService = new \app\components\services\MembershipService();
        $adminCi = Yii::$app->user->identity->isAdmin() ? Yii::$app->user->identity->CI : null;

        $membership = $membershipService->createMembership($clientCi, $planId, $adminCi);
        if (!$membership) {
            Yii::$app->response->statusCode = 500;
            return ['status' => 'error', 'message' => 'Error al crear membresia'];
        }

        $paymentGateway = new \app\components\services\PaymentGatewayService();
        $payment = $paymentGateway->processCashPayment($membership, $adminCi);
        if (!$payment) {
            Yii::$app->response->statusCode = 500;
            return ['status' => 'error', 'message' => 'Error al procesar pago en efectivo'];
        }

        $receiptService = new \app\components\services\ReceiptService();
        $receiptData = $receiptService->getReceiptData($payment);

        return [
            'status' => 'success',
            'message' => 'Pago en efectivo registrado exitosamente',
            'membership_code' => $membership->CODIGO_MEMBRESIA,
            'payment_id' => $payment->ID_RECIBO,
            'receipt' => $receiptData,
        ];
    }

    public function actionGetReceipt()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $paymentId = Yii::$app->request->get('id');
        if (empty($paymentId)) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'ID de recibo requerido'];
        }

        $payment = Payment::findOne($paymentId);
        if (!$payment) {
            Yii::$app->response->statusCode = 404;
            return ['status' => 'error', 'message' => 'Recibo no encontrado'];
        }

        $receiptService = new \app\components\services\ReceiptService();
        $receiptData = $receiptService->getReceiptData($payment);

        return ['status' => 'success', 'receipt' => $receiptData];
    }

    public function actionExportReceiptPdf()
    {
        $paymentId = Yii::$app->request->get('id');
        if (empty($paymentId)) {
            throw new \yii\web\BadRequestHttpException('ID de recibo requerido');
        }

        $payment = Payment::findOne($paymentId);
        if (!$payment) {
            throw new \yii\web\NotFoundHttpException('Recibo no encontrado');
        }

        $receiptService = new \app\components\services\ReceiptService();
        $receiptData = $receiptService->getReceiptData($payment);
        $html = $receiptService->generateReceiptHtml($receiptData);

        return $html;
    }

    public function actionClientReceipts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $clientCi = Yii::$app->user->identity->CI;
        
        $payments = Payment::find()
            ->where(['CI_CLIENTE' => $clientCi, 'ES_BORRADO' => 0])
            ->orderBy(['FECHA' => SORT_DESC])
            ->with(['membership.plan', 'administrator'])
            ->all();

        $receiptService = new \app\components\services\ReceiptService();
        $receipts = [];
        foreach ($payments as $payment) {
            $receipts[] = $receiptService->getReceiptData($payment);
        }

        return ['status' => 'success', 'receipts' => $receipts];
    }

    public function actionCheckActiveMembership()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ci = Yii::$app->request->get('ci', '');
        if (empty($ci)) {
            return ['hasActive' => false];
        }

        $membershipService = new \app\components\services\MembershipService();
        $active = $membershipService->getActiveMembershipForClient($ci);

        if ($active) {
            return [
                'hasActive' => true,
                'endDate' => date('d/m/Y', strtotime($active->FECHA_FIN)),
                'remainingDays' => $active->DIAS_DISPONIBLES,
            ];
        }

        return ['hasActive' => false];
    }

    public function actionClientSelfRenew()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $planId = (int)Yii::$app->request->post('plan_id');
        if (empty($planId)) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Plan no seleccionado'];
        }

        $clientCi = Yii::$app->user->identity->CI;

        $membershipService = new \app\components\services\MembershipService();
        $membership = $membershipService->createMembership($clientCi, $planId);
        if (!$membership) {
            Yii::$app->response->statusCode = 500;
            return ['status' => 'error', 'message' => 'Error al crear membresia'];
        }

        $paymentGateway = new \app\components\services\PaymentGatewayService();
        $payment = $paymentGateway->processClientSelfPayment($membership);
        if (!$payment) {
            Yii::$app->response->statusCode = 500;
            return ['status' => 'error', 'message' => 'Error al procesar pago'];
        }

        $receiptService = new \app\components\services\ReceiptService();
        $receiptData = $receiptService->getReceiptData($payment);

        return [
            'status' => 'success',
            'message' => 'Renovacion exitosa',
            'membership_code' => $membership->CODIGO_MEMBRESIA,
            'receipt' => $receiptData,
        ];
    }
}
