<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SaleItems $model */

$this->title = 'Update Sale Items: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sale Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sale-items-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
