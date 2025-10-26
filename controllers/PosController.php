<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\Product;
use app\models\Sale;
use app\models\SaleItem;
use yii\filters\VerbFilter;

class PosController extends Controller
{
    public $enableCsrfValidation=false;
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST'],
                    'update' => ['POST'],
                    'remove' => ['POST'],
                    'clear' => ['POST'],
                    'checkout' => ['POST'],
                ],
            ],
        ];
    }

    // 1. POS page (view will be built in next segment)
    public function actionIndex()
    {
        $cart = Yii::$app->session->get('cart', []);
        return $this->render('index', ['cart' => $cart]);
    }

    // 2. Lookup by SKU (GET)
    public function actionLookup($sku = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$sku) return ['success' => false, 'message' => 'No SKU provided'];

        $product = Product::find()->where(['sku' => $sku])->one();
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        return [
            'success' => true,
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => (float)$product->price,
                'tax_rate' => (float)$product->tax_rate,
                'stock' => (int)$product->stock,
            ]
        ];
    }

    // 3. Add item to cart (POST: sku)
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $sku = Yii::$app->request->post('sku');
        if (!$sku) return ['success' => false, 'message' => 'No SKU provided'];

        $product = Product::find()->where(['sku' => $sku])->one();
        if (!$product) return ['success' => false, 'message' => 'Product not found'];

        $session = Yii::$app->session;
        $cart = $session->get('cart', []);

        $pid = $product->id;
        if (isset($cart[$pid])) {
            $cart[$pid]['qty'] += 1;
            $cart[$pid]['total'] = round($cart[$pid]['qty'] * $cart[$pid]['unit_price'], 2);
        } else {
            $cart[$pid] = [
                'product_id' => $pid,
                'sku' => $product->sku,
                'name' => $product->name,
                'unit_price' => (float)$product->price,
                'qty' => 1,
                'total' => (float)$product->price,
                'tax_rate' => (float)$product->tax_rate,
            ];
        }

        $session->set('cart', $cart);
        return ['success' => true, 'cart' => array_values($cart)];
    }

    // 4. Get cart (GET)
    public function actionCart()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cart = Yii::$app->session->get('cart', []);
        return ['success' => true, 'cart' => array_values($cart)];
    }

    // 5. Update qty (POST: product_id, qty)
    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $pid = (int)Yii::$app->request->post('product_id');
        $qty = (int)Yii::$app->request->post('qty');
        if (!$pid) return ['success' => false, 'message' => 'product_id required'];

        $session = Yii::$app->session;
        $cart = $session->get('cart', []);
        if (!isset($cart[$pid])) return ['success' => false, 'message' => 'Item not in cart'];

        if ($qty <= 0) {
            unset($cart[$pid]);
        } else {
            $cart[$pid]['qty'] = $qty;
            $cart[$pid]['total'] = round($qty * $cart[$pid]['unit_price'], 2);
        }
        $session->set('cart', $cart);
        return ['success' => true, 'cart' => array_values($cart)];
    }

    // 6. Remove (POST: product_id)
    public function actionRemove()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $pid = (int)Yii::$app->request->post('product_id');
        if (!$pid) return ['success' => false, 'message' => 'product_id required'];
        $session = Yii::$app->session;
        $cart = $session->get('cart', []);
        if (isset($cart[$pid])) unset($cart[$pid]);
        $session->set('cart', $cart);
        return ['success' => true, 'cart' => array_values($cart)];
    }

    // 7. Clear cart
    public function actionClear()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->session->remove('cart');
        return ['success' => true];
    }


    public function actionScan($barcode = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$barcode) {
            return ['success' => false, 'message' => 'No barcode provided'];
        }

        // Assuming you have a Product model
        $product = \app\models\Product::find()->where(['sku' => $barcode])->one();

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        $item = [
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'unit_price' => (float)$product->price,
            'total' => (float)$product->price,
            'tax_rate' => (float)$product->tax_rate,
        ];

        return ['success' => true, 'item' => $item];
    }  

    // 8. Checkout (POST: payment_method)
    public function actionCheckout()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $session = Yii::$app->session;
        $cart = $session->get('cart', []);
        if (empty($cart)) return ['success' => false, 'message' => 'Cart is empty'];

        // compute totals server-side
        $subtotal = 0; $tax = 0;
        foreach ($cart as $it) {
            $subtotal += $it['total'];
            $tax += ($it['total'] * ($it['tax_rate'] ?? 0) / 100.0);
        }
        $total = round($subtotal + $tax, 2);

        $sale = new Sale();
        $sale->cashier = Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->username;
        $sale->subtotal = $subtotal;
        $sale->tax = $tax;
        $sale->total = $total;
        $sale->payment_method = Yii::$app->request->post('payment_method', 'Cash');

        if (!$sale->save()) {
            return ['success' => false, 'message' => 'Failed saving sale', 'errors' => $sale->errors];
        }

        foreach ($cart as $it) {
            $si = new SaleItem();
            $si->sale_id = $sale->id;
            $si->product_id = $it['product_id'];
            $si->qty = $it['qty'];
            $si->unit_price = $it['unit_price'];
            $si->total_price = $it['total'];
            $si->save(false);
        }

        // clear session
        $session->remove('cart');

        // return sale id & summary (UI will request receipt render if needed)
        return ['success' => true, 'sale_id' => $sale->id, 'subtotal'=>$subtotal,'tax'=>$tax,'total'=>$total];
    }
}