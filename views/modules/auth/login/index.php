<?php
use app\components\public\modules\AuthWidgetComponent\smart\LoginContainer;

$this->title = 'Login - Gym Universitario';
?>

<?= LoginContainer::widget(['model' => $model]) ?>