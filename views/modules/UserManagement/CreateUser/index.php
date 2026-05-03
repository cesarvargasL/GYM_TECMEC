<?php
use app\components\secure\modules\UserManagementWidgetComponent\CreateUser\smart\CreateUserContainer;

$this->title = 'Crear Usuario';
?>
<div class="user-management-create">
    <?= CreateUserContainer::widget() ?>
</div>