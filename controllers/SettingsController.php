<?php
declare(strict_types=1);

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;

class SettingsController extends Controller
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
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/settings/index');
    }
}