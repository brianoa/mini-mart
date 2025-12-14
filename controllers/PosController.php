<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Products;
use app\models\Sales;
use app\models\SaleItems;
use app\components\MpesaService;


class PosController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add'      => ['POST'],
                    'update'   => ['POST'],
                    'remove'   => ['POST'],
                    'clear'    => ['POST'],
                    'checkout' => ['POST'],
                ],
            ],
        ];
    }

    /** POS page */
    public function actionIndex()
    {
        $cart = Yii::$app->session->get('cart', []);
        return $this->render('index', ['cart' => $cart]);
    }

    /** Lookup product by SKU */
    public function actionLookup($sku = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$sku) {
            return ['success' => false, 'message' => 'No SKU provided'];
        }

        $product = Products::find()->where(['sku' => $sku])->one();

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        return [
            'success' => true,
            'product' => [
                'id'    => $product->id,
                'sku'   => $product->sku,
                'name'  => $product->name,
                'price' => (float)$product->selling_price,
                'tax_rate' => (float)$product->tax_rate,
                'stock' => (int)$product->balance_qty_instock,
            ],
        ];
    }

    /** Add item to cart (with stock check) */
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sku = Yii::$app->request->post('sku');
        if (!$sku) {
            return ['success' => false, 'message' => 'No SKU provided'];
        }

        $product = Products::find()->where(['sku' => $sku])->one();
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        $session = Yii::$app->session;
        $cart = $session->get('cart', []);
        $pid = $product->id;

        // calculate new quantity if we add one more of this product
        $newQty = 1;
        if (isset($cart[$pid])) {
            $newQty += $cart[$pid]['qty'];
        }

        // stock validation: do NOT allow adding beyond available stock
        if ($newQty > $product->balance_qty_instock) {
            $available = $product->balance_qty_instock;
            return [
                'success' => false,
                'message' => "Stock insufficient! Only {$available} in stock",
            ];
        }

        // normal add/update in cart
        if (isset($cart[$pid])) {
            $cart[$pid]['qty'] += 1;
            $cart[$pid]['total'] = round($cart[$pid]['qty'] * $cart[$pid]['unit_price'], 2);
        } else {
            $cart[$pid] = [
                'product_id' => $pid,
                'sku'        => $product->sku,
                'name'       => $product->name,
                'unit_price' => (float)$product->selling_price,
                'qty'        => 1,
                'total'      => (float)$product->selling_price,
                'tax_rate'   => (float)$product->tax_rate,
            ];
        }

        $session->set('cart', $cart);

        return [
            'success' => true,
            'cart'    => array_values($cart),
        ];
    }

    /** Get cart */
    public function actionCart()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => true,
            'cart'    => array_values(Yii::$app->session->get('cart', [])),
        ];
    }

    /** Update item quantity (with stock check) */
    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pid = (int)Yii::$app->request->post('product_id');
        $qty = (int)Yii::$app->request->post('qty');

        if (!$pid) {
            return ['success' => false, 'message' => 'product_id required'];
        }

        $session = Yii::$app->session;
        $cart = $session->get('cart', []);

        if (!isset($cart[$pid])) {
            return ['success' => false, 'message' => 'Item not in cart'];
        }

        if ($qty <= 0) {
            unset($cart[$pid]);
        } else {
            // stock validation when user manually changes qty
            $product = Products::findOne($pid);
            if (!$product) {
                return ['success' => false, 'message' => 'Product not found'];
            }
            if ($qty > $product->balance_qty_instock) {
                return [
                    'success' => false,
                    'message' => "Stock insufficient! Only {$product->balance_qty_instock} in stock",
                ];
            }

            $cart[$pid]['qty'] = $qty;
            $cart[$pid]['total'] = round($qty * $cart[$pid]['unit_price'], 2);
        }

        $session->set('cart', $cart);

        return [
            'success' => true,
            'cart'    => array_values($cart),
        ];
    }

    /** Remove item */
    public function actionRemove()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pid = (int)Yii::$app->request->post('product_id');
        if (!$pid) {
            return ['success' => false, 'message' => 'product_id required'];
        }

        $session = Yii::$app->session;
        $cart = $session->get('cart', []);

        if (isset($cart[$pid])) {
            unset($cart[$pid]);
        }

        $session->set('cart', $cart);

        return [
            'success' => true,
            'cart'    => array_values($cart),
        ];
    }

    /** Clear cart */
    public function actionClear()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        Yii::$app->session->remove('cart');

        return ['success' => true];
    }

    /** Barcode scan (simple lookup) */
    public function actionScan($barcode = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$barcode) {
            return ['success' => false, 'message' => 'No barcode provided'];
        }

        $product = Products::find()->where(['sku' => $barcode])->one();
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        return [
            'success' => true,
            'item' => [
                'id'         => $product->id,
                'name'       => $product->name,
                'qty'        => 1,
                'unit_price' => (float)$product->selling_price,
                'total'      => (float)$product->selling_price,
                'tax_rate'   => (float)$product->tax_rate,
            ],
        ];
    }

    /** Checkout (final stock validation + save) */
    public function actionCheckout()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $session = Yii::$app->session;
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }

        // Validate ALL cart items have enough stock before saving sale
        $errors = [];
        foreach ($cart as $item) {
            $product = Products::findOne($item['product_id']);
            if (!$product) {
                $errors[] = "{$item['name']}: product record missing";
                continue;
            }
            if ($item['qty'] > $product->balance_qty_instock) {
                $errors[] = "{$item['name']}: Need {$item['qty']}, only {$product->balance_qty_instock} available";
            }
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Stock issues detected',
                'errors'  => $errors,
            ];
        }

        // Calculate totals
        $subtotal = 0;
        $tax = 0;
        foreach ($cart as $item) {
            $subtotal += $item['total'];
            $tax += ($item['total'] * ($item['tax_rate'] ?? 0) / 100);
        }
        $total_amount = round($subtotal + $tax, 2);

        // Save sale
        $sale = new Sales();
        $sale->client_name = Yii::$app->request->post('client_name', 'Customer');
        $sale->client_phone = Yii::$app->request->post('client_phone');
        $sale->payment_method = Yii::$app->request->post('payment_method', Sales::PAYMENT_METHOD_CASH);
        $sale->status = Sales::STATUS_PAID;
        $sale->total_amount = $total_amount;
        $sale->amount_paid = Yii::$app->request->post('amount_paid', $total_amount);

        if (!$sale->save()) {
            return [
                'success' => false,
                'message' => 'Failed to save sale',
                'errors'  => $sale->errors,
            ];
        }

        // Save sale items and update stock
        foreach ($cart as $item) {
            $si = new SaleItems();
            $si->sale_id = $sale->id;
            $si->product_id = $item['product_id'];
            $si->quantity = $item['qty'];
            $si->unit_price = $item['unit_price'];
            $si->subtotal = $item['total'];
            $si->save(false);

            $product = Products::findOne($item['product_id']);
            if ($product) {
                $product->sold_qty_instock += $item['qty'];
                $product->balance_qty_instock -= $item['qty'];
                $product->save(false);
            }
        }

        // Clear cart
        $session->remove('cart');

        return [
            'success'      => true,
            'sale_id'      => $sale->id,
            'total_amount' => $total_amount,
        ];
    }

    /** Mpesa STK init: used by Mpesa Confirm button */
    public function actionMpesaInit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $phoneRaw = Yii::$app->request->post('phone');
        if (!$phoneRaw) {
            return ['success' => false, 'message' => 'Phone number required'];
        }

        // Normalize Kenyan phone to 2547XXXXXXXX
        $phone = preg_replace('/\D+/', '', $phoneRaw);
        if (preg_match('/^07\d{8}$/', $phone)) {
            $phone = '254' . substr($phone, 1);
        } elseif (preg_match('/^\+2547\d{8}$/', $phone)) {
            $phone = substr($phone, 1);
        }
        if (!preg_match('/^2547\d{8}$/', $phone)) {
            return ['success' => false, 'message' => 'Invalid Kenyan phone (use 07.. or 2547..).'];
        }

        $session = Yii::$app->session;
        $cart    = $session->get('cart', []);
        if (empty($cart)) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }

        // Calculate total from cart
        $subtotal = 0;
        $tax      = 0;
        foreach ($cart as $item) {
            $subtotal += $item['total'];
            $tax      += ($item['total'] * ($item['tax_rate'] ?? 0) / 100);
        }
        $total_amount = round($subtotal + $tax, 2);
        if ($total_amount <= 0) {
            return ['success' => false, 'message' => 'Total amount must be greater than zero'];
        }

        // Simple account reference for now (you can later use sale id)
        $accountRef = 'POS-' . time();

        try {
            $mpesa  = new MpesaService();
            $result = $mpesa->stkPush($phone, $total_amount, $accountRef, 'POS Sale');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        if (!isset($result['ResponseCode']) || $result['ResponseCode'] !== '0') {
            $msg = $result['CustomerMessage'] ?? $result['ResponseDescription'] ?? 'STK request failed';
            return ['success' => false, 'message' => $msg, 'raw' => $result];
        }

        return [
            'success'      => true,
            'message'      => $result['CustomerMessage'] ?? 'STK sent. Ask customer to check phone.',
            'total_amount' => $total_amount,
            'response'     => $result,
        ];
    }
}
