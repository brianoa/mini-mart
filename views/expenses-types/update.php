<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ExpensesTypes $model */

$this->title = 'Update Expenses Types: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Expenses Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="expenses-types-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
