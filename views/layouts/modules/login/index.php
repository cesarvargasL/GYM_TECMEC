<?php
use yii\helpers\Html;
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        body, html { 
            height: 100%; 
            margin: 0; 
            padding: 0; 
            background-color: #f4f6f9; 
            font-family: sans-serif;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>
    
    <?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>