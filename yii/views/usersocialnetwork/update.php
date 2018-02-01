<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\UserSocialNetwork */

$this->title = 'Update User Social Network: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'User Social Networks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-social-network-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
