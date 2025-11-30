<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sales".
 *
 * @property int $id
 * @property string $client_name
 * @property string|null $client_phone
 * @property string|null $status
 * @property string|null $payment_method
 * @property float $total_amount
 * @property float $amount_paid
 * @property string|null $created_at
 *
 * @property SaleItems[] $saleItems
 */
class Sales extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_PAID = 'PAID';
    const STATUS_NOT_PAID = 'NOT PAID';
    const STATUS_CANCELLED = 'CANCELLED';
    const PAYMENT_METHOD_CASH = 'CASH';
    const PAYMENT_METHOD_MPESA = 'MPESA';
    const PAYMENT_METHOD_CARD = 'CARD';
    const PAYMENT_METHOD_OTHER = 'OTHER';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sales';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => 'PAID'],
            [['payment_method'], 'default', 'value' => 'CASH'],
            [['client_name', 'total_amount','amount_paid'], 'required'],
            [['status', 'payment_method'], 'string'],
            [['total_amount','amount_paid'], 'number'],
            [['created_at'], 'safe'],
            [['client_name'], 'string', 'max' => 255],
            [['client_phone'], 'string', 'max' => 20],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            ['payment_method', 'in', 'range' => array_keys(self::optsPaymentMethod())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_name' => 'Client Name',
            'client_phone' => 'Client Phone',
            'status' => 'Status',
            'payment_method' => 'Payment Method',
            'total_amount' => 'Total Amount',
            'amount_paid'  => 'Amount Paid',
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
        return $this->hasMany(SaleItems::class, ['sale_id' => 'id']);
    }


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_PAID => 'PAID',
            self::STATUS_NOT_PAID => 'NOT PAID',
            self::STATUS_CANCELLED => 'CANCELLED',
        ];
    }

    /**
     * column payment_method ENUM value labels
     * @return string[]
     */
    public static function optsPaymentMethod()
    {
        return [
            self::PAYMENT_METHOD_CASH => 'CASH',
            self::PAYMENT_METHOD_MPESA => 'MPESA',
            self::PAYMENT_METHOD_CARD => 'CARD',
            self::PAYMENT_METHOD_OTHER => 'OTHER',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function setStatusToPaid()
    {
        $this->status = self::STATUS_PAID;
    }

    /**
     * @return bool
     */
    public function isStatusNotPaid()
    {
        return $this->status === self::STATUS_NOT_PAID;
    }

    public function setStatusToNotPaid()
    {
        $this->status = self::STATUS_NOT_PAID;
    }

    /**
     * @return bool
     */
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }

    /**
     * @return string
     */
    public function displayPaymentMethod()
    {
        return self::optsPaymentMethod()[$this->payment_method];
    }

    /**
     * @return bool
     */
    public function isPaymentMethodCash()
    {
        return $this->payment_method === self::PAYMENT_METHOD_CASH;
    }

    public function setPaymentMethodToCash()
    {
        $this->payment_method = self::PAYMENT_METHOD_CASH;
    }

    /**
     * @return bool
     */
    public function isPaymentMethodMpesa()
    {
        return $this->payment_method === self::PAYMENT_METHOD_MPESA;
    }

    public function setPaymentMethodToMpesa()
    {
        $this->payment_method = self::PAYMENT_METHOD_MPESA;
    }

    /**
     * @return bool
     */
    public function isPaymentMethodCard()
    {
        return $this->payment_method === self::PAYMENT_METHOD_CARD;
    }

    public function setPaymentMethodToCard()
    {
        $this->payment_method = self::PAYMENT_METHOD_CARD;
    }

    /**
     * @return bool
     */
    public function isPaymentMethodOther()
    {
        return $this->payment_method === self::PAYMENT_METHOD_OTHER;
    }

    public function setPaymentMethodToOther()
    {
        $this->payment_method = self::PAYMENT_METHOD_OTHER;
    }
    
}
