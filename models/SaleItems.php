<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sale_items".
 *
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $subtotal
 *
 * @property Products $product
 * @property Sales $sale
 */
class SaleItems extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sale_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal'], 'required'],
            [['sale_id', 'product_id', 'quantity'], 'integer'],
            [['unit_price', 'subtotal'], 'number'],
            [['sale_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sales::class, 'targetAttribute' => ['sale_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
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
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'subtotal' => 'Subtotal',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Products::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[Sale]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSale()
    {
        return $this->hasOne(Sales::class, ['id' => 'sale_id']);
    }

}
