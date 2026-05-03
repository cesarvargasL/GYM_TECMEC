<?php
use app\components\secure\modules\UserManagementWidgetComponent\CreateUser\smart\CreateUserContainer;

$this->title = 'Registro - Gym Universitario';
?>

<?= CreateUserContainer::widget(['isPublicContext' => true]) ?>