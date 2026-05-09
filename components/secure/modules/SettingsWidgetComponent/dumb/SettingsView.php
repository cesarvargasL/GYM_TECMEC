<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $user app\models\User */
/* @var $profileForm app\models\UpdateProfileForm */
/* @var $passwordForm app\models\ChangePasswordForm */
/* @var $activeTab string */
?>

<div style="padding: 20px; display: flex; justify-content: center; align-items: flex-start; height: 100%;">
    
    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 100%; max-width: 500px; text-align: center; position: relative;">
        
        <h2 style="margin-bottom: 30px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">Configuración</h2>

        <div id="view-main" style="display: <?= $activeTab === 'view-main' ? 'block' : 'none' ?>;">
            <?php if ($user->AVATAR): ?>
                <img src="<?= Html::encode($user->AVATAR) ?>" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px auto; display: block; border: 3px solid #0056b3;">
            <?php else: ?>
                <div style="width: 100px; height: 100px; background: #0056b3; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; margin: 0 auto 15px auto;">
                    <?= strtoupper(substr($user->NOMBRE_COMPLETO, 0, 1)) ?>
                </div>
            <?php endif; ?>
            
            <h3 style="margin: 0; color: #333;"><?= Html::encode($user->NOMBRE_COMPLETO) ?></h3>
            <p style="color: #666; margin-bottom: 5px;"><?= Html::encode($user->CORREO_ELECTRONICO) ?></p>
            <p style="color: #666; margin-bottom: 30px;">Tel: <?= Html::encode($user->TELEFONO ?? 'No registrado') ?></p>

            <div style="display: flex; flex-direction: column; gap: 15px; align-items: center;">
                <button type="button" class="btn btn-outline-success" onclick="toggleView('view-password')" style="border-radius: 20px; width: 80%; font-weight: bold;">Cambiar Contraseña</button>
                <button type="button" class="btn btn-outline-primary" onclick="toggleView('view-profile')" style="border-radius: 20px; width: 80%; font-weight: bold;">Editar Perfil y Foto</button>
            </div>
        </div>

        <div id="view-password" style="display: <?= $activeTab === 'view-password' ? 'block' : 'none' ?>; text-align: left;">
            <h4 style="text-align: center; margin-bottom: 20px; color: #0056b3;">Cambiar Contraseña</h4>
            
            <?php $formPass = ActiveForm::begin([
                'id' => 'password-form',
                'enableClientValidation' => false
            ]); ?>
            
            <?= $formPass->field($passwordForm, 'oldPassword')->passwordInput(['placeholder' => 'Contraseña Antigua'])->label('Contraseña Antigua') ?>
            <?= $formPass->field($passwordForm, 'newPassword')->passwordInput(['placeholder' => 'Nueva Contraseña'])->label('Nueva Contraseña') ?>
            <?= $formPass->field($passwordForm, 'confirmPassword')->passwordInput(['placeholder' => 'Confirmar Contraseña'])->label('Confirmar Contraseña') ?>
            
            <div style="display: flex; justify-content: space-between; margin-top: 25px;">
                <button type="button" class="btn btn-light" onclick="toggleView('view-main')" style="border-radius: 20px;">← Volver</button>
                <?= Html::submitButton('Guardar Contraseña', ['class' => 'btn btn-success', 'style' => 'border-radius: 20px; font-weight: bold;']) ?>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>

        <div id="view-profile" style="display: <?= $activeTab === 'view-profile' ? 'block' : 'none' ?>; text-align: left;">
            <h4 style="text-align: center; margin-bottom: 20px; color: #0056b3;">Editar Perfil</h4>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <?php if ($user->AVATAR): ?>
                    <img id="avatar-preview" src="<?= Html::encode($user->AVATAR) ?>" alt="Preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc;">
                <?php else: ?>
                    <img id="avatar-preview" src="https://via.placeholder.com/100?text=Foto" alt="Preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc;">
                <?php endif; ?>
            </div>

            <?php $formProfile = ActiveForm::begin([
                'id' => 'profile-form',
                'options' => ['enctype' => 'multipart/form-data'],
                'enableClientValidation' => false 
            ]); ?>
            
            <?= $formProfile->field($profileForm, 'avatar')->fileInput(['accept' => 'image/*', 'id' => 'avatar-input'])->label('Elegir nueva foto') ?>
            
            <?= $formProfile->field($profileForm, 'email')->textInput(['placeholder' => 'correo@ejemplo.com'])->label('Correo Electrónico') ?>
            <?= $formProfile->field($profileForm, 'phone')->textInput(['placeholder' => 'Ej: 71234567'])->label('Nuevo Número') ?>
            
            <div style="display: flex; justify-content: space-between; margin-top: 25px;">
                <button type="button" class="btn btn-light" onclick="toggleView('view-main')" style="border-radius: 20px;">← Volver</button>
                <?= Html::submitButton('Guardar Cambios', ['class' => 'btn btn-primary', 'style' => 'border-radius: 20px; font-weight: bold;']) ?>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>

<script>
    function toggleView(viewId) {
        document.getElementById('view-main').style.display = 'none';
        document.getElementById('view-password').style.display = 'none';
        document.getElementById('view-profile').style.display = 'none';
        
        document.getElementById(viewId).style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');

        if (avatarInput) {
            avatarInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        avatarPreview.style.border = '3px solid #28a745'; 
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>