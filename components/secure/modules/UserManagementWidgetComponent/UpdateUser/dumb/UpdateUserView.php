<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\shared\enums\Roles;
use app\shared\enums\ClientType;

/* @var $model app\models\AdminUpdateUserForm */
/* @var $user app\models\User */
?>

<div style="padding: 20px; display: flex; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 100%; max-width: 600px;">
        
        <h2 style="margin-bottom: 30px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            Editar Usuario: <?= Html::encode($user->CI) ?>
        </h2>

        <div style="text-align: center; margin-bottom: 20px;">
            <?php if ($user->AVATAR): ?>
                <img id="avatar-preview" src="<?= Html::encode($user->AVATAR) ?>" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc;">
            <?php else: ?>
                <div id="avatar-preview" style="width: 100px; height: 100px; background: #0056b3; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; margin: 0 auto; border: 3px solid #ccc;">
                    <?= strtoupper(substr($user->NOMBRE_COMPLETO, 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'update-user-form',
            'options' => ['enctype' => 'multipart/form-data'],
            'enableClientValidation' => false
        ]); ?>

        <?= $form->field($model, 'avatar')->fileInput(['accept' => 'image/*', 'id' => 'avatar-input'])->label('Actualizar Foto de Perfil') ?>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'name')->textInput()->label('Nombre Completo') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'email')->textInput()->label('Correo Electrónico') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'phone')->textInput()->label('Teléfono') ?>
            <div class="col-md-12">
                <?= $form->field($model, 'newPassword')->passwordInput(['placeholder' => 'Dejar en blanco para no cambiar'])->label('Resetear Contraseña') ?>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 30px;">
            <?= Html::a('← Cancelar', ['user-management/index'], ['class' => 'btn btn-light', 'style' => 'border-radius: 20px; font-weight: bold;']) ?>
            <?= Html::submitButton('💾 Guardar Cambios', ['class' => 'btn btn-primary', 'style' => 'border-radius: 20px; font-weight: bold;']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');

        if (avatarInput) {
            avatarInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if(avatarPreview.tagName.toLowerCase() === 'div') {
                            const img = document.createElement('img');
                            img.id = 'avatar-preview';
                            img.src = e.target.result;
                            img.style.cssText = "width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #28a745; margin: 0 auto; display: block;";
                            avatarPreview.parentNode.replaceChild(img, avatarPreview);
                        } else {
                            avatarPreview.src = e.target.result;
                            avatarPreview.style.border = '3px solid #28a745'; 
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>