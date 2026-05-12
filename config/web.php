<?php
use app\shared\enums\Roles;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'login/login',
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'Dcghxxsm0QW4I7ocLpclAh61SVk7nUf5',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['login/login'],
            'on afterLogin' => function ($event) {
                $user = $event->identity;
                if ($user->ROL === Roles::ADMINISTRATOR || $user->ROL === Roles::SUPER_ADMIN) {
                    $logService = new \app\components\services\AdminAccessLogService();
                    $logService->registerAccess($user);
                }
            },
        ],
        'errorHandler' => [
            'errorAction' => 'login/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'login' => 'login/login',
                'register' => 'login/register',
                'dashboard' => 'dashboard/index',
                'api/enroll-callback' => 'api/enroll-callback',
                'api/verify-access' => 'api/verify-access',
                'api/get-users' => 'api/get-users',
                'access-control/poll-events' => 'access-control/poll-events',
                'access-control/simulate-access' => 'access-control/simulate-access',
                'access-control/manual-search' => 'access-control/manual-search',
                'access-control/debug-membership' => 'access-control/debug-membership',
                'access-control' => 'access-control/index',
                'history' => 'history/index',
                'payment' => 'payment/index',
                'membership' => 'membership/index',
            ],
        ],
    ],
    'params' => $params,
];
return $config;
