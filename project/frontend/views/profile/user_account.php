<?php
use yii\bootstrap5\ActiveForm;

/** @var \yii\web\View $this */
/** @var \common\models\User $user */

?>

<?php if (isset($success) && $success): ?>
    <div class="alert alert-success">
        Your account was successfully updated
    </div>
<?php endif ?>

<?php $form = ActiveForm::begin([
    'action' => ['/profile/update-account'],
    'options' => [
        'data-pjax' => 1
    ]
]); ?>

<?= $form->field($user, 'username')->textInput(['autofocus' => true]) ?>
<?= $form->field($user, 'email') ?>

<div class="row">
    <div class="col">
        <?= $form->field($user, 'password')->passwordInput() ?>
    </div>
    <div class="col">
        <?= $form->field($user, 'passwordConfirm')->passwordInput() ?>
    </div>
</div>

<div class="form-group">
<button class="btn btn-primary">Update</button>
</div>

<?php ActiveForm::end(); ?>