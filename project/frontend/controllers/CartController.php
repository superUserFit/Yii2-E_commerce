<?php
/**
 * User: TheCodeholic
 * Date: 12/12/2020
 * Time: 3:32 PM
 */

namespace frontend\controllers;

use common\models\CartItems;
use common\models\OrderItems;
use common\models\Orders;
use common\models\OrderAddress;
use common\models\Product;
use Yii;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class CartController
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package frontend\controllers
 */
class CartController extends \frontend\base\Controller
{
    public function behaviors()
    {
        return [
            [
                'class' => ContentNegotiator::class,
                'only' => ['add', 'create-order', 'submit-payment', 'change-quantity'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST', 'DELETE'],
                    'create-order' => ['POST'],
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $user = Yii::$app->user;
        $cartItems = CartItems::getItemsForUser($user->id);

        return $this->render('index', [
            'items' => $cartItems
        ]);
    }

    public function actionAdd()
    {
        $id = Yii::$app->request->post('id');
        $product = Product::find()->id($id)->published()->one();
        if (!$product) {
            throw new NotFoundHttpException("Product does not exist");
        }

        if (Yii::$app->user->isGuest) {

            $cartItems = Yii::$app->session->get(CartItems::SESSION_KEY, []);
            $found = false;
            foreach ($cartItems as &$item) {
                if ($item['id'] == $id) {
                    $item['quantity']++;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $cartItem = [
                    'id' => $id,
                    'name' => $product->name,
                    'image' => $product->image,
                    'price' => $product->price,
                    'quantity' => 1,
                    'total_price' => $product->price
                ];
                $cartItems[] = $cartItem;
            }

            Yii::$app->session->set(CartItems::SESSION_KEY, $cartItems);
        } else {
            $userId = Yii::$app->user->id;
            $cartItem = CartItems::find()->userId($userId)->productId($id)->one();
            if ($cartItem) {
                $cartItem->quantity++;
            } else {
                $cartItem = new CartItems();
                $cartItem->product_id = $id;
                $cartItem->created_by = $userId;
                $cartItem->quantity = 1;
            }
            if ($cartItem->save()) {
                return [
                    'success' => true
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => $cartItem->errors
                ];
            }
        }
    }

    public function actionDelete($id)
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            $cartItems = Yii::$app->session->get(CartItems::SESSION_KEY, []);
            foreach ($cartItems as $i => $cartItem) {
                if ($cartItem['id'] == $id) {
                    array_splice($cartItems, $i, 1);
                    break;
                }
            }
            Yii::$app->session->set(CartItems::SESSION_KEY, $cartItems);
        } else {
            CartItems::deleteAll(['product_id' => $id, 'created_by' => $user->id]);
        }

        return $this->redirect(['index']);
    }

    public function actionChangeQuantity()
    {
        $user = Yii::$app->user;
        $id = Yii::$app->request->post('id');
        $product = Product::find()->id($id)->published()->one();
        if (!$product) {
            throw new NotFoundHttpException("Product does not exist");
        }
        $quantity = Yii::$app->request->post('quantity');
        if ($user->isGuest) {
            $cartItems = Yii::$app->session->get(CartItems::SESSION_KEY, []);
            foreach ($cartItems as &$cartItem) {
                if ($cartItem['id'] === $id) {
                    $cartItem['quantity'] = $quantity;
                    break;
                }
            }
            Yii::$app->session->set(CartItems::SESSION_KEY, $cartItems);
        } else {
            $cartItem = CartItems::find()->userId($user->id)->productId($id)->one();
            if ($cartItem) {
                $cartItem->quantity = $quantity;
                $cartItem->save();
            }
        }

        return [
            'quantity' => CartItems::getTotalQuantityForUser($user->id),
            'price' => Yii::$app->formatter->asCurrency(CartItems::getTotalPriceForItemForUser($id, $user->id))
        ];
    }

    public function actionCheckout()
    {
        $user = Yii::$app->user;
        $cartItems = CartItems::getItemsForUser($user->id);
        $productQuantity = CartItems::getTotalQuantityForUser($user->id);
        $totalPrice = CartItems::getTotalPriceForUser($user->id);

        if (empty($cartItems)) {
            return $this->redirect([Yii::$app->homeUrl]);
        }
        $order = new Orders();

        $order->total_price = $totalPrice;
        $order->status = Orders::STATUS_DRAFT;
        $order->created_at = time();
        $order->created_by = $user->id;
        $transaction = Yii::$app->db->beginTransaction();

        if ($order->load(Yii::$app->request->post())
            && $order->save()
            && $order->saveAddress(Yii::$app->request->post())
            && $order->saveOrderItems()) {
            $transaction->commit();

            CartItems::clearCartItems($user->id);

            return $this->render('pay-now', [
                'order' => $order,
            ]);
        }

        $orderAddress = new OrderAddress();
        if (!$user->isGuest) {
            /** @var \common\models\User $user */
            $user = Yii::$app->user->identity;
            $userAddress = $user->getAddress();

            $order->email = $user->email;
            $order->status = Orders::STATUS_DRAFT;

            $orderAddress->address = $userAddress->address;
            $orderAddress->city = $userAddress->city;
            $orderAddress->state = $userAddress->state;
            $orderAddress->country = $userAddress->country;
            $orderAddress->zipcode = $userAddress->zipcode;
        }

        return $this->render('checkout', [
            'order' => $order,
            'orderAddress' => $orderAddress,
            'cartItems' => $cartItems,
            'productQuantity' => $productQuantity,
            'totalPrice' => $totalPrice
        ]);
    }

    public function actionSubmitPayment()
    {
        $user = Yii::$app->user->identity;
        $orders = new Orders();
        $orderItems = new OrderItems();
        $orderAddress = new OrderAddress();

        $orders->total_price = CartItems::getTotalPriceForUser($user->id);
        $orders->status = Orders::STATUS_PAID;
        $orders->transaction_id = bin2hex(random_bytes(4));
        $orders->username = Yii::$app->request->post('Orders')['username'];
        $orders->email = Yii::$app->request->post('Orders')['email'];

        $orders->save();

        $orderAddress->order_id = $orders->id;
        $orderAddress->address = Yii::$app->request->post('OrderAddress')['address'];
        $orderAddress->city = Yii::$app->request->post('OrderAddress')['city'];
        $orderAddress->state = Yii::$app->request->post('OrderAddress')['state'];
        $orderAddress->country = Yii::$app->request->post('OrderAddress')['country'];
        $orderAddress->zipcode = Yii::$app->request->post('OrderAddress')['zipcode'];

        $orderAddress->save();

        $cartItems = json_decode(Yii::$app->request->post('Orders')['cartItems']);
        if(is_array($cartItems)) {
            foreach($cartItems as $item) {
                $orderItems->product_id = $item->id;
                $orderItems->product_name = $item->name;
                $orderItems->unit_price = $item->price;
                $orderItems->quantity = $item->quantity;
            }
        }
        $orderItems->order_id = $orders->id;

        if($orderItems->save()) {
            $this->goHome();
        }
    }
}