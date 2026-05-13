<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\components\services\BiometricService;
use app\components\services\AccessVerificationService;
use app\models\User;

class ApiController extends Controller
{
    public function behaviors(): array
    {
        return [
            'cors' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                ],
            ],
        ];
    }

    public function beforeAction($action): bool
    {
        if (in_array($action->id, ['enroll-callback', 'verify-access', 'get-users'], true)) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionEnrollCallback()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rawBody = Yii::$app->request->getRawBody();
        $data = json_decode($rawBody, true);

        if (!$data || !isset($data['ci'])) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Missing CI parameter'];
        }

        $ci = preg_replace('/[^0-9]/', '', $data['ci']);

        $biometricService = new BiometricService();
        $success = $biometricService->markFingerprintEnrolled($ci);

        if ($success) {
            return ['status' => 'success', 'message' => 'Fingerprint enrolled successfully'];
        }

        Yii::$app->response->statusCode = 500;
        return ['status' => 'error', 'message' => 'Failed to update user fingerprint status'];
    }

    public function actionVerifyAccess()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rawBody = Yii::$app->request->getRawBody();
        $data = json_decode($rawBody, true);

        if (!$data || !isset($data['usuario_id'])) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Missing usuario_id parameter'];
        }

        $ci = preg_replace('/[^0-9]/', '', (string)$data['usuario_id']);
        $idBiometrico = isset($data['id_biometrico']) ? (int)$data['id_biometrico'] : (int)$ci;

        $accessService = new AccessVerificationService();
        $result = $accessService->verifyAccess($ci, $idBiometrico);

        if ($result['status'] === 'error') {
            Yii::$app->response->statusCode = 500;
        }

        return $result;
    }

    public function actionGetUsers()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $users = User::find()
            ->where(['ES_BORRADO' => 0])
            ->select(['CI', 'NOMBRE_COMPLETO', 'ID_BIOMETRICO', 'HUELLA'])
            ->asArray()
            ->all();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => (int)$user['CI'],
                'user_id' => (string)$user['CI'],
                'nombre' => $user['NOMBRE_COMPLETO'],
                'biometric_id' => (int)$user['ID_BIOMETRICO'],
                'has_fingerprint' => (bool)$user['HUELLA'],
            ];
        }

        return [
            'status' => 'success',
            'total' => count($data),
            'data' => $data,
        ];
    }
}
