<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property float $price
 * @property float|null $tax_rate
 * @property int|null $stock
 * @property float $buying_price
 * @property float $selling_price
 * @property int $quantity
 * @property string $created_at
 *
 * @property SaleItem[] $saleItems
 */
class Product extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['selling_price'], 'default', 'value' => 0.00],
            [['quantity'], 'default', 'value' => 0],
            [['sku', 'name', 'created_at'], 'required'],
            [['price', 'tax_rate', 'buying_price', 'selling_price'], 'number'],
            [['stock', 'quantity'], 'integer'],
            [['created_at'], 'safe'],
            [['sku'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['sku'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'name' => 'Name',
            'price' => 'Price',
            'tax_rate' => 'Tax Rate',
            'stock' => 'Stock',
            'buying_price' => 'Buying Price',
            'selling_price' => 'Selling Price',
            'quantity' => 'Quantity',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[SaleItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSaleItems()
    {
        return $this->hasMany(SaleItem::class, ['product_id' => 'id']);
    }

}
