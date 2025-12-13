<?php
use yii\helpers\Html;

$this->title = 'Supermarket Dashboard';
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<?php if ($lowStockCount > 0): ?>
    <div class="alert shadow-sm" style="background-color: #424c74ff; color: #fff; border-left: 6px solid #ff5555;">
        <h5 class="mb-1"><i class="bi bi-exclamation-triangle-fill"></i> Low Stock Alert</h5>
        <p><?= $lowStockCount ?> product(s) are running low (less than 10 in quantity).</p>
        <?= Html::a('View Low Stock Products', ['products/low-stock'], ['class' => 'btn btn-outline-light btn-sm']) ?>
    </div>
<?php endif; ?>

<div class="container mt-5">
    <h1 class="mb-4 text-center text-primary"><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <?php
        $cards = [
            "Today's Revenue"        => $todayIncome,
            'Yesterday Revenue'      => $yesterdayIncome,
            'Weekly Revenue'         => $weeklyIncome,
            'Monthly Revenue'        => $monthlyIncome,
            'Last Month Revenue'     => $lastMonthRevenue,
            'Monthly Expenses'       => $monthlyExpenses,
            'Last Month Expenses'    => $lastMonthExpenses,
            'Last Month Net Income'  => $lastMonthNetIncome,
            'All-time Total Revenue' => $totalRevenue,
            'All-time Net Income'    => $netIncome,
        ];

        foreach ($cards as $title => $amount): ?>
            <div class="col-md-3 mb-3">
                <div class="card card-summary
                    <?= 
                    ($title === 'All-time Total Revenue') ? 'total-income-card' :
                    (($title === 'All-time Net Income') ? 'net-income-card' :
                    (($title === 'Monthly Revenue') ? 'monthly-revenue-card' :
                    (($title === 'Last Month Revenue') ? 'last-month-revenue-card' :
                    (($title === 'Monthly Expenses') ? 'monthly-expense-card' :
                    (($title === 'Last Month Expenses') ? 'last-month-expense-card' :
                    (($title === 'Last Month Net Income') ? 'last-month-net-card' : '')))))) ?>">
                    <div class="card-header"><?= Html::encode($title) ?></div>
                    <div class="card-body">Ksh <?= number_format($amount, 2) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <hr class="my-5">

    <div class="row">
        <div class="col-md-6">
            <h5 class="text-center">Income Overview</h5>
            <canvas id="barChart" style="width:100%; max-width:400px; height:250px;"></canvas>
        </div>
        <div class="col-md-6">
            <h5 class="text-center">Profit Chart</h5>
            <canvas id="profitChart" style="width:100%; max-width:350px; height:200px;"></canvas>
        </div>
    </div>

    <hr class="my-5">

    <div class="card shadow">
        <div class="card-header custom-header text-white">
            <h5 class="mb-0">Top 5 Best-Selling Products</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped table-compact mb-0">
                <thead class="custom-table-head">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Total Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bestSellingItems as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= Html::encode($item['name']) ?></td>
                            <td><?= Html::encode($item['total_sold']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bestSellingItems)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No sales data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Today', 'This Week', 'This Month', 'All Time'],
            datasets: [{
                label: 'Ksh',
                data: [{$todayIncome}, {$weeklyIncome}, {$monthlyIncome}, {$totalRevenue}],
                backgroundColor: ['#ffb347', '#f88379', '#6c91bf', '#7fc8a9'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Income Summary' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => 'Ksh ' + value
                    }
                }
            }
        }
    });

    const profitCtx = document.getElementById('profitChart').getContext('2d');
    new Chart(profitCtx, {
        type: 'bar',
        data: {
            labels: ['Existing Stock Value', 'Total Sales Value'],
            datasets: [{
                label: 'Ksh',
                data: [{$totalStockValue}, {$totalSalesValue}],
                backgroundColor: ['#61707dff', '#00c0ef'],
                borderColor: ['#5a6268', '#00acc1'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Stock Value vs Sales (Profit Insight)' },
                tooltip: {
                    callbacks: {
                        label: context => 'Ksh ' + context.raw.toLocaleString()
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => 'Ksh ' + value.toLocaleString()
                    }
                }
            }
        }
    });
");
?>
<style>
.card-summary {
    border-radius: 1rem;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    transition: 0.3s ease-in-out;
}
.card-summary:hover {
    transform: translateY(-5px);
}
.card-header {
    font-weight: bold;
    font-size: 1.2rem;
    background: #1d2b64;
    color: white;
    border-radius: 1rem 1rem 0 0;
}
.card-body {
    font-size: 1.5rem;
    color: #333;
}

/* Compact table */
.table-compact th,
.table-compact td {
    padding: 0.45rem 0.75rem;
    font-size: 0.95rem;
    vertical-align: middle;
}
.table-compact thead th {
    font-weight: 600;
}
.table-compact {
    margin-bottom: 0;
}

/* All-time Total Revenue card */
.total-income-card {
    background: linear-gradient(135deg, #000d1a, #000d1a);
    color: white;
}
.total-income-card .card-header {
    background: transparent;
    color: #fff;
    font-size: 1.1rem;
    font-weight: bold;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}
.total-income-card .card-body {
    font-size: 1.7rem;
    color: #fff;
}

/* All-time Net Income card */
.net-income-card {
    background: linear-gradient(135deg, #006666, #006666);
    color: white;
}
.net-income-card .card-header {
    background: transparent;
    color: #fff;
    font-size: 1.1rem;
    font-weight: bold;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}
.net-income-card .card-body {
    font-size: 1.7rem;
    color: #fff;
}

/* Monthly Revenue card */
.monthly-revenue-card {
    background: linear-gradient(135deg, #336699, #19334d);
    color: white;
}
.monthly-revenue-card .card-header,
.last-month-revenue-card .card-header,
.monthly-expense-card .card-header,
.last-month-expense-card .card-header,
.last-month-net-card .card-header {
    background: transparent;
    color: #fff;
    font-size: 1.1rem;
    font-weight: bold;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}
.monthly-revenue-card .card-body {
    font-size: 1.7rem;
    color: #fff;
}

/* Last Month Revenue card */
.last-month-revenue-card {
    background: linear-gradient(135deg, #3d5c5c, #1f2e2e);
    color: white;
}
.last-month-revenue-card .card-body {
    font-size: 1.7rem;
    color: #fff;
}

/* Monthly Expenses card */
.monthly-expense-card {
    background: linear-gradient(135deg, #800040, #4d0026);
    color: white;
}
.monthly-expense-card .card-body {
    font-size: 1.7rem;
    color: #fff;
}

/* Last Month Expenses card */
.last-month-expense-card {
    background: linear-gradient(135deg, #ff3333, #800000);
    color: white;
}
.last-month-expense-card .card-body {
    font-size: 1.7rem;
    color: #fff;
}

/* Last Month Net Income card */
.last-month-net-card {
    background: linear-gradient(135deg, #26734d, #06130d);
    color: white;
}
.last-month-net-card .card-body {
    font-size: 1.7rem;
    color: #fff;
}
</style>
