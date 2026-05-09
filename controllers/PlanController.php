<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\shared\enums\Roles;
use app\models\Plan;

class PlanController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->ROL === Roles::SUPER_ADMIN->value;
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex() {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/plan/index');
    }

    public function actionCreate() {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/plan/create');
    }

    public function actionUpdate($id) {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/plan/update', ['id' => $id]);
    }

    public function actionDelete($id) {
        $model = Plan::findOne($id);
        if ($model) {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Plan eliminado correctamente.');
        }
        return $this->redirect(['index']);
    }
}