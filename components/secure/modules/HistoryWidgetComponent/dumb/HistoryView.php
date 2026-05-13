<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
?>

<div style="padding: 20px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            Historial de Asistencias
        </h2>

        <form id="filter-form" method="GET" action="<?= Url::to(['history/index']) ?>" style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: flex-end;">
            <div>
                <label style="font-size: 13px; color: #666;">Desde</label><br>
                <input type="date" name="date_from" id="date-from" value="<?= Html::encode($dateFrom) ?>" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div>
                <label style="font-size: 13px; color: #666;">Hasta</label><br>
                <input type="date" name="date_to" id="date-to" value="<?= Html::encode($dateTo) ?>" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div style="flex-grow: 1;">
                <label style="font-size: 13px; color: #666;">Buscar</label><br>
                <input type="text" name="search" id="search-input" value="<?= Html::encode($search) ?>" placeholder="Nombre o CI..." style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            </div>
            <button type="submit" class="btn btn-primary" style="border-radius: 5px;">Filtrar</button>
            <a href="<?= Url::to(['history/index']) ?>" class="btn btn-light" style="border-radius: 5px;">Limpiar</a>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateFrom = document.getElementById('date-from');
            var dateTo = document.getElementById('date-to');
            var searchInput = document.getElementById('search-input');
            var form = document.getElementById('filter-form');
            var dateToChanged = false;

            if (dateTo) {
                dateTo.addEventListener('change', function() {
                    dateToChanged = true;
                    form.submit();
                });
            }

            if (dateFrom) {
                dateFrom.addEventListener('change', function() {
                    if (dateToChanged) {
                        form.submit();
                    }
                });
            }

            if (searchInput) {
                var searchTimeout = null;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        form.submit();
                    }, 500);
                });
            }
        });
        </script>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: left;">Nro</th>
                        <th style="padding: 12px; text-align: left;">Nombre</th>
                        <th style="padding: 12px; text-align: left;">Fecha/Hora</th>
                        <th style="padding: 12px; text-align: left;">Plan</th>
                        <th style="padding: 12px; text-align: left;">Tipo</th>
                        <th style="padding: 12px; text-align: left;">Pago</th>
                        <th style="padding: 12px; text-align: right;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendances)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">No se encontraron registros</td>
                        </tr>
                    <?php else: ?>
                        <?php $counter = $pages->offset + 1; ?>
                        <?php foreach ($attendances as $att): ?>
                            <tr style="border-bottom: 1px solid #e9ecef;">
                                <td style="padding: 12px;"><?= $counter++ ?></td>
                                <td style="padding: 12px;">
                                    <?php if ($att->client): ?>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <?php if ($att->client->AVATAR): ?>
                                                <img src="<?= Html::encode($att->client->AVATAR) ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= Html::encode($att->client->NOMBRE_COMPLETO) ?></strong><br>
                                                <small style="color: #666;">CI: <?= Html::encode($att->CI_CLIENTE) ?></small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <em>Desconocido</em>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;"><?= date('d/m/Y H:i', strtotime($att->FECHA_DE_INGRESO)) ?></td>
                                <td style="padding: 12px;">
                                    <?php if ($att->membership && $att->membership->plan): ?>
                                        <?= Html::encode($att->membership->plan->NOMBRE_PLAN) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php if ($att->client): ?>
                                        <span style="background: #e9ecef; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                                            <?= Html::encode($att->client->TIPO_CLIENTE) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php
                                    $payment = null;
                                    if ($att->membership && !empty($att->membership->payments)) {
                                        $payment = $att->membership->payments[0];
                                    }
                                    ?>
                                    <?php if ($payment): ?>
                                        <span style="background: <?= $payment->TIPO_PAGO === 'QR' ? '#d4edda' : '#cce5ff' ?>; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                                            <?= $payment->getTipoPagoLabel() ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: right;">
                                    <?php if ($payment): ?>
                                        <strong>Bs. <?= number_format($payment->MONTO, 2) ?></strong>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
            <?= LinkPager::widget([
                'pagination' => $pages,
                'options' => ['class' => 'pagination mb-0'],
                'linkOptions' => ['class' => 'page-link'],
                'pageCssClass' => 'page-item',
                'activePageCssClass' => 'active',
                'disabledPageCssClass' => 'disabled',
            ]) ?>
        </div>
    </div>
</div>
