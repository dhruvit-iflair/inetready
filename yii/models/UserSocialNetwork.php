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
class UserSocialNetwork extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_social_networks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['social_network_id', 'user_id', 'url'], 'required'],
            [['social_network_id', 'user_id'], 'integer'],
            [['url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'social_network_id' => 'Social Network ID',
            'user_id' => 'User ID',
            'url' => 'Url',
        ];
    }
}
