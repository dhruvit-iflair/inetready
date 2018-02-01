<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Social Networks';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-social-network-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create User Social Network', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'social_network_id',
            'user_id',
            'url:url',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
