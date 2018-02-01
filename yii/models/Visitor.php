<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "visitors".
 *
 * @property integer $id
 * @property string $ip_address
 * @property string $lat
 * @property string $lng
 * @property string $last_visit
 */
class Visitor extends \yii\db\ActiveRecord
{
    public $visit;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'visitors';
    }

    public function getUsers() 
    {
        return $this->hasOne(User::className(),['id' => 'visitor_id']); 
    }
    
	public function getCampaign() 
    {
        return $this->hasOne(Campaign::className(),['id' => 'campaign_id']); 
    }
	
    public function getKeys() 
    {
        return $this->hasOne(CampaignKeys::className(),['id' => 'key_id']); 
    }
}
