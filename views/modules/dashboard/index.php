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
            <button class="client-tab" data-tab="tab-receipts">Mis Recibos</button>
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

                <h4 style="color: #333; margin-bottom: 10px;">Calendario de Asistencias - <?= date('F Y') ?></h4>
                <div class="calendar-grid" id="attendance-calendar">
                    <div class="calendar-day calendar-day-header">Lun</div>
                    <div class="calendar-day calendar-day-header">Mar</div>
                    <div class="calendar-day calendar-day-header">Mie</div>
                    <div class="calendar-day calendar-day-header">Jue</div>
                    <div class="calendar-day calendar-day-header">Vie</div>
                    <div class="calendar-day calendar-day-header">Sab</div>
                    <div class="calendar-day calendar-day-header">Dom</div>
                    <?php
                    $today = new DateTime();
                    $firstDayOfMonth = new DateTime($today->format('Y-m-01'));
                    $lastDayOfMonth = new DateTime($today->format('Y-m-t'));
                    $membershipStart = new DateTime($activeMembership->FECHA_INICIO);
                    $membershipEnd = new DateTime($activeMembership->FECHA_FIN);
                    $attendanceDates = $attendanceDates ?? [];

                    $current = clone $firstDayOfMonth;
                    $firstDayOfWeek = (int)$current->format('N') - 1;
                    for ($i = 0; $i < $firstDayOfWeek; $i++) {
                        echo '<div class="calendar-day" style="opacity: 0.3;"></div>';
                    }

                    while ($current <= $lastDayOfMonth) {
                        $dateStr = $current->format('Y-m-d');
                        $isWithinMembership = ($current >= $membershipStart && $current <= $membershipEnd);
                        $isAttended = in_array($dateStr, $attendanceDates);
                        $isToday = $current->format('Y-m-d') === $today->format('Y-m-d');
                        $isFuture = $current > $today;

                        $classes = 'calendar-day';
                        if ($isAttended) $classes .= ' attended';
                        if ($isToday) $classes .= ' today';
                        if ($isWithinMembership && !$isAttended && !$isFuture) $classes .= ' missed';
                        if ($isFuture) $classes .= ' future';

                        $content = $current->format('j');
                        if ($isAttended) $content .= ' ✓';
                        elseif ($isWithinMembership && !$isAttended && !$isFuture) $content .= ' ✗';

                        echo '<div class="' . $classes . '" title="' . $dateStr . '">' . $content . '</div>';

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
                <p style="color: #666; margin-bottom: 20px;">Selecciona un plan para renovar tu membresia.</p>

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

        <div id="tab-receipts" class="client-tab-content">
            <div style="padding: 20px;">
                <h3 style="color: #0056b3; margin-bottom: 20px;">Mis Recibos</h3>
                
                <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                    <div>
                        <label style="font-size: 13px; color: #666;">Desde</label><br>
                        <input type="date" id="receipt-date-from" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div>
                        <label style="font-size: 13px; color: #666;">Hasta</label><br>
                        <input type="date" id="receipt-date-to" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div>
                        <label style="font-size: 13px; color: #666;">Tipo Pago</label><br>
                        <select id="receipt-type-filter" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                            <option value="">Todos</option>
                            <option value="QR">QR</option>
                            <option value="EFECTIVO">Efectivo</option>
                        </select>
                    </div>
                    <div style="align-self: flex-end;">
                        <button id="btn-filter-receipts" class="btn btn-primary" style="border-radius: 5px;">Filtrar</button>
                    </div>
                </div>

                <div id="receipts-list" style="max-height: 500px; overflow-y: auto;"></div>
            </div>
        </div>

        <script>
            document.querySelectorAll('.client-tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.client-tab').forEach(function(t) { t.classList.remove('active'); });
                    document.querySelectorAll('.client-tab-content').forEach(function(c) { c.classList.remove('active'); });
                    this.classList.add('active');
                    document.getElementById(this.dataset.tab).classList.add('active');
                    
                    if (this.dataset.tab === 'tab-receipts') {
                        loadClientReceipts();
                    }
                });
            });

            function loadClientReceipts() {
                var container = document.getElementById('receipts-list');
                container.innerHTML = '<p style="text-align: center; color: #666;">Cargando recibos...</p>';

                fetch('<?= Url::to(["payment/client-receipts"]) ?>')
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.status === 'success' && data.receipts.length > 0) {
                            window._allReceipts = data.receipts;
                            renderReceipts(data.receipts);
                        } else {
                            container.innerHTML = '<p style="text-align: center; color: #666;">No tienes recibos registrados.</p>';
                        }
                    })
                    .catch(function() {
                        container.innerHTML = '<p style="text-align: center; color: #dc3545;">Error al cargar recibos.</p>';
                    });
            }

            function renderReceipts(receipts) {
                var container = document.getElementById('receipts-list');
                if (receipts.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #666;">No se encontraron recibos con los filtros aplicados.</p>';
                    return;
                }

                var html = '';
                receipts.forEach(function(r, index) {
                    html += '<div class="receipt-item" data-index="' + index + '" style="padding: 15px; border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 10px; cursor: pointer; transition: background 0.2s;">' +
                        '<div style="display: flex; justify-content: space-between; align-items: center;">' +
                        '<div>' +
                        '<strong>Recibo #' + r.id_recibo + '</strong><br>' +
                        '<small style="color: #666;">' + r.fecha + ' - ' + r.tipo_pago_label + '</small>' +
                        '</div>' +
                        '<div style="text-align: right;">' +
                        '<strong style="color: #28a745;">Bs. ' + parseFloat(r.monto).toFixed(2) + '</strong><br>' +
                        '<small style="color: #666;">' + (r.plan ? r.plan.nombre : 'N/A') + '</small>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                });
                container.innerHTML = html;

                container.querySelectorAll('.receipt-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        var idx = parseInt(this.getAttribute('data-index'));
                        showReceiptPopup(window._allReceipts[idx]);
                    });
                    item.addEventListener('mouseover', function() { this.style.background = '#f8f9fa'; });
                    item.addEventListener('mouseout', function() { this.style.background = 'white'; });
                });
            }

            document.getElementById('btn-filter-receipts').addEventListener('click', function() {
                var dateFrom = document.getElementById('receipt-date-from').value;
                var dateTo = document.getElementById('receipt-date-to').value;
                var typeFilter = document.getElementById('receipt-type-filter').value;

                if (!window._allReceipts) return;

                var filtered = window._allReceipts.filter(function(r) {
                    var pass = true;
                    if (dateFrom) {
                        pass = pass && r.fecha.substring(0, 10) >= dateFrom;
                    }
                    if (dateTo) {
                        pass = pass && r.fecha.substring(0, 10) <= dateTo;
                    }
                    if (typeFilter) {
                        pass = pass && r.tipo_pago === typeFilter;
                    }
                    return pass;
                });

                renderReceipts(filtered);
            });

            function showReceiptPopup(receipt) {
                var html = '<div style="text-align: left; font-size: 14px; position: relative;">';
                html += '<span onclick="Swal.close();" style="position: absolute; top: -10px; right: -10px; cursor: pointer; font-size: 24px; color: #999; background: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">&times;</span>';
                html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">';
                html += '<h4 style="margin: 0 0 10px; color: #0056b3;">Recibo #' + receipt.id_recibo + '</h4>';
                html += '<p><strong>Fecha:</strong> ' + receipt.fecha + '</p>';
                html += '<p><strong>Cliente:</strong> ' + receipt.cliente.nombre + ' (CI: ' + receipt.cliente.ci + ')</p>';
                html += '<p><strong>Tipo Cliente:</strong> ' + receipt.cliente.tipo_cliente + '</p>';
                if (receipt.administrador) {
                    html += '<p><strong>Atendido por:</strong> ' + receipt.administrador.nombre + '</p>';
                }
                html += '</div>';
                
                html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">';
                html += '<p><strong>Plan:</strong> ' + (receipt.plan ? receipt.plan.nombre : 'N/A') + '</p>';
                html += '<p><strong>Tipo Plan:</strong> ' + (receipt.plan ? receipt.plan.tipo_plan : 'N/A') + '</p>';
                html += '<p><strong>Membresia:</strong> ' + receipt.membresia.codigo + '</p>';
                html += '<p><strong>Vigencia:</strong> ' + receipt.membresia.fecha_inicio + ' a ' + receipt.membresia.fecha_fin + '</p>';
                html += '</div>';
                
                html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">';
                html += '<p><strong>Metodo de Pago:</strong> ' + receipt.tipo_pago_label + '</p>';
                if (receipt.codigo_pago) html += '<p><strong>Codigo Pago:</strong> ' + receipt.codigo_pago + '</p>';
                if (receipt.nro_documento) html += '<p><strong>Nro Documento:</strong> ' + receipt.nro_documento + '</p>';
                html += '<p style="font-size: 24px; color: #28a745; font-weight: bold; text-align: center; margin: 10px 0;">Bs. ' + parseFloat(receipt.monto).toFixed(2) + '</p>';
                html += '</div>';
                
                html += '</div>';

                Swal.fire({
                    title: 'Recibo #' + receipt.id_recibo,
                    html: html,
                    width: 600,
                    showCancelButton: true,
                    confirmButtonText: 'Imprimir',
                    cancelButtonText: 'Cerrar',
                    showDenyButton: true,
                    denyButtonText: 'Descargar PDF'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        printReceipt(receipt.id_recibo);
                    } else if (result.isDenied) {
                        downloadReceiptPdf(receipt.id_recibo);
                    }
                });
            }

            function printReceipt(paymentId) {
                var url = '<?= Url::to(["payment/export-receipt-pdf"]) ?>?id=' + paymentId;
                var win = window.open('', '_blank');
                fetch(url)
                    .then(function(res) { return res.text(); })
                    .then(function(html) {
                        win.document.open();
                        win.document.write(html);
                        win.document.close();
                        setTimeout(function() { win.print(); }, 500);
                    });
            }

            function downloadReceiptPdf(paymentId) {
                var url = '<?= Url::to(["payment/export-receipt-pdf"]) ?>?id=' + paymentId;
                var win = window.open('', '_blank');
                fetch(url)
                    .then(function(res) { return res.text(); })
                    .then(function(html) {
                        win.document.open();
                        win.document.write(html);
                        win.document.close();
                    });
            }

            function clientRenew(planId) {
                fetch('<?= Url::to(["payment/check-active-membership"]) ?>?ci=<?= Yii::$app->user->identity->CI ?>')
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.hasActive) {
                            Swal.fire('Error', 'Ya tienes una membresia activa hasta ' + data.endDate + ' (' + data.remainingDays + ' dias restantes). Debe esperar a que venza para renovar.', 'warning');
                            return;
                        }

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
                                        showReceiptPopup(data.receipt);
                                        setTimeout(function() { location.reload(); }, 2000);
                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                });
                            }
                        });
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
