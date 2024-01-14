<?php
/**
 * @var \common\models\Orders $order
 * @var \common\models\OrderAddress $orderAddress
 * @var array $cartItems
 * @var int $productQuantity
 * @var float $totalPrice
 */

use yii\bootstrap5\ActiveForm;
?>

<?php $form = ActiveForm::begin([
    'action' => ['/cart/submit-payment'],
]); ?>
<div class="row">
    <div class="col">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($order, 'username')->textInput(['autofocus' => true]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($order, 'email')->textInput(['autofocus' => true]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Address Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($orderAddress, 'address') ?>
                        <?= $form->field($orderAddress, 'city') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($orderAddress, 'state') ?>
                        <?= $form->field($orderAddress, 'country') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($orderAddress, 'zipcode') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo \common\models\Product::formatImageUrl($item['image']) ?>" style="width:50px;" alt="<?php echo $item['name'] ?>">
                                </td>
                                <td><?php echo $item['name'] ?></td>
                                <td><?php echo $item['quantity'] ?></td>
                                <td><?php echo '<p>RM' . $item['total_price'] . '</p>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?= $form->field($order, 'cartItems')->hiddenInput(['value' => json_encode($cartItems)])->label(false) ?>
                    </tbody>
                </table>
                <hr>
                <table class="table">
                    <tr>
                        <td>Total Items</td>
                        <td class="text-right"><?php echo $productQuantity ?></td>
                    </tr>
                    <tr>
                        <td>Total Price</td>
                        <td><?php echo '<p>RM'. $totalPrice .'</p>'; ?></td>
                    </tr>
                </table>

                <p class="d-flex justify-content-end mt-3">
                    <button class="btn btn-secondary px-5">Pay Now</button>
                </p>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>