<?php
use yii\helpers\Html;

$this->title = 'Low Stock Products';
$this->params['breadcrumbs'][] = $this->title;
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    .low-stock-card {
        border-radius: 1rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease-in-out;
    }

    .low-stock-card:hover {
        transform: translateY(-5px);
    }

    .low-stock-card .card-header {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        color: #fff;
        font-weight: bold;
    }

    .low-stock-badge {
        font-size: 0.95rem;
        background-color: #dc3545;
        padding: 5px 10px;
        border-radius: 0.4rem;
        color: #fff;
    }

    .stock-title {
        font-weight: 600;
        color: #1d2b64;
    }
</style>

<div class="container mt-4">
    <h3>
        <i class="bi bi-exclamation-circle-fill me-2"></i>
        <?= Html::encode($this->title) ?>
    </h3>

    <?php if (empty($lowStockItems)): ?>
        <div class="alert alert-success shadow-sm">
            <i class="bi bi-check-circle-fill text-success me-2"></i>
            All stock levels are healthy. No products below 10.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($lowStockItems as $item): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card low-stock-card h-100 border-0">
                        <div class="card-header">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= Html::encode($item->name) ?>
                        </div>
                        <div class="card-body">
                            <p>
                                <span class="stock-title">SKU:</span>
                                <?= Html::encode($item->sku) ?>
                            </p>
                            <p>
                                <span class="stock-title">Quantity in stock:</span>
                                <span class="low-stock-badge">
                                    <?= Html::encode($item->balance_qty_instock) ?>
                                </span>
                            </p>
                            <p>
                                <span class="stock-title">Buying Price:</span>
                                Ksh <?= number_format($item->buying_price, 2) ?>
                            </p>
                            <p>
                                <span class="stock-title">Selling Price:</span>
                                Ksh <?= number_format($item->selling_price, 2) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
