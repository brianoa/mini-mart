<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SaleItems $model */

$this->title = 'Create Sale Items';
$this->params['breadcrumbs'][] = ['label' => 'Sale Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sale-items-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
