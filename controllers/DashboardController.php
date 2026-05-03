<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class DashboardController extends Controller
{
    public const RESTRICTED_ROUTE = 'Ruta inaccesible. Primero debe iniciar sesión.';

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
                }
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'modules/dashboard/index'; 
        return $this->render('//modules/dashboard/index');
    }
}