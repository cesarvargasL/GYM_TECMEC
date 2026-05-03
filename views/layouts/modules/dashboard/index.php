<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\shared\ToasterWidgetComponent\smart\ToasterContainer;
use app\shared\AppConst;
use app\shared\enums\Roles;

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
    
    <style>
        body { margin: 0; font-family: sans-serif; display: flex; height: 100vh; background: #f4f6f9; }
        .sidebar { width: 250px; background: #fff; border-right: 2px solid #333; display: flex; flex-direction: column; border-radius: 0 20px 20px 0;}
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid #ddd; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 10px;}
        .sidebar-header img { width: 40px; }
        .nav-links { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .nav-links li a { display: block; padding: 15px 30px; color: #333; text-decoration: none; font-weight: 500; border-radius: 0 25px 25px 0; margin-right: 20px;}
        .nav-links li a:hover, .nav-links li a.active { background: #eef2f5; color: #0056b3; border: 1px solid #0056b3; border-left: none; }
        .user-profile { padding: 20px; border-top: 2px solid #333; text-align: center; display: flex; align-items: center; gap: 10px; justify-content: center;}
        .main-content { flex-grow: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: 20px 30px; border-bottom: 2px solid #333; font-size: 20px; font-weight: bold;}
        .content-body { padding: 30px; overflow-y: auto; }
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
            <?php endif; ?>
            <li><a href="#">Control de Entrada</a></li>
            <li><a href="<?= Url::to(['dashboard/index']) ?>" class="<?= $currentRoute === 'dashboard/index' ? 'active' : AppConst::EMPTY ?>">Registro</a></li>
            <li><a href="#">Membresía</a></li>
            <li><a href="#">Usuarios</a></li>
            <li><a href="#">Historial</a></li>
            <li><a href="#">Configuraciones</a></li>
        </ul>
        
        <div class="user-profile">
            <div style="width: 40px; height: 40px; background: #0056b3; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center;">
                <?= strtoupper(substr(Yii::$app->user->identity->NOMBRE_COMPLETO ?? 'A', 0, 1)) ?>
            </div>
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
            Registro
        </header>
        <section class="content-body">
            
            <?= $content ?> 

        </section>
    </main>
    <?= ToasterContainer::widget() ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>