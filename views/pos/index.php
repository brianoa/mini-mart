<?php
/** @var yii\web\View $this */
$this->title = 'Supermarket POS';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h3 class="mb-3 text-primary">🛒 Supermarket POS</h3>

            <div class="card shadow-sm p-3 mb-3">
                <label for="barcodeInput" class="form-label fw-bold">Scan or Enter Barcode:</label>
                <input type="text" id="barcodeInput" class="form-control" placeholder="Scan barcode here..." autofocus>
            </div>

            <table class="table table-bordered" id="cartTable">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="text-end fw-bold">
                <p>Subtotal: $<span id="subtotal">0.00</span></p>
                <p>Tax: $<span id="tax">0.00</span></p>
                <p class="fs-5">Total: $<span id="total">0.00</span></p>
            </div>

            <button id="checkoutBtn" class="btn btn-success w-100 mt-2">Checkout</button>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm p-3">
                <h5 class="text-secondary">🧾 Receipt Preview</h5>
                <pre id="receiptBox" class="bg-light p-3 rounded" style="min-height: 300px;"></pre>
            </div>
        </div>
    </div>

    <audio id="scanBeep" 
    src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg" preload="auto">
  </audio>
</div>

<?php
$addUrl = \yii\helpers\Url::to(['pos/add']);
$checkoutUrl = \yii\helpers\Url::to(['pos/checkout']);
$cartUrl = \yii\helpers\Url::to(['pos/cart']);
$js = <<<'JS'
const barcodeInput = document.getElementById('barcodeInput');
const cartTableBody = document.querySelector('#cartTable tbody');
const subtotalEl = document.getElementById('subtotal');
const taxEl = document.getElementById('tax');
const totalEl = document.getElementById('total');
const receiptBox = document.getElementById('receiptBox');

function renderCart(cart) {
    cartTableBody.innerHTML = '';
    let subtotal = 0;
    let tax = 0;

    cart.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.name}</td>
            <td>${item.qty}</td>
            <td>$${item.unit_price.toFixed(2)}</td>
            <td>$${item.total.toFixed(2)}</td>
        `;
        cartTableBody.appendChild(tr);

        subtotal += item.total;
        tax += (item.tax_rate / 100) * item.total;
    });

    subtotalEl.textContent = subtotal.toFixed(2);
    taxEl.textContent = tax.toFixed(2);
    totalEl.textContent = (subtotal + tax).toFixed(2);
}

barcodeInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const sku = barcodeInput.value.trim();
        if (!sku) return;
        fetch('$addUrl', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({sku})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderCart(data.cart);
                barcodeInput.value = '';
            }
        });
    }
});

document.getElementById('checkoutBtn').addEventListener('click', function() {
    fetch('$checkoutUrl', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({payment_method: 'Cash'})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            receiptBox.textContent = JSON.stringify(data, null, 2);
            renderCart([]);
        }
    });
});
JS;

$this->registerJs($js);
?>