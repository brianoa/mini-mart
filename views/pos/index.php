<?php
/** @var yii\web\View $this */
$this->title = 'Supermarket POS';
?>

<script src="/js/qz-tray.js"></script>

<div class="container-fluid p-3" style="background:#e6e6e6; min-height:100vh;">
    <div class="bg-white p-3 shadow rounded" style="max-width:1100px; margin:auto;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="fw-bold mb-0">Supermarket</h4>
            <div class="text-end">
                <div class="small"><?= date('d/m/Y') ?></div>
                <div class="small">Cashier: <?= Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->id ?></div>
            </div>
        </div>

        <table class="table table-sm table-bordered" id="cartTable">
            <thead class="table-light">
                <tr>
                    <th style="width:40%">Item Name</th>
                    <th style="width:10%">Qty</th>
                    <th style="width:20%">Unit Price</th>
                    <th style="width:20%">Total Price</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="d-flex justify-content-between">
            <div style="width:50%">
                <input type="text" id="barcodeInput" class="form-control form-control-sm mb-2" placeholder="Scan barcode..." autofocus>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary btn-sm" id="newSaleBtn">New Sale</button>
                    <button class="btn btn-secondary btn-sm" id="voidBtn">Void Item</button>
                    <button class="btn btn-secondary btn-sm" id="printReceiptBtn">Print Receipt</button>
                </div>
            </div>

            <div class="text-end" style="width:40%">
                <div class="fw-bold">Subtotal <span class="ms-3">Ksh<span id="subtotal">0.00</span></span></div>
                <div class="fw-bold">Tax <span class="ms-5">Ksh<span id="tax">0.00</span></span></div>
                <div class="fw-bold fs-5">Total <span class="ms-4">Ksh<span id="total">0.00</span></span></div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button class="btn btn-success px-4" id="cashBtn">Cash</button>
            <button class="btn btn-success px-4" id="creditBtn">Credit</button>
            <button class="btn btn-success px-4" id="mpesaBtn">Mpesa</button>
        </div>

        <div class="mt-3">
            <h6>Receipt Preview</h6>
            <div id="receiptBox" class="border p-2 bg-light" style="font-family:monospace; white-space:pre-wrap; min-height:200px"></div>
        </div>
        <audio id="scanBeep" src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg" preload="auto"></audio>
    </div>
</div>

<?php
$addUrl = \yii\helpers\Url::to(['pos/add']);
$checkoutUrl = \yii\helpers\Url::to(['pos/checkout']);
$clearUrl = \yii\helpers\Url::to(['pos/clear']);
$js = <<<JS
const barcodeInput = document.getElementById('barcodeInput');
const cartTableBody = document.querySelector('#cartTable tbody');
const subtotalEl = document.getElementById('subtotal');
const taxEl = document.getElementById('tax');
const totalEl = document.getElementById('total');
const receiptBox = document.getElementById('receiptBox');

let lastReceipt = '';

function renderCart(cart) {
    cartTableBody.innerHTML = '';
    let subtotal = 0;
    let tax = 0;

    cart.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>\${item.name}</td>
            <td>\${item.qty}</td>
            <td>Ksh\${item.unit_price.toFixed(2)}</td>
            <td>Ksh\${item.total.toFixed(2)}</td>
        `;
        cartTableBody.appendChild(tr);
        subtotal += item.total;
        tax += (item.tax_rate / 100) * item.total;
    });

    subtotalEl.textContent = subtotal.toFixed(2);
    taxEl.textContent = tax.toFixed(2);
    totalEl.textContent = (subtotal + tax).toFixed(2);
    document.getElementById('scanBeep').play();
}

barcodeInput.addEventListener('keypress', e => {
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

function processCheckout(method) {
    fetch('$checkoutUrl', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ payment_method: method })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return alert(data.message);

        let now = new Date();
        let r = `SUPERMARKET RECEIPT\n`;
        r += `------------------------------------------\n`;
        r += `Date: \${now.toLocaleString()}\n`;
        r += `Sale ID: \${data.sale_id}\n`;
        r += `Payment: \${method}\n`;
        r += `------------------------------------------\n`;
        r += `Item                          Qty    Total\n`;
        r += `------------------------------------------\n`;

        document.querySelectorAll('#cartTable tbody tr').forEach(row => {
            const c = row.children;

            let name  = c[0].innerText;
            let qty   = c[1].innerText;
            let total = c[3].innerText;

            // Fixed-width, aligned formatting
            let line =
                name.padEnd(28).substring(0, 28) + " " +
                qty.toString().padStart(3) + "   " +
                total.toString().padStart(7);

            r += line + '\\n';
        });

        r += `------------------------------------------\n`;
        r += `Subtotal: $\${subtotalEl.textContent}\n`;
        r += `Tax:      $\${taxEl.textContent}\n`;
        r += `TOTAL:    $\${totalEl.textContent}\n`;

        lastReceipt = r;
        receiptBox.textContent = r;
        renderCart([]);
    });
}


document.getElementById('cashBtn').onclick = () => processCheckout('Cash');
document.getElementById('creditBtn').onclick = () => processCheckout('Credit');
document.getElementById('mpesaBtn').onclick = () => processCheckout('Mpesa');

document.getElementById('newSaleBtn').onclick = () => fetch('$clearUrl', {method:'POST'}).then(()=>{renderCart([]); receiptBox.textContent='';});
document.getElementById('voidBtn').onclick = () => alert('Click qty update to remove an item or set qty to 0');

document.getElementById('printReceiptBtn').addEventListener('click', () => {
    if (!lastReceipt) {
        alert('No receipt to print!');
        return;
    }

    qz.websocket.connect().then(() => {
        return qz.printers.find("XP-80C");
    }).then(printer => {
        let config = qz.configs.create(printer);

        let data = [{
            type: 'raw',
            format: 'plain',
            data: lastReceipt
        }];

        return qz.print(config, data);
    }).then(() => {
        console.log("Receipt printed successfully");
    }).catch(err => {
        console.error("Print Error:", err);
        alert("Printer not found or QZ Tray not running.");
    });
});

JS;
$this->registerJs($js);
?>
