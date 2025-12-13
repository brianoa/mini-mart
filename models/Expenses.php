<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "expenses".
 *
 * @property int $id
 * @property string $expense_type
 * @property float $amount
 * @property string $created_at
 */
class Expenses extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'expenses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['expense_type', 'amount'], 'required'],
            [['amount'], 'number'],
            [['created_at'], 'safe'],
            [['expense_type'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'expense_type' => 'Expense Type',
            'amount' => 'Amount',
            'created_at' => 'Created At',
        ];
    }

}
