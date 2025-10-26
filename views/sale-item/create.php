<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SaleItem $model */

$this->title = 'Create Sale Item';
$this->params['breadcrumbs'][] = ['label' => 'Sale Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sale-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
