<?php
namespace homer\user\models;

use Yii;
use trntv\filekit\behaviors\UploadBehavior;
use dektrium\user\models\Profile as BaseProfile;
use mdm\admin\models\Assignment;
use yii\helpers\ArrayHelper;

class Profile extends BaseProfile
{
    public $avatar;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['avatar','avatar_path','avatar_base_url'], 'safe'];
        $rules[] =[['pharmacy_id'], 'integer'];
        return $rules;
    }

    public function behaviors()
    {
        return [
            'avatar-profile' => [
                'class' => UploadBehavior::className(),
                'attribute' => 'avatar',
                'pathAttribute' => 'avatar_path',
                'baseUrlAttribute' => 'avatar_base_url'
            ]
        ];
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['avatar'] = 'รูปประจำตัว';
        $labels['pharmacy_id'] = 'ร้านขายยา';
        return $labels;
    }

    public function getAvatar($default = '/img/admin.png')
    {
        $cache = Yii::$app->cache;
        $key = 'avatar'.Yii::$app->user->id;
        $avatar = $cache->get($key);
        if($this->avatar_path && $avatar != false){
            return $avatar;
        }else{
            $path = $this->avatar_base_url . '/' . $this->avatar_path;
            if($this->avatar_path){
                $cache->set($key, $path, 60 * 60 * 1);
            }
            return $this->avatar_path
            ? Yii::getAlias($path)
            : $default;
        }
    }

    public function getPermissions(){
        $model = new Assignment($this->user_id);
        if($model){
            $assign = ArrayHelper::getValue($model->getItems(),'assigned',[]);
            $keys =  array_keys($assign);
            return is_array($keys) ? implode(",",$keys) : '';
        }else{
            return '';
        }
    }
}