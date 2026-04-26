<?php
use yii\grid\GridView;
use yii\helpers\Html;
?>

<div class="daily-access-log-component">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '', 
        'tableOptions' => ['class' => 'table table-striped', 'style' => 'width: 100%; text-align: left;'],
        'columns' => [
            [
                'attribute' => 'name',
                'label' => 'User',
                'format' => 'raw',
                'value' => function($data) {
                    return '<strong>' . Html::encode($data['name']) . '</strong>';
                }
            ],
            [
                'attribute' => 'role',
                'label' => 'Role',
                'contentOptions' => ['style' => 'text-transform: capitalize;']
            ],
            [
                'attribute' => 'registeredCount',
                'label' => 'Registered',
            ],
            [
                'attribute' => 'time',
                'label' => 'Time',
            ],
            [
                'attribute' => 'date',
                'label' => 'Date',
                'format' => ['date', 'php:d/m/y']
            ],
        ],
    ]); ?>
</div>