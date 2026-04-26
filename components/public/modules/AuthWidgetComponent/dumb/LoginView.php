<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; 
?>

<div style="display: flex; justify-content: center; align-items: center; height: 100vh; width: 100%;">
    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center; width: 100%; max-width: 400px;">
        
        <h2 style="margin-bottom: 30px; color: #333;">GYM Universitario</h2>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{input}\n{error}",
            ],
        ]); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Username', 'style' => 'margin-bottom: 15px; border-radius: 5px;']) ?>

        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password', 'style' => 'margin-bottom: 25px; border-radius: 5px;']) ?>

        <div class="form-group">
            <?= Html::submitButton('INICIA SESIÓN', ['class' => 'btn btn-success', 'name' => 'login-button', 'style' => 'width: 100%; font-weight: bold; border-radius: 20px;']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php foreach ($flashes as $type => $message): ?>
    <?php 
        $bgColor = $type === 'error' ? '#dc3545' : '#28a745'; // Rojo o Verde Bootstrap
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '<?= $bgColor ?>',
                color: '#ffffff',
                iconColor: '#ffffff'
            });
            
            Toast.fire({
                icon: '<?= $type === "error" ? "error" : "success" ?>',
                title: '<?= $message ?>'
            });
        });
    </script>
<?php endforeach; ?>