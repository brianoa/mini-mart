<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="container p-4">

<a href="<?= Url::to(['client-purchases']) ?>" class="btn btn-secondary rounded-pill mb-4">
← Back to Clients
</a>

<div class="card shadow-lg border-0 rounded-4">

    <div class="card-header bg-white border-0">
        <h4 class="fw-bold">
            <?= Html::encode($sale->client_name) ?>
        </h4>
        <small class="text-muted">
            <?= date('d M, Y - h:i A', strtotime($sale->created_at)) ?>
        </small>
    </div>

    <div class="card-body">

        <div class="row mb-4">
            <div class="col-md-3"><strong>Phone:</strong> <?= Html::encode($sale->client_phone ?: '-') ?></div>
            <div class="col-md-3"><strong>Total:</strong> Ksh <?= number_format($sale->total_amount, 2) ?></div>
            <div class="col-md-3"><strong>Paid:</strong> Ksh <?= number_format($sale->amount_paid, 2) ?></div>
            <div class="col-md-3"><strong>Method:</strong> <?= Html::encode($sale->payment_method) ?></div>
        </div>

        <h5 class="fw-bold">Purchased Items</h5>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th width="100">Qty</th>
                    <th width="150">Unit Price</th>
                    <th width="150">Subtotal</th>
                </tr>
            </thead>
            <tbody>

            <?php
            $grandTotal = 0;
            foreach ($items as $item):
                $grandTotal += $item->subtotal;
            ?>
                <tr>
                    <td><?= Html::encode($item->product->name) ?></td>
                    <td><?= $item->quantity ?></td>
                    <td>Ksh <?= number_format($item->unit_price, 2) ?></td>
                    <td class="fw-bold">Ksh <?= number_format($item->subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>

            </tbody>

            <tfoot>
                <tr class="table-dark">
                    <th colspan="3">TOTAL</th>
                    <th>Ksh <?= number_format($grandTotal, 2) ?></th>
                </tr>
            </tfoot>
        </table>

    </div>
</div>
</div>
