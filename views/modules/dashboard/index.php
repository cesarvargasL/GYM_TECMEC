<?php
use yii\helpers\Html;
use app\components\secure\modules\DailyAccessLogWidgetComponent\smart\DailyAccessLogContainer;


$this->title = 'Admin Access Control';
?>

<div class="dashboard-index" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    
    <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
        <?= Html::encode($this->title) ?>
    </p>

    <?= DailyAccessLogContainer::widget() ?>

</div>