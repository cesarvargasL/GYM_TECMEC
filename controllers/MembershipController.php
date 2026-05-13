<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\shared\enums\Roles;

class MembershipController extends Controller
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
        return $this->render('//modules/membership/index');
    }

    public function actionCreate()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/membership/create');
    }
}
