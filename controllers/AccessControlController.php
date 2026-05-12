<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;
use app\shared\enums\Roles;
use app\components\services\AccessVerificationService;
use app\models\User;
use app\models\Membership;

class AccessControlController extends Controller
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
                        'matchCallback' => function ($rule, $action) {
                            $rol = Yii::$app->user->identity->ROL;
                            return $rol === Roles::SUPER_ADMIN->value || $rol === Roles::ADMINISTRATOR->value;
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/access-control/index');
    }

    public function actionPollEvents(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $filePath = Yii::getAlias('@runtime/access_events.json');
        $lastId = Yii::$app->request->get('lastId', '0');

        if (!file_exists($filePath)) {
            return ['events' => [], 'deviceOnline' => false];
        }

        $content = file_get_contents($filePath);
        $events = json_decode($content, true) ?: [];

        $newEvents = [];
        foreach ($events as $event) {
            if ((string)$event['id'] > (string)$lastId) {
                $newEvents[] = $event;
            }
        }

        return [
            'events' => $newEvents,
            'deviceOnline' => $this->isDeviceOnline(),
        ];
    }

    public function actionSimulateAccess(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ci = Yii::$app->request->post('ci');
        if (empty($ci)) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'CI requerido'];
        }

        $accessService = new AccessVerificationService();
        $result = $accessService->verifyAccess($ci, (int)$ci);

        $this->pushEvent($result);

        return $this->formatAccessResponse($result);
    }

    public function actionManualSearch(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ci = Yii::$app->request->post('ci');
        if (empty($ci)) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'CI requerido'];
        }

        $accessService = new AccessVerificationService();
        $result = $accessService->verifyAccess($ci, 0);

        $this->pushEvent($result);

        return $this->formatAccessResponse($result);
    }

    public function actionVerifyAccess(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rawBody = Yii::$app->request->getRawBody();
        $data = json_decode($rawBody, true);

        if (!$data || !isset($data['usuario_id'])) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Missing usuario_id parameter'];
        }

        $ci = (string)$data['usuario_id'];
        $idBiometrico = isset($data['id_biometrico']) ? (int)$data['id_biometrico'] : (int)$ci;

        $accessService = new AccessVerificationService();
        $result = $accessService->verifyAccess($ci, $idBiometrico);

        $this->pushEvent($result);

        if ($result['status'] === 'error') {
            Yii::$app->response->statusCode = 500;
        }

        return $this->formatAccessResponse($result);
    }

    public function actionDebugMembership(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ci = Yii::$app->request->get('ci');
        if (empty($ci)) {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'CI requerido'];
        }

        $user = User::findOne(['CI' => $ci, 'ES_BORRADO' => 0]);
        if (!$user) {
            return ['user_found' => false, 'message' => 'Usuario no encontrado'];
        }

        $memberships = Membership::find()
            ->where(['CI_CLIENTE' => $ci])
            ->orderBy(['FECHA_INICIO' => SORT_DESC])
            ->all();

        $today = date('Y-m-d');
        $activeMembership = null;

        foreach ($memberships as $m) {
            $isActive = ($m->FECHA_INICIO <= $today && $m->FECHA_FIN >= $today && !$m->ES_BORRADO);
            if ($isActive && !$activeMembership) {
                $activeMembership = $m;
            }
        }

        return [
            'user_found' => true,
            'user' => [
                'ci' => $user->CI,
                'nombre' => $user->NOMBRE_COMPLETO,
                'rol' => $user->ROL,
            ],
            'today' => $today,
            'active_membership' => $activeMembership ? [
                'codigo' => $activeMembership->CODIGO_MEMBRESIA,
                'fecha_inicio' => $activeMembership->FECHA_INICIO,
                'fecha_fin' => $activeMembership->FECHA_FIN,
                'dias_disponibles' => $activeMembership->DIAS_DISPONIBLES,
                'es_borrado' => $activeMembership->ES_BORRADO,
            ] : null,
            'all_memberships' => array_map(function($m) use ($today) {
                return [
                    'codigo' => $m->CODIGO_MEMBRESIA,
                    'fecha_inicio' => $m->FECHA_INICIO,
                    'fecha_fin' => $m->FECHA_FIN,
                    'dias_disponibles' => $m->DIAS_DISPONIBLES,
                    'es_borrado' => $m->ES_BORRADO,
                    'is_active' => ($m->FECHA_INICIO <= $today && $m->FECHA_FIN >= $today && !$m->ES_BORRADO),
                ];
            }, $memberships),
        ];
    }

    private function formatAccessResponse(array $result): array
    {
        if ($result['status'] === 'granted') {
            return [
                'status' => 'granted',
                'reason' => $result['reason'] ?? 'Acceso permitido',
                'user' => [
                    'ci' => $result['user']->CI,
                    'nombre' => $result['user']->NOMBRE_COMPLETO,
                    'avatar' => $result['user']->AVATAR,
                ],
                'remainingDays' => $result['remainingDays'] ?? 0,
            ];
        }

        return [
            'status' => $result['status'],
            'reason' => $result['reason'] ?? 'Acceso denegado',
            'user' => isset($result['user']) && $result['user'] ? [
                'ci' => $result['user']->CI,
                'nombre' => $result['user']->NOMBRE_COMPLETO,
                'avatar' => $result['user']->AVATAR,
            ] : null,
            'remainingDays' => 0,
        ];
    }

    private function pushEvent(array $result): void
    {
        $filePath = Yii::getAlias('@runtime/access_events.json');
        $events = [];

        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $events = json_decode($content, true) ?: [];
        }

        $formattedData = $this->formatAccessResponse($result);

        $newEvent = [
            'id' => uniqid(),
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $formattedData,
        ];

        array_unshift($events, $newEvent);
        $events = array_slice($events, 0, 50);

        file_put_contents($filePath, json_encode($events, JSON_PRETTY_PRINT));
    }

    private function isDeviceOnline(): bool
    {
        $flaskUrl = Yii::$app->params['flask_api_url'] ?? 'http://localhost:5000';
        $ch = curl_init($flaskUrl . '/api/status');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}
