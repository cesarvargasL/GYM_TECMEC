<?php
use app\components\secure\modules\PlanWidgetComponent\PlanForm\smart\PlanFormContainer;
$this->title = 'Editar Plan';
?>
<?= PlanFormContainer::widget(['id' => $id]) ?>