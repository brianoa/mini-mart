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
 * @property string|null $created_at
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
            [['tax_rate'], 'default', 'value' => 0.00],
            [['stock'], 'default', 'value' => 0],
            [['sku', 'name'], 'required'],
            [['price', 'tax_rate'], 'number'],
            [['stock'], 'integer'],
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
     public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s'); // current timestamp
        }
        return parent::beforeSave($insert);
    }

}
