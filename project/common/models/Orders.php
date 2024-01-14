<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%orders}}".
 *
 * @property int $id
 * @property float $total_price
 * @property int $status
 * @property string $username
 * @property string $email
 * @property string|null $transaction_id
 * @property int|null $created_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property OrderAddress $orderAddress
 * @property OrderItems[] $orderItems
 */
class Orders extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_PAID = 1;
    const STATUS_FAILED = 2;
    const STATUS_COMPLETED = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['total_price', 'status', 'username', 'email'], 'required'],
            [['total_price'], 'number'],
            [['status', 'created_at', 'created_by'], 'integer'],
            [['username', 'email', 'transaction_id'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'total_price' => 'Total Price',
            'status' => 'Status',
            'username' => 'Username',
            'email' => 'Email',
            'transaction_id' => 'Transaction ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[OrderAddress]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\OrderAddressQuery
     */
    public function getOrderAddress()
    {
        return $this->hasOne(OrderAddress::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\OrderItemsQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItems::class, ['order_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\OrdersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\OrdersQuery(get_called_class());
    }

    public function saveAddress($postData)
    {
        $orderAddress = new OrderAddress();
        $orderAddress->order_id = $this->id;

        if($orderAddress->load($postData) && $orderAddress->save()) {
            return true;
        }
        throw new \Exception("Could not save order address: " . implode("<br>", $orderAddress->getFirstError($postData)));
    }

    public function saveOrderItems()
    {
        $user = Yii::$app->user;
        $cartItems = CartItems::getItemsForUser($user->id);

        foreach($cartItems as $cartItem) {
            $orderItem = new OrderItems();
            $orderItem->product_name = $cartItem['name'];
            $orderItem->product_id = $cartItem['id'];
            $orderItem->unit_price = $cartItem['price'];
            $orderItem->order_id = $this->id;
            $orderItem->quantity = $cartItem['quantity'];

            if(!$orderItem->save()) {
                throw new \Exception("Order item was not saved" . implode('<br>', $orderItem->getFirstErrors()));
            }
        }

        return true;
    }

    public function getItemsQuantity()
    {
        return $sum = CartItems::findBySql(
            "SELECT SUM(quantity) FROM order_items WHERE order_id = :orderId", ['orderId' => $this->id]
        )->scalar();
    }

    public function sendEmailToVendor()
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'order_completed_vendor-html', 'text' => 'order_completed_vendor-text'],
                ['order' => $this]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo(Yii::$app->params['vendorEmail'])
            ->setSubject('New order has been made at ' . Yii::$app->name)
            ->send();
    }

    public function sendEmailToCustomer()
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'order_completed_customer-html', 'text' => 'order_completed_customer-text'],
                ['order' => $this]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Your orders is confirmed at ' . Yii::$app->name)
            ->send();
    }

    public static function getStatusLabels()
    {
        return [
            self::STATUS_PAID => 'Paid',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_DRAFT => 'Draft'
        ];
    }
}
