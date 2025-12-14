<?php

namespace app\controllers;

use app\models\Products;
use app\models\ProductsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii;

/**
 * ProductsController implements the CRUD actions for Products model.
 */
class ProductsController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Products models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProductsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Products model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Products model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Products();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Products model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Products model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Products model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Products the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Products::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionLowStock()
    {
        $lowStockItems = Products::find()
            ->where(['<', 'balance_qty_instock', 10])
            ->all();

        return $this->render('low-stock', [
            'lowStockItems' => $lowStockItems,
        ]);
    }
    /**
     * Restocks an existing product by ID or SKU.
     * Supports pre-filling product name from low-stock view links.
     * @param int|null $id Product ID (optional)
     * @param string|null $sku Product SKU (optional)
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if product not found
     */
    public function actionRestock($id = null, $sku = null)
    {
        $model = new Products(); // Used only for form binding and validation

        // Pre-fill product if ID or SKU provided (from low-stock links)
        if ($id !== null) {
            $model = $this->findModel($id);
        } elseif ($sku !== null) {
            $model = Products::findOne(['sku' => $sku]);
            if ($model === null) {
                throw new NotFoundHttpException('Product not found.');
            }
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            // Reload the actual product to update (in case form was pre-filled)
            $productToUpdate = ($id !== null) ? $this->findModel($id) : Products::findOne(['sku' => $model->sku]);
            
            if ($productToUpdate === null) {
                throw new NotFoundHttpException('Product not found.');
            }

            // Get restock quantity and new prices from form
            $restockQty = $model->initial_qty_instock; // Reuse initial_qty field for restock amount
            $newBuyingPrice = $model->buying_price;
            $newSellingPrice = $model->selling_price;

            // Update stock and prices
            $productToUpdate->balance_qty_instock += $restockQty;
            $productToUpdate->buying_price = $newBuyingPrice;
            $productToUpdate->selling_price = $newSellingPrice;

            if ($productToUpdate->save(false)) {
                // Log the restock (create ProductRestockLog if you have one, or skip)
                /*
                $log = new ProductRestockLog();
                $log->product_id = $productToUpdate->id;
                $log->quantity_restocked = $restockQty;
                $log->restock_price = $productToUpdate->buying_price ?? 0;
                $log->restocked_at = date('Y-m-d H:i:s');
                $log->save(false);
                */

                Yii::$app->session->setFlash('success', 'Product restocked successfully.');
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to restock product.');
            }
        }

        return $this->render('restock', [
            'model' => $model,
        ]);
    }
    
}
