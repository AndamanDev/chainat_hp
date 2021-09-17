<?php

namespace frontend\modules\app\models;

use Yii;
use yii\validators\RequiredValidator;

/**
 * This is the model class for table "tb_service".
 *
 * @property int $serviceid เลขที่บริการ
 * @property string $service_name ชื่อบริการ
 * @property int $service_groupid เลขที่กลุ่มบริการ
 * @property string $service_route ลำดับการบริการ
 * @property int $prn_profileid แบบการพิมพ์บัตรคิว
 * @property int $prn_copyqty จำนวนพิมพ์ต่อครั้ง
 * @property string $service_prefix ตัวอักษร/ตัวเลข นำหน้าคิว
 * @property int $service_numdigit จำนวนหลักหมายเลขคิว
 * @property string $service_status สถานะคิว
 * @property int $service_md_name_id ชื่อแพทย์
 *
 * @property TbServicegroup $serviceGroup
 */
class TbService extends \yii\db\ActiveRecord
{
    public $schedules;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tb_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_name', 'prn_profileid', 'prn_copyqty', 'service_prefix', 'service_numdigit', 'service_status'], 'required'],
            [['service_groupid', 'prn_profileid', 'prn_copyqty', 'service_numdigit', 'service_md_name_id', 'print_by_hn', 'quickly', 'show_on_kiosk', 'show_on_mobile', 'prn_profileid_quickly','service_type_id'], 'integer'],
            [['service_name', 'btn_kiosk_name'], 'string', 'max' => 100],
            [['service_route'], 'string', 'max' => 11],
            [['service_prefix'], 'string', 'max' => 2],
            [['service_status'], 'string', 'max' => 10],
            [['main_dep'], 'string', 'max' => 50],
            // [['schedules'], 'safe'],
            [['schedules'], 'validateSchedules'],
            [['service_groupid'], 'exist', 'skipOnError' => true, 'targetClass' => TbServicegroup::className(), 'targetAttribute' => ['service_groupid' => 'servicegroupid']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'serviceid' => 'เลขที่บริการ',
            'service_name' => 'ชื่อบริการ',
            'service_groupid' => 'เลขที่กลุ่มบริการ',
            'service_route' => 'ลำดับการบริการ',
            'prn_profileid' => 'แบบการพิมพ์บัตรคิว',
            'prn_copyqty' => 'จำนวนพิมพ์ต่อครั้ง',
            'service_prefix' => 'ตัวอักษร/ตัวเลข นำหน้าคิว',
            'service_numdigit' => 'จำนวนหลักหมายเลขคิว',
            'service_status' => 'สถานะคิว',
            'service_md_name_id' => 'ชื่อแพทย์',
            'print_by_hn' => 'ออกบัตรคิวโดยใช้ HN',
            'quickly' => 'แสดงปุ่มคิวด่วน',
            'show_on_kiosk' => 'แสดงปุ่ม Kiosk',
            'show_on_mobile' => 'แสดงปุ่ม Mobile',
            'btn_kiosk_name' => 'ชื่อปุ่ม Kiosk',
            'prn_profileid_quickly' => 'แบบการพิมพ์บัตรคิวด่วน',
            'main_dep' => 'รหัสแผนก',
            'service_type_id' => 'ประเภทบริการ',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceGroup()
    {
        return $this->hasOne(TbServicegroup::className(), ['servicegroupid' => 'service_groupid']);
    }

    /**
     * @inheritdoc
     * @return TbServiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TbServiceQuery(get_called_class());
    }

    public function validateSchedules($attribute)
    {
        $requiredValidator = new RequiredValidator();

        foreach ($this->$attribute as $index => $row) {
            $error1 = null;
            $error2 = null;
            $error3 = null;
            $error4 = null;
            $requiredValidator->validate($row['t_slot_begin'], $error1);
            if (!empty($error1)) {
                $key = $attribute . '[' . $index . '][t_slot_begin]';
                $this->addError($key, $error1);
            }
            $requiredValidator->validate($row['t_slot_end'], $error2);
            if (!empty($error2)) {
                $key = $attribute . '[' . $index . '][t_slot_end]';
                $this->addError($key, $error2);
            }
            $requiredValidator->validate($row['q_limit'], $error3);
            if (!empty($error3)) {
                $key = $attribute . '[' . $index . '][q_limit]';
                $this->addError($key, $error3);
            }
            $requiredValidator->validate($row['q_limitqty'], $error4);
            if (!empty($error4)) {
                $key = $attribute . '[' . $index . '][q_limitqty]';
                $this->addError($key, $error4);
            }
        }
    }

    public function getAppointPrefix() //ตัวอักษรหน้าเลขคิว
    {
        return $this->hasOne(TbCidStation::className(), ['id' => 'service_prefix']);
    }

        
    // เรียงคิวต่อเนื่อง
    public function getIsPrefixSuccession()
    {
        return $this->prefix_succession == 1;
    }
}
