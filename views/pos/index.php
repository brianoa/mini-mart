<?php
/** @var yii\web\View $this */
$this->title = 'Supermarket POS';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h3 class="mb-3 text-primary">🛒 Supermarket POS</h3>

            <!-- Barcode Input -->
            <div class="card shadow-sm p-3 mb-3">
                <label for="barcodeInput" class="form-label fw-bold">Scan or Enter Barcode:</label>
                <input type="text" id="barcodeInput" class="form-control" placeholder="Scan barcode here..." autofocus>
            </div>

            <!-- Cart Table -->
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

            <!-- Totals -->
            <div class="text-end fw-bold">
                <p>Subtotal: $<span id="subtotal">0.00</span></p>
                <p>Tax: $<span id="tax">0.00</span></p>
                <p class="fs-5">Total: $<span id="total">0.00</span></p>
            </div>

            <!-- Payment Buttons -->
            <div class="mt-3 text-center">
                <button id="cashBtn" class="btn btn-success m-1 w-25">Cash</button>
                <button id="mpesaBtn" class="btn btn-warning m-1 w-25">Mpesa</button>
                <button id="balanceBtn" class="btn btn-info m-1 w-25">Balance</button>
                <button id="printReceiptBtn" class="btn btn-secondary m-1 w-25">🖨️ Print Receipt</button>
            </div>
        </div>

        <!-- Receipt Preview -->
        <div class="col-md-6">
            <div class="card shadow-sm p-3">
                <h5 class="text-secondary">🧾 Receipt Preview</h5>
                <div id="receiptBox" class="bg-white p-3 rounded border" style="min-height:300px; font-family:monospace; white-space:pre-wrap;"></div>
            </div>
        </div>
    </div>

    <audio id="scanBeep" src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg" preload="auto"></audio>
</div>

<?php
$addUrl = \yii\helpers\Url::to(['pos/add']);
$checkoutUrl = \yii\helpers\Url::to(['pos/checkout']);
$js = <<<JS
const barcodeInput = document.getElementById('barcodeInput');
const cartTableBody = document.querySelector('#cartTable tbody');
const subtotalEl = document.getElementById('subtotal');
const taxEl = document.getElementById('tax');
const totalEl = document.getElementById('total');
const receiptBox = document.getElementById('receiptBox');

let lastReceipt = ''; // store last generated receipt

function renderCart(cart) {
    cartTableBody.innerHTML = '';
    let subtotal = 0;
    let tax = 0;

    cart.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>\${item.name}</td>
            <td>\${item.qty}</td>
            <td>$\${item.unit_price.toFixed(2)}</td>
            <td>$\${item.total.toFixed(2)}</td>
        `;
        cartTableBody.appendChild(tr);
        tr.classList.add('table-info');
        setTimeout(() => tr.classList.remove('table-info'), 300);
        subtotal += item.total;
        tax += (item.tax_rate / 100) * item.total;
    });

    subtotalEl.textContent = subtotal.toFixed(2);
    taxEl.textContent = tax.toFixed(2);
    totalEl.textContent = (subtotal + tax).toFixed(2);
    document.getElementById('scanBeep').play();
}

// --- Handle barcode scanning ---
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

// --- Checkout function ---
function processCheckout(paymentMethod) {
    fetch('$checkoutUrl', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ payment_method: paymentMethod })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            let now = new Date();
            let receipt = '';
            receipt += `        SUPERMARKET POS RECEIPT\\n`;
            receipt += `--------------------------------------\\n`;
            receipt += `Date: \${now.toLocaleString()}\\n`;
            receipt += `Sale ID: \${data.sale_id}\\n`;
            receipt += `Payment: \${paymentMethod}\\n`;
            receipt += `--------------------------------------\\n`;
            receipt += `Item                Qty   Price   Total\\n`;
            receipt += `--------------------------------------\\n`;

            const rows = document.querySelectorAll('#cartTable tbody tr');
            rows.forEach(row => {
                const cols = row.querySelectorAll('td');
                const name = cols[0].innerText.padEnd(18, ' ');
                const qty = cols[1].innerText.padStart(3, ' ');
                const price = cols[2].innerText.padStart(7, ' ');
                const total = cols[3].innerText.padStart(7, ' ');
                receipt += `\${name}\${qty}\${price}\${total}\\n`;
            });

            receipt += `--------------------------------------\\n`;
            receipt += `Subtotal: $\${subtotalEl.textContent}\\n`;
            receipt += `Tax: $\${taxEl.textContent}\\n`;
            receipt += `TOTAL: $\${totalEl.textContent}\\n`;
            receipt += `--------------------------------------\\n`;
            receipt += `Thank you for shopping with us!\\n`;

            lastReceipt = receipt;
            receiptBox.textContent = receipt;
            renderCart([]);
        } else {
            alert('Checkout failed: ' + data.message);
        }
    })
    .catch(err => console.error('Checkout error:', err));
}

// --- Payment buttons ---
document.getElementById('cashBtn').addEventListener('click', () => processCheckout('Cash'));
document.getElementById('mpesaBtn').addEventListener('click', () => processCheckout('Mpesa'));

// --- Balance button (calculate cash balance) ---
document.getElementById('balanceBtn').addEventListener('click', () => {
    const total = parseFloat(totalEl.textContent);
    if (total <= 0) return alert('Cart is empty.');
    const given = parseFloat(prompt('Enter cash received:', total));
    if (isNaN(given) || given < total) {
        alert('Invalid or insufficient amount received.');
        return;
    }
    const balance = (given - total).toFixed(2);
    alert(` Balance to return: $\${balance}`);
});

// --- Print Receipt button ---
document.getElementById('printReceiptBtn').addEventListener('click', () => {
    if (!lastReceipt) return alert('No receipt to print!');
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    printWindow.document.write(`<pre>\${lastReceipt}</pre>`);
    printWindow.print();
    printWindow.close();


    
});
JS;

$this->registerJs($js);
?>