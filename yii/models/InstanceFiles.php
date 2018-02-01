<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
/**
 * This is the model class for table "instance_files".
 *
 * @property integer $file_id
 * @property integer $cinstence_id
 * @property string $orgnl_file_name
 * @property string $savd_file_name
 * @property string $file_type
 * @property string $created_date
 */
class InstanceFiles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'instance_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['created_date'], 'safe'],
            [['orgnl_file_name', 'savd_file_name'], 'string', 'max' => 50],
            [['file_type'], 'string', 'max' => 20],
            //[['orgnl_file_name'], 'file', 'skipOnEmpty' => false, /*'extensions' => 'png, jpg',*/ 'maxFiles' => 1000],    
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'File ID',
            'user_id' => 'Cinstence ID',
            'orgnl_file_name' => 'Orgnl File Name',
            'savd_file_name' => 'Savd File Name',
            'file_type' => 'File Type',
            'created_date' => 'Created Date',
        ];
    }
    
   /* public function upload($cinstence_id)
    {
        print_r($this->orgnl_file_name);
        if ($this->validate()) { 
            foreach ($this->orgnl_file_name as $file) {
                $timestamp      = time();
                $randno         = rand(1000,999999);
                $savd_file_name = $cinstence_id."_".$timestamp."_".$randno.".";
                 $uploadPath 	= 'uploads'.DIRECTORY_SEPARATOR.'instances'.DIRECTORY_SEPARATOR.$cinstence_id. DIRECTORY_SEPARATOR;
              
                 //echo $savd_file_name.$file->extension;
                $file->saveAs($uploadPath . $file->baseName . '.' . $savd_file_name.$file->extension);
            }
            return true;
        } else {
            return false;
        }
    }*/
}
