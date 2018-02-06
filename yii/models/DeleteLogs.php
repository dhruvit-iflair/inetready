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
class DeleteLogs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'delete_log';
    }
	
	public function getCampaign()
    {
		return $this->hasOne(Campaign::ClassName(), ['id' => 'campaign_id']);
    }

}
