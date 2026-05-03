<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\shared\enums\ClientType;
use app\shared\enums\ClientStatus; 
?>
<div class="create-user-form-view" style="padding: 20px;">

    <div style="<?= $isPublicContext ? 'margin: 0 auto; background: white;' : 'background: white;' ?> padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); max-width: 600px;">
        
        <h2 style="margin-bottom: 30px; color: #333; text-align: <?= $isPublicContext ? 'center' : 'left' ?>;">
            <?= $isPublicContext ? 'Registro de Cliente' : 'Registrar Nuevo Usuario' ?>
        </h2>

        <?php $form = ActiveForm::begin([
            'id' => 'create-user-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['style' => 'font-weight:bold;'],
                'horizontalCssClasses' => ['input' => 'col-sm-8'],
            ],
        ]); ?>

        <?php if (!$isPublicContext): ?>
            <?= $form->field($model, 'ROL')->dropDownList($availableRoles, ['prompt' => 'Seleccione un Rol', 'id' => 'drop-rol']) ?>
        <?php endif; ?>

        <?= $form->field($model, 'CI')->textInput(['placeholder' => 'Carnet de Identidad', 'id' => 'input-ci']) ?>
        <?= $form->field($model, 'NOMBRE_COMPLETO')->textInput(['id' => 'input-nombre']) ?>
        <?= $form->field($model, 'CORREO_ELECTRONICO')->textInput(['placeholder' => 'email@gym.com']) ?>
        
        <?= $form->field($model, 'ESTADO')->hiddenInput(['value' => ClientStatus::ACTIVE->value, 'id' => 'input-estado'])->label(false) ?>

        <div id="section-tipo-cliente" style="<?= $isPublicContext ? 'display: block;' : 'display: none;' ?>">
            <?= $form->field($model, 'TIPO_CLIENTE')->dropDownList([
                ClientType::EXTERNAL->value => 'Externo',
                ClientType::UNIVERSITY_STUDENT->value => 'Universitario'
            ], ['prompt' => 'Seleccione Tipo', 'id' => 'drop-tipo-cliente']) ?>
        </div>

        <div id="section-hardware" style="display: none; border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
            <h4 style="margin-top: 0;">Sincronización de Hardware</h4>
            
            <?php if (!$isPublicContext): ?>
                <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 15px;">
                    <div style="width: 100px; height: 100px; background: #eee; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                        <span id="label-huella-status" style="font-size: 12px; color: #666;">Sin Huella</span>
                    </div>
                    <?= Html::button('Registrar Huella (Flask API)', ['class' => 'btn btn-secondary', 'id' => 'btn-hardware-huella', 'style' => 'border-radius: 20px;']) ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 15px; <?= !$isPublicContext ? 'border-top: 1px solid #eee; padding-top: 15px;' : '' ?>">
                <p style="margin-bottom: 5px; font-weight: bold;">Fotografía (Webcam)</p>
                <div style="display: flex; gap: 10px; align-items: end;">
                    <video id="webcam-video" width="200" height="150" autoplay style="border: 1px solid #ccc; background: #000; border-radius: 5px;"></video>
                    <img id="webcam-preview" style="display: none; width: 200px; height: 150px; border: 1px solid #ccc; border-radius: 5px; object-fit: cover;">
                    <canvas id="webcam-canvas" width="200" height="150" style="display: none;"></canvas>
                    
                    <?= Html::button('📸 Tomar Foto', ['class' => 'btn btn-outline-primary', 'id' => 'btn-hardware-foto', 'style' => 'border-radius: 20px;']) ?>
                </div>
                <input type="hidden" name="foto_webcam" id="input-foto-webcam" value="">
                <span id="label-foto-status" style="font-size: 12px; color: green; display: none; margin-top: 5px;">¡Foto capturada exitosamente!</span>
            </div>
        </div>

        <div class="form-group text-center">
            <?= Html::submitButton($isPublicContext ? 'COMPLETAR REGISTRO' : 'CREAR USUARIO', ['class' => 'btn btn-success', 'id' => 'btn-action-main', 'style' => 'width: 100%; font-weight: bold; border-radius: 20px;', 'disabled' => true]) ?>
        </div>

        <?php if ($isPublicContext): ?>
            <div style="margin-top: 15px; text-align: center;">
                <?= Html::a('Volver al Login', ['login/login'], ['style' => 'color: #0056b3; text-decoration: none; font-size: 14px;']) ?>
            </div>
        <?php endif; ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropRol = document.getElementById('drop-rol');
        const sectionTipoCliente = document.getElementById('section-tipo-cliente');
        const sectionHardware = document.getElementById('section-hardware');
        const dropTipoCliente = document.getElementById('drop-tipo-cliente');
        const btnActionMain = document.getElementById('btn-action-main');
        const inputCi = document.getElementById('input-ci');
        
        const video = document.getElementById('webcam-video');
        const canvas = document.getElementById('webcam-canvas');
        const btnFoto = document.getElementById('btn-hardware-foto');
        const inputFotoWebcam = document.getElementById('input-foto-webcam');
        const labelFotoStatus = document.getElementById('label-foto-status');

        const btnHuella = document.getElementById('btn-hardware-huella');
        const labelHuellaStatus = document.getElementById('label-huella-status');

        const isPublic = <?= $isPublicContext ? 'true' : 'false' ?>;

        if (isPublic) {
            sectionTipoCliente.style.display = 'block';
            sectionHardware.style.display = 'none';
        }
        
        function startCamera() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(function(stream) { video.srcObject = stream; })
                    .catch(function(err) { console.error("Error al acceder a la cámara: ", err); });
            }
        }

        if (dropRol) {
            dropRol.addEventListener('change', function() {
                const rol = this.value;
                if (rol === '<?= \app\shared\enums\Roles::ADMINISTRATOR->value ?>' || rol === '<?= \app\shared\enums\Roles::SUPER_ADMIN->value ?>') {
                    sectionTipoCliente.style.display = 'none';
                    sectionHardware.style.display = 'block'; 
                    startCamera(); 
                    btnActionMain.innerText = 'CREAR USUARIO';
                    btnActionMain.disabled = false;
                    btnActionMain.type = "submit";
                } else if (rol === '<?= \app\shared\enums\Roles::CLIENT->value ?>') {
                    sectionTipoCliente.style.display = 'block';
                    sectionHardware.style.display = 'none';
                    btnActionMain.disabled = true;
                } else {
                    sectionTipoCliente.style.display = 'none';
                    sectionHardware.style.display = 'none';
                    btnActionMain.disabled = true;
                }
            });
        }

        dropTipoCliente.addEventListener('change', function() {
            const tipo = this.value;
            if (tipo === '<?= \app\shared\enums\ClientType::EXTERNAL->value ?>') {
                sectionHardware.style.display = 'block';
                startCamera(); 
                btnActionMain.innerText = isPublic ? 'COMPLETAR REGISTRO EXTERNO' : 'CREAR USUARIO EXTERNO';
                
                btnActionMain.disabled = isPublic ? false : true; 
                btnActionMain.type = "submit";

            } else if (tipo === '<?= \app\shared\enums\ClientType::UNIVERSITY_STUDENT->value ?>') {
                sectionHardware.style.display = 'none';
                btnActionMain.innerText = 'CONSULTAR UNIVERSIDAD';
                btnActionMain.disabled = false;
                btnActionMain.type = "button";
            } else {
                sectionHardware.style.display = 'none';
                btnActionMain.disabled = true;
            }
        });

        btnFoto.addEventListener('click', function() {
            const preview = document.getElementById('webcam-preview');
            if (video.style.display === 'none') {
                video.style.display = 'block';
                preview.style.display = 'none';
                btnFoto.innerText = '📸 Tomar Foto';
                labelFotoStatus.style.display = 'none';
                inputFotoWebcam.value = '';
            } else {
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataUrl = canvas.toDataURL('image/jpeg');
                
                inputFotoWebcam.value = dataUrl;
                preview.src = dataUrl; 
                
                video.style.display = 'none';
                preview.style.display = 'block';
                
                btnFoto.innerText = '🔄 Retomar Foto';
                labelFotoStatus.style.display = 'block';
            }
        });

        if (btnHuella) {
            btnHuella.addEventListener('click', function() {
                const ci = inputCi.value;
                const nombre = document.getElementById('input-nombre').value;
                if(!ci || !nombre) { alert("Complete CI y Nombre primero."); return; }

                btnHuella.disabled = true;
                btnHuella.innerText = "Registrando...";
                
                console.log(`Llamando a Flask API para registrar huella de: ${nombre} (${ci})`);
                setTimeout(() => {
                    console.log("Huella registrada exitosamente (Simulación).");
                    labelHuellaStatus.innerText = "Huella OK";
                    labelHuellaStatus.style.color = "green";
                    btnHuella.innerText = "Registrar Huella (OK)";
                    btnActionMain.disabled = false; 
                }, 2000); 
            });
        }
    });
</script>