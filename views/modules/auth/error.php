<?php
use yii\helpers\Html;

$this->title = $name;
?>
<div style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f6f9;">
    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center; max-width: 500px;">
        <h1 style="color: #dc3545; font-size: 24px; margin-bottom: 20px;">
            <?= Html::encode($this->title) ?>
        </h1>
        
        <div style="font-size: 16px; color: #333; margin-bottom: 30px;">
            <?= nl2br(Html::encode($message)) ?>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            The above error occurred while the Web server was processing your request. <br>
            Please contact us if you think this is a server error. Thank you.
        </p>

        <div style="margin-top: 20px;">
            <?= Html::a('Return to Home', ['dashboard/index'], ['class' => 'btn btn-primary', 'style' => 'text-decoration: none; padding: 10px 20px; background-color: #0056b3; color: white; border-radius: 20px;']) ?>
        </div>
    </div>
</div>