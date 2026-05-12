<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\shared\enums\Roles;

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
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/payment/index');
    }

    public function actionProcessQr(): Response
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

        return [
            'status' => 'success',
            'message' => 'Pago QR procesado exitosamente',
            'membership_code' => $membership->CODIGO_MEMBRESIA,
            'payment_id' => $payment->ID_RECIBO,
        ];
    }

    public function actionProcessCash(): Response
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

        return [
            'status' => 'success',
            'message' => 'Pago en efectivo registrado exitosamente',
            'membership_code' => $membership->CODIGO_MEMBRESIA,
            'payment_id' => $payment->ID_RECIBO,
        ];
    }

    public function actionClientSelfRenew(): Response
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

        return [
            'status' => 'success',
            'message' => 'Renovacion exitosa',
            'membership_code' => $membership->CODIGO_MEMBRESIA,
        ];
    }
}
