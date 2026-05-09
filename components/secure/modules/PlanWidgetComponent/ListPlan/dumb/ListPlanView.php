<?php
use yii\helpers\Html;
use app\shared\enums\ClientStatus;

/* @var $planes app\models\Plan[] */
/* @var $isSuperAdmin bool */
?>

<div style="padding: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="color: #333; margin: 0;">Catálogo de Planes (Membresías)</h2>
        
        <?php if ($isSuperAdmin): ?>
            <?= Html::a('+ Agregar Nuevo Plan', ['plan/create'], ['class' => 'btn btn-success', 'style' => 'font-weight: bold; border-radius: 20px;']) ?>
        <?php endif; ?>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: flex-start;">
        
        <?php foreach ($planes as $plan): ?>
            <?php 
                $isActive = $plan->ESTADO === ClientStatus::ACTIVE->value;
                $planTitle = strtoupper($plan->NOMBRE_PLAN);
            ?>

            <div style="
                background-color: <?= $isActive ? '#b3e0ff' : '#d6d8db' ?>; 
                border: 2px solid <?= $isActive ? '#0056b3' : '#6c757d' ?>; 
                border-radius: 15px; 
                padding: 20px; 
                width: 300px; 
                text-align: center;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                opacity: <?= $isActive ? '1' : '0.7' ?>;
                position: relative;
            ">
                <h4 style="color: <?= $isActive ? '#004085' : '#495057' ?>; font-weight: bold; margin-top: 0;">
                    <?= Html::encode($planTitle) ?>
                </h4>
                
                <div style="margin: 20px 0; font-size: 18px; color: #333;">
                    <strong>Modalidad:</strong> <?= Html::encode(str_replace('_', ' ', $plan->TIPO_PLAN)) ?><br>
                    <strong>Precio:</strong> <span style="font-size: 22px; font-weight: bold; color: <?= $isActive ? '#28a745' : '#6c757d' ?>;"><?= Html::encode($plan->MONTO) ?> Bs.</span>
                </div>

                <div style="font-size: 12px; color: #666; margin-bottom: 15px;">
                    <?php if ($plan->FECHA_VIGENCIA === null): ?>
                        🟢 Plan sin fecha de vencimiento (De por vida)
                    <?php elseif (!$isActive): ?>
                        🔴 Venció el: <?= Html::encode($plan->FECHA_VIGENCIA) ?> (Requiere Renovación)
                    <?php else: ?>
                        Válido hasta: <?= Html::encode($plan->FECHA_VIGENCIA) ?>
                    <?php endif; ?>
                </div>

                <?php if ($isSuperAdmin): ?>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 15px;">
                        <?= Html::a('Editar', ['plan/update', 'id' => $plan->ID_PLAN], [
                            'class' => 'btn ' . ($isActive ? 'btn-outline-primary' : 'btn-outline-secondary'), 
                            'style' => 'border-radius: 20px; width: 100px; font-weight: bold;'
                        ]) ?>
                        
                        <?= Html::a('Eliminar', ['plan/delete', 'id' => $plan->ID_PLAN], [
                            'class' => 'btn ' . ($isActive ? 'btn-outline-danger' : 'btn-outline-dark'), 
                            'style' => 'border-radius: 20px; width: 100px; font-weight: bold;',
                            'data' => [
                                'confirm' => '¿Estás seguro de que deseas eliminar este plan?',
                                'method' => 'post', 
                            ]
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endforeach; ?>

        <?php if (empty($planes)): ?>
            <div style="width: 100%; text-align: center; padding: 50px; color: #666;">
                <h3>No hay planes registrados en el sistema.</h3>
            </div>
        <?php endif; ?>

    </div>
</div>