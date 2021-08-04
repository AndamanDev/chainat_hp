<?php

namespace frontend\modules\app\models;

use Yii;

/**
 * This is the model class for table "tb_token_nhso".
 *
 * @property int $smctoken_id
 * @property string $smctoken รหัส Token ที่ได้จากการ Authentication ด้วยบัตรประจาตัวประชาชน
 * @property int $user_person_id เลขประจาตัวประชาชนผู้ใช้งานระบบ
 * @property int $createdby
 * @property string $crearedat
 */
class TbTokenNhso extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_token_nhso';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_person_id', 'smctoken'], 'required'],
            [['user_person_id', 'createdby'], 'integer'],
            [['crearedat'], 'safe'],
            [['smctoken'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'smctoken_id' => 'Smctoken ID',
            'smctoken' => 'รหัส Token ที่ได้จากการ Authentication ด้วยบัตรประจาตัวประชาชน',
            'user_person_id' => 'เลขประจาตัวประชาชนผู้ใช้งานระบบ',
            'createdby' => 'Createdby',
            'crearedat' => 'Crearedat',
        ];
    }
}
