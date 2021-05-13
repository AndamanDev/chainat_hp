<?php

namespace frontend\modules\app\models;

use Yii;

/**
 * This is the model class for table "tb_drug_dispensing".
 *
 * @property int $dispensing_id
 * @property int $pharmacy_drug_id รหัสร้านขายยา
 * @property string $pharmacy_drug_name
 * @property string $deptname ชื่อคลีนิก
 * @property int $rx_operator_id เลขที่ใบสั่งยา
 * @property string $HN HN
 * @property string $pt_name ชื่อผู้รับบริการ
 * @property string $doctor_name ชื่อแพทย์สั่งยา
 * @property string $dispensing_date จ่ายยาเมื่อ
 * @property int $dispensing_status_id สถานะการจ่ายยา
 * @property int $dispensing_by ผู้จ่ายยา
 * @property string $created_at สร้างรายการเมื่อ
 * @property int $created_by สร้างรายการโดย
 * @property string $updated_at ปรับปรุงรายการเมื่อ
 * @property int $updated_by ปรับปรุงรายการโดย
 * @property string $note หมายเหตุการจ่ายยา
 */
class TbDrugDispensing extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_drug_dispensing';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pharmacy_drug_id', 'rx_operator_id', 'dispensing_status_id', 'dispensing_by', 'created_by', 'updated_by'], 'integer'],
            [['dispensing_date', 'created_at', 'updated_at'], 'safe'],
            [['pharmacy_drug_name', 'deptname'], 'string', 'max' => 50],
            [['HN', 'pt_name', 'doctor_name', 'note'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'dispensing_id' => 'Dispensing ID',
            'pharmacy_drug_id' => 'รหัสร้านขายยา',
            'pharmacy_drug_name' => 'Pharmacy Drug Name',
            'deptname' => 'ชื่อคลีนิก',
            'rx_operator_id' => 'เลขที่ใบสั่งยา',
            'HN' => 'HN',
            'pt_name' => 'ชื่อผู้รับบริการ',
            'doctor_name' => 'ชื่อแพทย์สั่งยา',
            'dispensing_date' => 'จ่ายยาเมื่อ',
            'dispensing_status_id' => 'สถานะการจ่ายยา',
            'dispensing_by' => 'ผู้จ่ายยา',
            'created_at' => 'สร้างรายการเมื่อ',
            'created_by' => 'สร้างรายการโดย',
            'updated_at' => 'ปรับปรุงรายการเมื่อ',
            'updated_by' => 'ปรับปรุงรายการโดย',
            'note' => 'หมายเหตุการจ่ายยา',
        ];
    }

}