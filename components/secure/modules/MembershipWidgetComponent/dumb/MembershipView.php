<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
?>

<div style="padding: 20px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            Membresias Activas
        </h2>

        <form id="filter-form" method="GET" action="<?= Url::to(['membership/index']) ?>" style="display: flex; gap: 15px; margin-bottom: 20px;">
            <input type="text" name="search" value="<?= Html::encode($search) ?>" placeholder="Buscar por nombre, CI o codigo..." style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; flex-grow: 1;">
            <button type="submit" class="btn btn-primary" style="border-radius: 5px;">Buscar</button>
            <a href="<?= Url::to(['membership/index']) ?>" class="btn btn-light" style="border-radius: 5px;">Limpiar</a>
        </form>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: left;">Codigo</th>
                        <th style="padding: 12px; text-align: left;">Cliente</th>
                        <th style="padding: 12px; text-align: left;">Plan</th>
                        <th style="padding: 12px; text-align: left;">Inicio</th>
                        <th style="padding: 12px; text-align: left;">Fin</th>
                        <th style="padding: 12px; text-align: center;">Dias Disp.</th>
                        <th style="padding: 12px; text-align: center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($memberships)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">No se encontraron membresias</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($memberships as $m): ?>
                            <tr style="border-bottom: 1px solid #e9ecef;">
                                <td style="padding: 12px; font-family: monospace;"><?= Html::encode($m->CODIGO_MEMBRESIA) ?></td>
                                <td style="padding: 12px;">
                                    <?php if ($m->client): ?>
                                        <div>
                                            <strong><?= Html::encode($m->client->NOMBRE_COMPLETO) ?></strong><br>
                                            <small style="color: #666;">CI: <?= Html::encode($m->CI_CLIENTE) ?></small>
                                        </div>
                                    <?php else: ?>
                                        <em>Desconocido</em>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php if ($m->plan): ?>
                                        <?= Html::encode($m->plan->NOMBRE_PLAN) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;"><?= date('d/m/Y', strtotime($m->FECHA_INICIO)) ?></td>
                                <td style="padding: 12px;"><?= date('d/m/Y', strtotime($m->FECHA_FIN)) ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    <span style="background: <?= $m->DIAS_DISPONIBLES > 5 ? '#d4edda' : ($m->DIAS_DISPONIBLES > 0 ? '#fff3cd' : '#f8d7da') ?>; padding: 4px 12px; border-radius: 12px; font-weight: bold;">
                                        <?= $m->DIAS_DISPONIBLES ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php if ($m->isActive()): ?>
                                        <span style="background-color: #28a745; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;">Activa</span>
                                    <?php else: ?>
                                        <span style="background-color: #dc3545; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;">Vencida</span>
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
