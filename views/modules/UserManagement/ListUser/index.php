<?php
use app\components\secure\modules\UserManagementWidgetComponent\ListUser\smart\ListUserContainer;

$this->title = 'Lista de Usuarios';
?>

<?= ListUserContainer::widget() ?>