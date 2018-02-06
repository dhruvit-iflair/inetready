<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "message".
 *
 * @property integer $id
 * @property integer $to_user_id
 * @property string $sender_name
 * @property string $sender_email
 * @property string $message
 * @property string $sent_time
 */
class Share extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'share';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to_user_id', 'sender_name', 'sender_email', 'message', 'sent_time'], 'required'],
            [['to_user_id'], 'integer'],
            [['message'], 'string'],
            [['sent_time'], 'safe'],
            [['sender_name', 'sender_email'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to_user_id' => 'To User ID',
            'sender_name' => 'Sender Name',
            'sender_email' => 'Sender Email',
            'message' => 'Message',
            'sent_time' => 'Sent Time',
        ];
    }
}
