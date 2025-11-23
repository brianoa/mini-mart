<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Sales $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="sales-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'client_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList([ 'PAID' => 'PAID', 'NOT PAID' => 'NOT PAID', 'CANCELLED' => 'CANCELLED', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'payment_method')->dropDownList([ 'CASH' => 'CASH', 'MPESA' => 'MPESA', 'CARD' => 'CARD', 'OTHER' => 'OTHER', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'total_amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
