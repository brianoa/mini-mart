<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<!-- Include Bootstrap Icons if not yet added -->
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<div class="stock-restock-form card p-4 mx-auto"
     style="max-width: 500px; border-radius: 1.5rem; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); transition: 0.3s ease-in-out; margin-top: 30px;">

    <div class="card-header text-white text-center"
         style="background: #1d2b64; border-radius: 1.5rem 1.5rem 0 0;">
        <h4 class="mb-0">Restock Product</h4>
    </div>

    <div class="card-body">

        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'restock-form']
        ]); ?>

        <!-- Product name (readonly) -->
        <div class="form-group mb-3">
            <?= $form->field($model, 'name')->textInput([
                'readonly' => true,
                'class' => 'form-control rounded-pill',
                'style' => 'background-color: #f0f0f0; font-weight: bold;'
            ]) ?>
        </div>

        <!-- SKU (readonly so user knows which product) -->
        <div class="form-group mb-3">
            <?= $form->field($model, 'sku')->textInput([
                'readonly' => true,
                'class' => 'form-control rounded-pill',
                'style' => 'background-color: #f0f0f0;'
            ]) ?>
        </div>

        <!-- Restock quantity: we reuse initial_qty_instock as the input -->
        <div class="form-group mb-4">
            <?= $form->field($model, 'initial_qty_instock')->textInput([
                'type' => 'number',
                'min' => 1,
                'class' => 'form-control rounded-pill',
                'placeholder' => 'Enter quantity to add'
            ])->label('Restock Quantity') ?>
        </div>

        <!-- Buying price (restocking cost price) -->
        <div class="form-group mb-4">
            <?= $form->field($model, 'buying_price')->textInput([
                'type' => 'number',
                'step' => '0.01',
                'class' => 'form-control rounded-pill',
                'placeholder' => 'Enter the restocking (buying) price'
            ]) ?>
        </div>

        <!-- Selling price -->
        <div class="form-group mb-4">
            <?= $form->field($model, 'selling_price')->textInput([
                'type' => 'number',
                'step' => '0.01',
                'class' => 'form-control rounded-pill',
                'placeholder' => 'Enter the selling price',
            ]) ?>
        </div>

        <div class="form-group text-center">
            <?= Html::submitButton('<i class="bi bi-box-arrow-in-down"></i> Restock', [
                'class' => 'btn rounded-pill px-5 py-2',
                'style' => 'background: #1d2b64; color: white; font-weight: bold; font-size: 1rem; border: none;',
                'encode' => false, // allow icon HTML
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<style>
.restock-form .form-group label {
    font-weight: 500;
    color: #333;
}
.restock-form .form-control {
    height: 45px;
    padding: 10px 20px;
    font-size: 1rem;
    transition: all 0.2s ease-in-out;
}
.restock-form .form-control:focus {
    border-color: #1d2b64;
    box-shadow: 0 0 0 0.2rem rgba(29, 43, 100, 0.25);
}
.stock-restock-form:hover {
    transform: translateY(-3px);
}
</style>
