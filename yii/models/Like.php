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
class Like extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'like';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }
    
}
