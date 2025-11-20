<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sale_item".
 *
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property int $qty
 * @property float $unit_price
 * @property float $total_price
 *
 * @property Product $product
 * @property Sale $sale
 */
class SaleItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sale_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qty'], 'default', 'value' => 1],
            [['sale_id', 'product_id', 'unit_price', 'total_price'], 'required'],
            [['sale_id', 'product_id', 'qty'], 'integer'],
            [['unit_price', 'total_price'], 'number'],
            [['sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sale::class, 'targetAttribute' => ['sale_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sale_id' => 'Sale ID',
            'product_id' => 'Product ID',
            'qty' => 'Qty',
            'unit_price' => 'Unit Price',
            'total_price' => 'Total Price',
        ];
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Reduce product stock when a new sale item is created
        if ($insert) {
            $product = $this->product;
            if ($product) {
                $product->quantity -= $this->qty;
                if ($product->quantity < 0) {
                    $product->quantity = 0;
                }
                $product->save(false);
            }
        }
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[Sale]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSale()
    {
        return $this->hasOne(Sale::class, ['id' => 'sale_id']);
    }

}
