<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\shared\enums\Roles;
use yii\web\ErrorAction;

class UserManagementController extends Controller
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

    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
                'view' => '@app/views/modules/auth/error',
            ],
        ];
    }

    public function actionCreate()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/UserManagement/CreateUser/index');
    }

    public function actionIndex()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/UserManagement/ListUser/index');
    }

    public function actionSoftDelete($id)
    {
        $user = \app\models\User::findOne(['CI' => $id, 'ES_BORRADO' => 0]);
        if ($user) {
            $user->ES_BORRADO = 1;
            $user->save(false);
            Yii::$app->session->setFlash('success', 'Usuario eliminado correctamente.');
        }
        return $this->redirect(['index']);
    }

    public function actionHardDelete($id)
    {
        $user = \app\models\User::findOne(['CI' => $id]);
        if ($user) {
            $user->delete();
            Yii::$app->session->setFlash('success', 'Usuario purgado de la base de datos.');
        }
        return $this->redirect(['index']);
    }

    public function actionUpdate($id)
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/UserManagement/UpdateUser/index', [
            'id' => $id,
        ]);
    }
}
