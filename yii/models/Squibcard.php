<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "squibcard".
 *
 * @property integer $id
 * @property string $name
 * @property string $template
 * @property integer $status
 * @property string $created
 */
class Squibcard extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'squibcard';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'template', 'status', 'created'], 'required'],
            [['template'], 'string'],
            [['status'], 'integer'],
            [['created'], 'safe'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'template' => 'Template',
            'status' => 'Status',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return SquibcardQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SquibcardQuery(get_called_class());
    }
}
