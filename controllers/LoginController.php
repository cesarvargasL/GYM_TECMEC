<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

class LoginController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
                'view' => '@app/views/modules/auth/error',
            ],
        ];
    }

    public function actionIndex(): Response
    {
        return $this->redirect(['login/login']);
    }

    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            if ($user->ROL === 'CLIENTE') {
                return $this->redirect(['dashboard/index']);
            }
            return $this->redirect(['access-control/index']);
        }

        $model = new \app\models\LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = Yii::$app->user->identity;
            if ($user->ROL === 'CLIENTE') {
                return $this->redirect(['dashboard/index']);
            }
            return $this->redirect(['access-control/index']);
        }

        $this->layout = 'modules/login/index';
        return $this->render('//modules/auth/login/index', [
            'model' => $model
        ]);
    }

    public function actionRegister(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['dashboard/index']);
        }

        $this->layout = 'modules/login/index';
        return $this->render('//modules/auth/register/index');
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->redirect(['login/login']);
    }
}