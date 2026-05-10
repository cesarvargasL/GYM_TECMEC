<?php
use app\components\secure\modules\UserManagementWidgetComponent\UpdateUser\smart\UpdateUserContainer;

$this->title = 'Editar Usuario';
?>
<div class="user-management-update">
    <?= UpdateUserContainer::widget(['userId' => $id]) ?>
</div>