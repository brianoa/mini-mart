<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="container-fluid p-4">

    <div class="card shadow-lg border-0 rounded-4">

        <div class="card-header bg-white border-0">
            <h4 class="fw-bold mb-0">Client Purchases</h4>
            <small class="text-muted">List of all customers who have bought items</small>
        </div>

        <div class="card-body">

            <form method="get" class="row mb-4 g-2">
                <div class="col-md-4">
                    <input type="text"
                           name="client"
                           value="<?= Html::encode($client) ?>"
                           class="form-control rounded-pill shadow-sm"
                           placeholder="Search client name...">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary rounded-pill w-100">
                        Search
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Phone</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($sale->created_at)) ?></td>
                            <td><?= Html::encode($sale->client_name) ?></td>
                            <td><?= Html::encode($sale->client_phone ?: '-') ?></td>
                            <td>Ksh <?= number_format($sale->total_amount, 2) ?></td>
                            <td>Ksh <?= number_format($sale->amount_paid, 2) ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= Html::encode($sale->payment_method) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($sale->status == 'PAID'): ?>
                                    <span class="badge bg-success">PAID</span>
                                <?php elseif ($sale->status == 'NOT PAID'): ?>
                                    <span class="badge bg-warning text-dark">NOT PAID</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">CANCELLED</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end">
                                <a href="<?= Url::to(['view-client-purchase', 'id' => $sale->id]) ?>"
                                   class="btn btn-outline-primary btn-sm rounded-pill">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>
