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
class Campaign extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'campaign';
    }

    public function getKeys()
    {
        return $this->hasMany(CampaignKeys::className(),['campaign_id' => 'id']); 
    }
    
    public function getVisits()
    {
        return $this->hasMany(Visitor::className(),['campaign_id' => 'id']); 
    }
	
	public function getUser() 
    {
        return $this->hasOne(User::ClassName(), ['id' => 'user_id']);
    }
}
