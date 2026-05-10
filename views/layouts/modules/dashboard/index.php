<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\shared\ToasterWidgetComponent\smart\ToasterContainer;
use app\shared\AppConst;
use app\shared\enums\Roles;
use yii\web\YiiAsset;

YiiAsset::register($this);

$currentRoute = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
$currentUserRole = Yii::$app->user->identity->ROL ?? null;

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            margin: 0;
            font-family: sans-serif;
            display: flex;
            height: 100vh;
            background: #f4f6f9;
        }

        .sidebar {
            width: 250px;
            background: #fff;
            border-right: 2px solid #333;
            display: flex;
            flex-direction: column;
            border-radius: 0 20px 20px 0;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar-header img {
            width: 40px;
        }

        .nav-links {
            list-style: none;
            padding: 20px 0;
            margin: 0;
            flex-grow: 1;
        }

        .nav-links li a {
            display: block;
            padding: 15px 30px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            border-radius: 0 25px 25px 0;
            margin-right: 20px;
        }

        .nav-links li a:hover,
        .nav-links li a.active {
            background: #eef2f5;
            color: #0056b3;
            border: 1px solid #0056b3;
            border-left: none;
        }

        .user-profile {
            padding: 20px;
            border-top: 2px solid #333;
            text-align: center;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: #fff;
            padding: 20px 30px;
            border-bottom: 2px solid #333;
            font-size: 20px;
            font-weight: bold;
        }

        .content-body {
            padding: 30px;
            overflow-y: auto;

            .pagination {
                display: flex;
                padding-left: 0;
                list-style: none;
                justify-content: flex-end;
                gap: 5px;
                margin-top: 20px;
            }

            .page-item .page-link {
                display: block;
                padding: 8px 14px;
                color: #0056b3;
                background-color: #fff;
                border: 1px solid #dee2e6;
                border-radius: 5px;
                text-decoration: none;
                font-weight: bold;
                transition: all 0.2s;
            }

            .page-item .page-link:hover {
                background-color: #eef2f5;
                border-color: #0056b3;
            }

            .page-item.active .page-link {
                color: #fff;
                background-color: #0056b3;
                border-color: #0056b3;
            }

            .page-item.disabled .page-link {
                color: #6c757d;
                pointer-events: none;
                background-color: #f8f9fa;
                border-color: #dee2e6;
            }
        }
    </style>
</head>

<body>
    <?php $this->beginBody() ?>

    <aside class="sidebar">
        <div class="sidebar-header">
            <div style="width: 30px; height: 30px; background: #ccc; border-radius: 50%;"></div>
            Gymnasio<br>Universitario
        </div>

        <ul class="nav-links">
            <?php if ($currentUserRole === Roles::ADMINISTRATOR->value || $currentUserRole === Roles::SUPER_ADMIN->value): ?>
                <li><a href="<?= Url::to(['user-management/create']) ?>" class="<?= $currentRoute === 'user-management/create' ? 'active' : AppConst::EMPTY ?>">Crear Usuario</a></li>
                <li><a href="<?= Url::to(['user-management/index']) ?>" class="<?= $currentRoute === 'user-management/index' ? 'active' : AppConst::EMPTY ?>">Usuarios</a></li>
            <?php endif; ?>
            <li><a href="#">Control de Entrada</a></li>
            <li><a href="<?= Url::to(['dashboard/index']) ?>" class="<?= $currentRoute === 'dashboard/index' ? 'active' : AppConst::EMPTY ?>">Registro</a></li>
            <li><a href="<?= Url::to(['plan/index']) ?>" class="<?= $currentRoute === 'plan/index' ? 'active' : AppConst::EMPTY ?>">Planes</a></li>
            <li><a href="#">Historial</a></li>
            <li><a href="<?= Url::to(['settings/index']) ?>" class="<?= $currentRoute === 'settings/index' ? 'active' : AppConst::EMPTY ?>">Configuraciones</a></li>
        </ul>

        <div class="user-profile">
            <?php if (Yii::$app->user->identity->AVATAR): ?>
                <img src="<?= Html::encode(Yii::$app->user->identity->AVATAR) ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #0056b3;">
            <?php else: ?>
                <div style="width: 40px; height: 40px; background: #0056b3; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                    <?= strtoupper(substr(Yii::$app->user->identity->NOMBRE_COMPLETO ?? 'A', 0, 1)) ?>
                </div>
            <?php endif; ?>

            <div style="text-align: left; line-height: 1.2;">
                <strong><?= Yii::$app->user->identity->NOMBRE_COMPLETO ?? 'Admin' ?></strong><br>
                <small style="color: #666;"><?= Yii::$app->user->identity->ROL ?? 'Rol' ?></small>
                <br>
                <?= Html::a('Logout', ['login/logout'], [
                    'data' => [
                        'method' => 'post',
                    ],
                    'style' => 'color: red; font-size: 11px; text-decoration: none;'
                ]) ?>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <?= Html::encode($this->title ?: 'Panel de Control') ?>
        </header>
        <section class="content-body">
            <?= $content ?>
            <?= ToasterContainer::widget() ?>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof yii !== 'undefined') {
                yii.confirm = function(message, okCallback, cancelCallback) {
                    Swal.fire({
                        title: 'Confirmación de Seguridad',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, estoy seguro',
                        cancelButtonText: 'No, cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            okCallback();
                        } else if (cancelCallback) {
                            cancelCallback();
                        }
                    });
                };
            }
        });
    </script>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>