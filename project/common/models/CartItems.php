<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%cart_items}}".
 *
 * @property int      $id
 * @property int      $product_id
 * @property int      $quantity
 * @property int|null $created_by
 *
 * @property User     $createdBy
 * @property Product  $product
 */
class CartItems extends \yii\db\ActiveRecord
{
    const SESSION_KEY = 'CART_ITEMS';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cart_items}}';
    }

    public static function getTotalQuantityForUser($currUserId)
    {
        if (Yii::$app->user->isGuest) {
            $cartItems = Yii::$app->session->get(CartItems::SESSION_KEY, []);
            $sum = 0;
            foreach ($cartItems as $cartItem) {
                $sum += $cartItem['quantity'];
            }
        } else {
            $sum = CartItems::findBySql(
                "SELECT SUM(quantity) FROM cart_items WHERE created_by = :userId", ['userId' => $currUserId]
            )->scalar();
        }

        return $sum;
    }

    public static function getTotalPriceForUser($currUserId)
    {
        if (Yii::$app->user->isGuest) {
            $cartItems = Yii::$app->session->get(CartItems::SESSION_KEY, []);
            $sum = 0;
            foreach ($cartItems as $cartItem) {
                $sum += $cartItem['quantity'] * $cartItem['price'];
            }
        } else {
            $sum = CartItems::findBySql(
                "SELECT SUM(c.quantity * p.price)
                    FROM cart_items c
                    LEFT JOIN product p on p.id = c.product_id
                WHERE c.created_by = :userId", ['userId' => $currUserId]
            )->scalar();
        }

        return $sum;
    }

    public static function getTotalPriceForItemForUser($productId, $currUserId)
    {
        if (Yii::$app->user->isGuest()) {
            $cartItems = Yii::$app->session->get(CartItems::SESSION_KEY, []);
            $sum = 0;
            foreach ($cartItems as $cartItem) {
                if ($cartItem['id'] == $productId) {
                    $sum += $cartItem['quantity'] * $cartItem['price'];
                }
            }
        } else {
            $sum = CartItems::findBySql(
                "SELECT SUM(c.quantity * p.price)
                    FROM cart_items c
                    LEFT JOIN products p on p.id = c.product_id
                WHERE c.product_id = :id AND c.created_by = :userId", ['id' => $productId, 'userId' => $currUserId]
            )->scalar();
        }

        return $sum;
    }

    public static function getItemsForUser($currUserId)
    {
        if (Yii::$app->user->isGuest) {
            $cartItems = Yii::$app->session->get(CartItems::SESSION_KEY, []);
        } else {
            $cartItems = CartItems::findBySql(
                "SELECT c.product_id as id, p.image, p.name, p.price, c.quantity, p.price * c.quantity as total_price FROM cart_items c LEFT JOIN product p on p.id = c.product_id WHERE c.created_by = :userId",
                ['userId' => $currUserId])
                ->asArray()
                ->all();
        }
        return $cartItems;
    }

    public static function clearCartItems($currUserId)
    {
        if (Yii::$app->user->isGuest()) {
            Yii::$app->session->remove(CartItems::SESSION_KEY);
        } else {
            CartItems::deleteAll(['created_by' => $currUserId]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'quantity'], 'required'],
            [['product_id', 'quantity', 'created_by'], 'integer'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
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
            'product_id' => 'Product ID',
            'quantity' => 'Quantity',
            'created_by' => 'Created By',
        ];
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\ProductQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\CartItemsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CartItemsQuery(get_called_class());
    }
}
