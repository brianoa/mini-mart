<?php
use yii\helpers\Url;
$this->title = 'POS';
?>
<div class="pos">
  <h2>Supermarket POS</h2>

  <div>
    <!-- Hidden input that receives scanner input -->
    <input id="barcode-input" placeholder="Scan barcode or type SKU and press Enter" autofocus />
    <button id="btn-manual">Add</button>
  </div>

  <table id="cart-table">
    <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
    <tbody>
      <?php foreach($cart as $c): ?>
        <tr data-product-id="<?= $c['product_id'] ?>">
          <td><?= $c['name'] ?></td>
          <td><?= $c['qty'] ?></td>
          <td><?= number_format($c['unit_price'],2) ?></td>
          <td><?= number_format($c['total'],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div id="totals">
    <p>Subtotal: <span id="subtotal">0.00</span></p>
    <p>Tax: <span id="tax">0.00</span></p>
    <p>Total: <strong id="total">0.00</strong></p>
  </div>

  <div id="payments">
    <button class="pay-btn" data-method="Cash">Cash</button>
    <button class="pay-btn" data-method="Credit">Credit</button>
    <button class="pay-btn" data-method="Mpesa">Mpesa</button>
  </div>
</div>

<script src="https://unpkg.com/onscan.js@1.6.0/onscan.min.js"></script>
<script>
const lookupUrl = "<?= Url::to(['pos/lookup']) ?>";
const addUrl = "<?= Url::to(['pos/add']) ?>";
const checkoutUrl = "<?= Url::to(['pos/checkout']) ?>";

function refreshCartView(cart) {
  // update table and totals (simple)
  // ...
}

onscan.attachTo(document, {
  suffixKeyCodes: [13], // enter
  reactToPaste: true,
  onScan: function(s){ // s is scanned barcode
     // call add via fetch
     fetch(addUrl, {
       method:'POST',
       headers:{'Content-Type':'application/json','X-CSRF-Token': '<?= Yii::$app->request->getCsrfToken() ?>'},
       body: JSON.stringify({sku:s})
     }).then(r=>r.json()).then(data=>{
        if(data.success) refreshCartView(data.cart);
     });
  }
});

// payment buttons
document.querySelectorAll('.pay-btn').forEach(btn=>{
  btn.addEventListener('click', () => {
    const method = btn.dataset.method;
    fetch(checkoutUrl, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token':'<?= Yii::$app->request->getCsrfToken() ?>'},
      body: 'payment_method=' + encodeURIComponent(method)
    }).then(r=>r.text()).then(html=>{
       // show printed receipt
       const w = window.open('','receipt');
       w.document.write(html);
       w.print(); // will open print dialog
    });
  });
});
</script>