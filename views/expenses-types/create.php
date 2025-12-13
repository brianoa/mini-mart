<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ExpensesTypes $model */

$this->title = 'Create Expenses Types';
$this->params['breadcrumbs'][] = ['label' => 'Expenses Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expenses-types-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
