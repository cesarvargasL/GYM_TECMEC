<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\shared\enums\Roles;

$this->title = 'GYM - Tecmec';

$user = Yii::$app->user->identity;
$isClient = $user->isClient();
?>

<div class="dashboard-index" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

    <?php if ($isClient): ?>
        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
            Bienvenido, <?= Html::encode($user->NOMBRE_COMPLETO) ?>
        </p>

        <div class="client-tabs">
            <button class="client-tab active" data-tab="tab-days">Dias Restantes</button>
            <button class="client-tab" data-tab="tab-pay">Pagar</button>
            <button class="client-tab" data-tab="tab-plans">Membresias</button>
        </div>

        <div id="tab-days" class="client-tab-content active">
            <?php if ($activeMembership): ?>
                <div style="padding: 20px; background: #f8f9fa; border-radius: 10px; margin-bottom: 20px;">
                    <h3 style="color: #0056b3; margin-bottom: 10px;">Membresia Activa</h3>
                    <p><strong>Plan:</strong> <?= $activeMembership->plan ? Html::encode($activeMembership->plan->NOMBRE_PLAN) : '-' ?></p>
                    <p><strong>Desde:</strong> <?= date('d/m/Y', strtotime($activeMembership->FECHA_INICIO)) ?></p>
                    <p><strong>Hasta:</strong> <?= date('d/m/Y', strtotime($activeMembership->FECHA_FIN)) ?></p>
                    <p><strong>Dias disponibles:</strong> <span style="color: <?= $activeMembership->DIAS_DISPONIBLES > 5 ? '#28a745' : '#dc3545' ?>; font-weight: bold;"><?= $activeMembership->DIAS_DISPONIBLES ?></span></p>
                </div>

                <h4 style="color: #333; margin-bottom: 10px;">Calendario de Asistencias</h4>
                <div class="calendar-grid" id="attendance-calendar">
                    <div class="calendar-day calendar-day-header">Lun</div>
                    <div class="calendar-day calendar-day-header">Mar</div>
                    <div class="calendar-day calendar-day-header">Mie</div>
                    <div class="calendar-day calendar-day-header">Jue</div>
                    <div class="calendar-day calendar-day-header">Vie</div>
                    <div class="calendar-day calendar-day-header">Sab</div>
                    <div class="calendar-day calendar-day-header">Dom</div>
                    <?php
                    $startDate = new DateTime($activeMembership->FECHA_INICIO);
                    $endDate = new DateTime($activeMembership->FECHA_FIN);
                    $today = new DateTime();
                    $attendanceDates = $attendanceDates ?? [];

                    $current = clone $startDate;
                    $firstDayOfWeek = (int)$current->format('N') - 1;
                    for ($i = 0; $i < $firstDayOfWeek; $i++) {
                        echo '<div class="calendar-day" style="opacity: 0.3;"></div>';
                    }

                    while ($current <= $endDate) {
                        $dateStr = $current->format('Y-m-d');
                        $isAttended = in_array($dateStr, $attendanceDates);
                        $isToday = $current->format('Y-m-d') === $today->format('Y-m-d');

                        $classes = 'calendar-day';
                        if ($isAttended) $classes .= ' attended';
                        if ($isToday) $classes .= ' today';

                        echo '<div class="' . $classes . '" title="' . $dateStr . '">' . $current->format('j') . ($isAttended ? ' ✓' : '') . '</div>';

                        $current->modify('+1 day');
                    }
                    ?>
                </div>
            <?php else: ?>
                <div style="padding: 30px; text-align: center; background: #f8f9fa; border-radius: 10px;">
                    <h3 style="color: #666;">No tienes una membresia activa</h3>
                    <p>Selecciona la pestana "Pagar" para adquirir un plan.</p>
                </div>
            <?php endif; ?>
        </div>

        <div id="tab-pay" class="client-tab-content">
            <div style="padding: 20px;">
                <h3 style="color: #0056b3; margin-bottom: 20px;">Renovar Membresia</h3>
                <p style="color: #666; margin-bottom: 20px;">Selecciona un plan para renovar tu membresia con pago QR.</p>

                <div id="client-plans-list">
                    <?php
                    $plans = \app\models\Plan::find()->where(['ESTADO' => 'ACTIVO'])->all();
                    foreach ($plans as $plan):
                    ?>
                        <div style="padding: 15px; border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong><?= Html::encode($plan->NOMBRE_PLAN) ?></strong><br>
                                <small style="color: #666;"><?= Html::encode($plan->getTipoPlanLabel()) ?> - <?= Html::encode($plan->TIPO_CLIENTE) ?></small>
                            </div>
                            <div style="text-align: right;">
                                <strong style="color: #28a745; font-size: 18px;">Bs. <?= number_format($plan->MONTO, 2) ?></strong><br>
                                <button class="btn btn-success btn-sm" onclick="clientRenew(<?= $plan->ID_PLAN ?>)" style="border-radius: 15px;">Pagar QR</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div id="tab-plans" class="client-tab-content">
            <div style="padding: 20px;">
                <h3 style="color: #0056b3; margin-bottom: 20px;">Planes Disponibles</h3>
                <?php
                foreach ($plans ?? \app\models\Plan::find()->where(['ESTADO' => 'ACTIVO'])->all() as $plan):
                ?>
                    <div style="padding: 20px; border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 15px;">
                        <h4 style="margin: 0 0 10px 0;"><?= Html::encode($plan->NOMBRE_PLAN) ?></h4>
                        <p style="color: #666; margin: 5px 0;"><strong>Tipo:</strong> <?= Html::encode($plan->getTipoPlanLabel()) ?></p>
                        <p style="color: #666; margin: 5px 0;"><strong>Dirigido a:</strong> <?= Html::encode($plan->TIPO_CLIENTE) ?></p>
                        <p style="color: #666; margin: 5px 0;"><strong>Duracion:</strong> <?= $plan->getDaysCount() ?> dias</p>
                        <p style="color: #28a745; font-size: 20px; font-weight: bold; margin: 10px 0;">Bs. <?= number_format($plan->MONTO, 2) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
            document.querySelectorAll('.client-tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.client-tab').forEach(function(t) { t.classList.remove('active'); });
                    document.querySelectorAll('.client-tab-content').forEach(function(c) { c.classList.remove('active'); });
                    this.classList.add('active');
                    document.getElementById(this.dataset.tab).classList.add('active');
                });
            });

            function clientRenew(planId) {
                Swal.fire({
                    title: 'Confirmar Pago QR',
                    text: 'Se procesara un pago simulado con el servicio universitario.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch('<?= Url::to(["payment/client-self-renew"]) ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: 'plan_id=' + planId
                        })
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data.status === 'success') {
                                Swal.fire('Exitoso', 'Membresia renovada: #' + data.membership_code, 'success')
                                    .then(function() { location.reload(); });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                    }
                });
            }
        </script>

    <?php else: ?>
        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
            <?= Html::encode($this->title) ?>
        </p>

        <?= \app\components\secure\modules\DailyAccessLogWidgetComponent\smart\DailyAccessLogContainer::widget() ?>
    <?php endif; ?>

</div>
