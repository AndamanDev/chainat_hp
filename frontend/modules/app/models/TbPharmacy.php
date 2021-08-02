<?php

namespace frontend\modules\app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "tb_pharmacy".
 *
 * @property int $pharmacy_id
 * @property string $pharmacy_name ชื่อร้านขายยา
 * @property string $create_at วันที่สร้าง
 * @property string $update_at วันที่บันทึก
 * @property string $pharmacy_address
 */
class TbPharmacy extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_pharmacy';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pharmacy_id', 'pharmacy_name'], 'required'],
            [['create_at', 'update_at'], 'safe'],
            [['pharmacy_name', 'pharmacy_address'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'pharmacy_id' => 'Pharmacy ID',
            'pharmacy_name' => 'ชื่อร้านขายยา',
            'create_at' => 'วันที่สร้าง',
            'update_at' => 'วันที่บันทึก',
            'pharmacy_address' => 'Pharmacy Address',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['create_at', 'update_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['update_at'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => Yii::$app->formatter->asDate('now', 'php:Y-m-d H:i:s')
            ],
        ];
    }
}
