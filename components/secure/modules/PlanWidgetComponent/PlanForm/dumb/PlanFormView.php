<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\shared\enums\PlanType;
use app\shared\enums\ClientType;

/* @var $model app\models\Plan */

$isNewRecord = $model->isNewRecord;
$isLifetime = !$isNewRecord && $model->FECHA_VIGENCIA === null;
?>
<div style="padding: 20px;">
    <div style="background: white; padding: 40px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 30px; color: #333; text-align: center;">
            <?= $isNewRecord ? 'Agregar Nuevo Plan' : 'Editar Plan' ?>
        </h2>

        <?php $form = ActiveForm::begin(['id' => 'plan-form']); ?>

        <?= $form->field($model, 'NOMBRE_PLAN')->textInput(['placeholder' => 'Ej: Plan Tecnologías de la Salud', 'maxlength' => true]) ?>

        <?= $form->field($model, 'TIPO_PLAN')->dropDownList([
            PlanType::MONTHLY->value => 'Mensual',
            PlanType::HALF_MONTH->value => 'Medio Mes',
            PlanType::SESSION->value => 'Sesión',
        ], ['prompt' => 'Seleccione el Tipo de Plan']) ?>

        <?= $form->field($model, 'TIPO_CLIENTE')->dropDownList([
            ClientType::EXTERNAL->value => 'Externo',
            ClientType::UNIVERSITY_STUDENT->value => 'Universitario'
        ], ['prompt' => 'Seleccione a quién aplica']) ?>

        <?= $form->field($model, 'MONTO')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => 'Ej: 100.00']) ?>

        <div class="form-group" style="margin-bottom: 15px;">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="check-lifetime" name="is_lifetime" value="1" <?= $isLifetime ? 'checked' : '' ?>>
                <label class="form-check-label" for="check-lifetime">Plan sin fecha de vencimiento (De por vida)</label>
            </div>
        </div>

        <div id="date-container" style="<?= $isLifetime ? 'display: none;' : 'display: block;' ?>">
            <?= $form->field($model, 'FECHA_VIGENCIA')->textInput(['type' => 'date']) ?>
        </div>

        <div class="form-group text-center" style="margin-top: 30px;">
            <?= Html::submitButton($isNewRecord ? 'GUARDAR PLAN' : 'ACTUALIZAR PLAN', ['class' => 'btn btn-success', 'style' => 'width: 100%; font-weight: bold; border-radius: 20px;']) ?>
        </div>
        
        <div style="text-align: center; margin-top: 15px;">
            <?= Html::a('Cancelar', ['plan/index'], ['style' => 'color: #6c757d; text-decoration: none;']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkLifetime = document.getElementById('check-lifetime');
        const dateContainer = document.getElementById('date-container');
        const dateInput = document.getElementById('plan-fecha_vigencia');

        checkLifetime.addEventListener('change', function() {
            if (this.checked) {
                dateContainer.style.display = 'none';
                dateInput.value = '';
            } else {
                dateContainer.style.display = 'block';
            }
        });
    });
</script>