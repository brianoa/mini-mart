<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property float $buying_price
 * @property float $selling_price
 * @property float|null $tax_rate
 * @property int $initial_qty_instock
 * @property int|null $sold_qty_instock
 * @property int $balance_qty_instock
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property SaleItems[] $saleItems
 */
class Products extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tax_rate'], 'default', 'value' => 0.00],
            [['sold_qty_instock'], 'default', 'value' => 0],
            [['sku', 'name', 'selling_price','buying_price', 'initial_qty_instock', 'balance_qty_instock'], 'required'],
            [['selling_price', 'buying_price', 'tax_rate'], 'number'],
            [['initial_qty_instock', 'sold_qty_instock', 'balance_qty_instock'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['sku'], 'string', 'max' => 50],
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
            'buying_price' => 'Buying Price',
            'selling_price' => 'Selling Price',
            'tax_rate' => 'Tax Rate',
            'initial_qty_instock' => 'Initial Qty Instock',
            'sold_qty_instock' => 'Sold Qty Instock',
            'balance_qty_instock' => 'Balance Qty Instock',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[SaleItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSaleItems()
    {
        return $this->hasMany(SaleItems::class, ['product_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        $this->updated_at = date('Y-m-d H:i:s');
        return parent::beforeSave($insert);
    }


}
