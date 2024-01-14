<?php
/** @var yii\web\View $this
 *  @var array $items
*/

?>

<div>
    <div class="card-header">
        <h3>Your cart items</h3>
    </div>
    <div class="card-body p-0">
        <?php if(!empty($items)): ?>
        <table class="table table-hover">
        <thead>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Unit Price(RM)</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
                <tr data-id="<?php echo $item['id'] ?>" data-url="<?php echo \yii\helpers\Url::to(['/cart/change-quantity']) ?>">
                    <td><?= $item['name'] ?></td>
                    <td>
                        <img src="<?= Yii::$app->params['frontendUrl'] . '/storage' . $item['image'] ?>" alt="<?= $item['name'] ?>" style="width: 50px">
                    </td>
                    <td><?= $item['price'] ?></td>
                    <td>
                        <input type="number" min="1" class="form-control item-quantity" style="width: 60px" value="<?= $item['quantity'] ?>">
                    </td>
                    <td><?= $item['total_price'] ?></td>
                    <td><?= \yii\helpers\Html::a('Delete', ['/cart/delete', 'id' => $item['id']], [
                        'class' => 'btn btn-outline-danger btn-sm',
                        'data-method' => 'post',
                        'data-confirm' => 'Are you sure you want to remove this product from cart?'
                    ]) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
        <div class="card-body d-flex justify-content-end">
            <a href="<?= \yii\helpers\Url::to(['/cart/checkout']) ?>" class="btn btn-primary px-5">Checkout</a>
        </div>
        <?php else: ?>
        <p class="text-muted text-center p-5">There are no items in the cart</p>
        <?php endif; ?>
    </div>
</div>