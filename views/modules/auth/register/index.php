<?php
use app\components\secure\modules\UserManagementWidgetComponent\CreateUser\smart\CreateUserContainer;

$this->title = 'Registro';
?>

<?= CreateUserContainer::widget(['isPublicContext' => true]) ?>