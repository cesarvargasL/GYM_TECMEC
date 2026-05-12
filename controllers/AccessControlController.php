<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;
use app\shared\enums\Roles;
use app\components\services\AccessVerificationService;

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

    public function actionStream(): Response
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        $response = Yii::$app->response;
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        $filePath = Yii::getAlias('@runtime/access_events.json');

        $lastEventId = Yii::$app->request->get('lastEventId', '0');

        while (true) {
            if (connection_aborted()) {
                break;
            }

            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $events = json_decode($content, true) ?: [];

                foreach ($events as $event) {
                    if ((string)$event['id'] > (string)$lastEventId) {
                        echo "id: {$event['id']}\n";
                        echo "data: " . json_encode($event['data']) . "\n\n";
                        ob_flush();
                        flush();
                        $lastEventId = (string)$event['id'];
                    }
                }
            }

            sleep(2);
        }

        return $response;
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
        $result = $accessService->verifyAccessManual($ci);

        if ($result['status'] === 'granted') {
            return [
                'status' => 'granted',
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
            'reason' => $result['reason'],
            'user' => $result['user'] ? [
                'ci' => $result['user']->CI,
                'nombre' => $result['user']->NOMBRE_COMPLETO,
                'avatar' => $result['user']->AVATAR,
            ] : null,
        ];
    }
}
