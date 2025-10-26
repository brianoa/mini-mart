<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SaleItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="sale-item-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'sale_id') ?>

    <?= $form->field($model, 'product_id') ?>

    <?= $form->field($model, 'qty') ?>

    <?= $form->field($model, 'unit_price') ?>

    <?php // echo $form->field($model, 'total_price') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
