<?php

/** @var yii\web\View $this */
/** @var \yii\data\ActiveDataProvider $dataProvider */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <div class="body-content">
        <?php echo \yii\widgets\ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_product_item',
            'layout' => '{summary}<div class="row">{items}</div>{pager}',
            'options' => [
                'class' => 'w-75'
            ],
            'itemOptions' => [
                'class' => 'col-lg-4 col-md-6 mb-5 product-item',
            ],
            'pager' => [
                'class' => \yii\bootstrap5\LinkPager::class,
            ],
        ]) ?>
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
        </div>
    </div>
</div>