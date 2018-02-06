<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_social_networks".
 *
 * @property integer $id
 * @property integer $social_network_id
 * @property integer $user_id
 * @property string $url
 */
class CampaignKeys extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'campaign_keys';
    }

}
