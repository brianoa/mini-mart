<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sale".
 *
 * @property int $id
 * @property string|null $cashier
 * @property float $subtotal
 * @property float $tax
 * @property float $total
 * @property string|null $payment_method
 * @property string|null $created_at
 *
 * @property SaleItem[] $saleItems
 */
class Sale extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sale';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cashier', 'payment_method'], 'default', 'value' => null],
            [['subtotal', 'tax', 'total'], 'required'],
            [['subtotal', 'tax', 'total'], 'number'],
            [['created_at'], 'safe'],
            [['cashier'], 'string', 'max' => 100],
            [['payment_method'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cashier' => 'Cashier',
            'subtotal' => 'Subtotal',
            'tax' => 'Tax',
            'total' => 'Total',
            'payment_method' => 'Payment Method',
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
        return $this->hasMany(SaleItem::class, ['sale_id' => 'id']);
    }

}
