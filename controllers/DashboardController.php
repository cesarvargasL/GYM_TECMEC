<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\shared\enums\Roles;

class DashboardController extends Controller
{
    public const RESTRICTED_ROUTE = 'Ruta inaccesible. Primero debe iniciar sesion.';

    public function behaviors()
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
                'denyCallback' => function ($rule, $action) {
                    Yii::$app->session->setFlash('error', self::RESTRICTED_ROUTE);
                    return Yii::$app->response->redirect(['login/login']);
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'modules/dashboard/index';

        $user = Yii::$app->user->identity;
        $activeMembership = null;
        $attendanceDates = [];

        if ($user->isClient()) {
            $activeMembership = $user->getActiveMembership()->one();
            if ($activeMembership) {
                $membershipService = new \app\components\services\MembershipService();
                $attendanceDates = $membershipService->getAllAttendanceDatesForClient(
                    $user->CI,
                    (int)$activeMembership->CODIGO_MEMBRESIA
                );
            }
        }

        return $this->render('//modules/dashboard/index', [
            'activeMembership' => $activeMembership,
            'attendanceDates' => $attendanceDates,
        ]);
    }
}
