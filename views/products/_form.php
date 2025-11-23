<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Products $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="products-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'buying_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'selling_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tax_rate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'initial_qty_instock')->textInput() ?>

    <?= $form->field($model, 'sold_qty_instock')->textInput() ?>

    <?= $form->field($model, 'balance_qty_instock')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
