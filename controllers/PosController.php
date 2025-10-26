<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\Product;
use app\models\Sale;
use app\models\SaleItem;
use yii\web\Response;

class PosController extends Controller
{
    public function actionIndex()
    {
        $cart = Yii::$app->session->get('cart', []);
        return $this->render('index', ['cart'=>$cart]);
    }

    public function actionLookup($sku)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $product = Product::find()->where(['sku'=>$sku])->one();
        if (!$product) return ['success'=>false,'message'=>'Product not found'];
        return ['success'=>true,'product'=>[
            'id'=>$product->id,
            'name'=>$product->name,
            'price'=>floatval($product->price),
            'tax_rate'=>floatval($product->tax_rate),
        ]];
    }

    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $sku = Yii::$app->request->post('sku');
        $product = Product::find()->where(['sku'=>$sku])->one();
        if (!$product) return ['success'=>false,'message'=>'Not found'];

        $session = Yii::$app->session;
        $cart = $session->get('cart', []);
        // key by product_id
        if (isset($cart[$product->id])) {
            $cart[$product->id]['qty'] += 1;
            $cart[$product->id]['total'] = $cart[$product->id]['qty'] * $cart[$product->id]['unit_price'];
        } else {
            $cart[$product->id] = [
               'product_id'=>$product->id,
               'sku'=>$product->sku,
               'name'=>$product->name,
               'unit_price'=>floatval($product->price),
               'qty'=>1,
               'total'=>floatval($product->price),
               'tax_rate'=>floatval($product->tax_rate),
            ];
        }
        $session->set('cart',$cart);
        return ['success'=>true,'cart'=>$cart];
    }

    public function actionCheckout()
    {
        $session = Yii::$app->session;
        $cart = $session->get('cart',[]);
        if (empty($cart)) { return $this->redirect(['index']); }

        // compute totals
        $subtotal = 0; $tax = 0;
        foreach ($cart as $it) {
            $subtotal += $it['total'];
            $tax += ($it['total'] * $it['tax_rate'] / 100.0);
        }
        $total = $subtotal + $tax;

        $sale = new Sale();
        $sale->cashier = Yii::$app->user->isGuest ? 'guest' : Yii::$app->user->identity->username;
        $sale->subtotal = $subtotal;
        $sale->tax = $tax;
        $sale->total = $total;
        $sale->payment_method = Yii::$app->request->post('payment_method','Cash');
        if ($sale->save()) {
            foreach ($cart as $it) {
                $si = new SaleItem();
                $si->sale_id = $sale->id;
                $si->product_id = $it['product_id'];
                $si->qty = $it['qty'];
                $si->unit_price = $it['unit_price'];
                $si->total_price = $it['total'];
                $si->save();
            }
            $session->remove('cart');
            return $this->renderPartial('receipt', ['sale'=>$sale]);
        }
        // fallback
        return $this->redirect(['index']);
    }
}