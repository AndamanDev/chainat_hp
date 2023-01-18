<?php

namespace frontend\modules\app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\behaviors\AttributeBehavior;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "tb_quequ".
 *
 * @property int $q_ids running
 * @property string $q_num หมายเลขคิว
 * @property string $q_timestp วันที่ออกคิว
 * @property int $q_arrive_time เวลามาถึง
 * @property int $q_appoint_time เวลานัดหมาย
 * @property string $cid รหัสประชาชนผู้ป่วย
 * @property string $q_vn Visit number ของผู้ป่วย
 * @property string $q_hn หมายเลข HN ผู้ป่วย
 * @property string $q_qn QN
 * @property string $rx_q
 * @property string $pt_name ชื่อผู้ป่วย
 * @property int $pt_visit_type_id ประเภท walkin/ไม่ walkin
 * @property int $appoint_id แผนกที่นัดหมาย
 * @property int $servicegroupid กลุ่มบริการ
 * @property int $quickly ความด่วนของคิว
 * @property int $serviceid ประเภทบริการ
 * @property int $created_from คิวสร้างจาก 1 kiosk 2 mobile
 * @property int $q_status_id สถานะ
 * @property string $doctor_id รหัสแพทย์
 * @property string $doctor_name แพทย์ที่นัด
 * @property int $counterserviceid เลขที่ช่องบริการ
 * @property int $tslotid รหัสช่วงเวลา
 * @property string $created_at วันที่บันทึก
 * @property string $updated_at วันที่แก้ไข
 * @property string $pt_pic ไฟล์ภาพ path file
 * @property string $pt_sound ไฟล์เสียง path file
 * @property string $maininscl_name สิทธิ์
 * @property string $u_id รหัสผู้ใช้งาน mobile
 * @property string $token รหัสแจ้งเตือนคิว
 * @property string $age อายุ
 * @property int $countdrug 0 = ไม่มียา , 1 = มียา
 * @property int $qfinace 0 = ไม่ต้องจ่ายเงิน, 1 = จ่ายงิน
 * @property string $queue_date วันที่ออกบัตรคิว
 * @property string $queue_time เวลาออกบัตรคิว
 */
class TbQuequ extends \yii\db\ActiveRecord
{
    public $vstdate;

    public $search_by;

    public $cid_station;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tb_quequ';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['q_timestp', 'created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => Yii::$app->formatter->asDate('now', 'php:Y-m-d H:i:s')
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          [['q_timestp', 'created_at', 'updated_at', 'queue_date', 'queue_time'], 'safe'],
          [['q_arrive_time', 'q_appoint_time', 'pt_visit_type_id', 'appoint_id', 'servicegroupid', 'quickly', 'serviceid', 'created_from', 'q_status_id', 'counterserviceid', 'tslotid', 'countdrug', 'qfinace'], 'integer'],
          [['u_id', 'token'], 'string'],
          // [['queue_date', 'queue_time'], 'required'],
          [['q_num', 'q_vn', 'q_hn'], 'string', 'max' => 20],
          [['cid'], 'string', 'max' => 13],
          [['q_qn', 'rx_q'], 'string', 'max' => 10],
          [['pt_name'], 'string', 'max' => 200],
          [['doctor_id'], 'string', 'max' => 50],
          [['doctor_name'], 'string', 'max' => 250],
          [['pt_pic', 'pt_sound', 'maininscl_name'], 'string', 'max' => 255],
          [['age'], 'string', 'max' => 11],
      ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
          'q_ids' => 'running',
          'q_num' => 'หมายเลขคิว',
          'q_timestp' => 'วันที่ออกคิว',
          'q_arrive_time' => 'เวลามาถึง',
          'q_appoint_time' => 'เวลานัดหมาย',
          'cid' => 'รหัสประชาชนผู้ป่วย',
          'q_vn' => 'Visit number ของผู้ป่วย',
          'q_hn' => 'หมายเลข HN ผู้ป่วย',
          'q_qn' => 'QN',
          'rx_q' => 'Rx Q',
          'pt_name' => 'ชื่อผู้ป่วย',
          'pt_visit_type_id' => 'ประเภท walkin/ไม่ walkin',
          'appoint_id' => 'แผนกที่นัดหมาย',
          'servicegroupid' => 'กลุ่มบริการ',
          'quickly' => 'ความด่วนของคิว',
          'serviceid' => 'ประเภทบริการ',
          'created_from' => 'คิวสร้างจาก 1 kiosk 2 mobile',
          'q_status_id' => 'สถานะ',
          'doctor_id' => 'รหัสแพทย์',
          'doctor_name' => 'แพทย์ที่นัด',
          'counterserviceid' => 'เลขที่ช่องบริการ',
          'tslotid' => 'รหัสช่วงเวลา',
          'created_at' => 'วันที่บันทึก',
          'updated_at' => 'วันที่แก้ไข',
          'pt_pic' => 'ไฟล์ภาพ path file',
          'pt_sound' => 'ไฟล์เสียง path file',
          'maininscl_name' => 'สิทธิ์',
          'u_id' => 'รหัสผู้ใช้งาน mobile',
          'token' => 'รหัสแจ้งเตือนคิว',
          'age' => 'อายุ',
          'countdrug' => '0 = ไม่มียา , 1 = มียา',
          'qfinace' => '0 = ไม่ต้องจ่ายเงิน, 1 = จ่ายงิน',
          'queue_date' => 'วันที่ออกบัตรคิว',
          'queue_time' => 'เวลาออกบัตรคิว',
        ];
    }

    /**
     * @inheritdoc
     * @return TbQuequQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TbQuequQuery(get_called_class());
    }

    public function genQnum($service)
    {
        // $queue = ArrayHelper::map($this->find()->where(['serviceid' => $service['serviceid']])->all(),'q_ids','q_num');
        $queue = ArrayHelper::map($this->find()->all(), 'q_ids', 'q_num');
        $qnums = [];
        $maxqnum = null;
        $qid = null;
        if (count($queue) > 0) {
            foreach ($queue as $key => $q) {
                $qnums[$key] = preg_replace('/[^0-9\.]/', '', $q);
            }
            $maxqnum = max($qnums);
            $qid = array_search($maxqnum, $qnums);
        }
        $component = \Yii::createObject([
            'class' => \common\components\AutoNumber::className(),
            'prefix' => 0, //$service ? $service['service_prefix'] : 'A',
            'number' => ArrayHelper::getValue($queue, $qid, null),
            'digit' => 2, //$service ? $service['service_numdigit'] : 3,
        ]);
        return $component->generate();
    }

    public function getStatus()
    {
        $modelQ = $this;
        $modelQTran = TbQtrans::findOne(['q_ids' => $this->q_ids]);
        if ($modelQTran['service_status_id'] == 1) {
            //รอเรียก
            if ($modelQ['servicegroupid'] == 1) {
                //ลงทะเบียน
                return 'รอเรียกคิว (จุดลงทะเบียน)';
            } else {
                //ซักประวัติ
                return 'รอเรียกคิว (ซักประวัติ)';
            }
        } elseif ($modelQTran['service_status_id'] == 2) {
            //เรียกคิว
            $modelCaller = TbCaller::findOne([
                'qtran_ids' => $modelQTran['ids'],
                'q_ids' => $modelQ['q_ids'],
            ]);
            if (!$modelCaller) {
                return '-';
            }
            if ($modelQ['servicegroupid'] == 1) {
                //ลงทะเบียน
                if (
                    $modelCaller['call_status'] == 'calling' ||
                    $modelCaller['call_status'] == 'callend'
                ) {
                    return 'กำลังเรียก (จุดลงทะเบียน) ' .
                        $modelCaller->tbCounterservice->counterservice_name;
                }
            }
            if (
                $modelQ['servicegroupid'] == 2 &&
                empty($modelQTran['counter_service_id'])
            ) {
                //
                if (
                    $modelCaller['call_status'] == 'calling' ||
                    $modelCaller['call_status'] == 'callend'
                ) {
                    return 'กำลังเรียก (ซักประวัติ) ' .
                        $modelCaller->tbCounterservice->counterservice_name;
                }
            }
            if (
                $modelQ['servicegroupid'] == 2 &&
                !empty($modelQTran['counter_service_id'])
            ) {
                //
                $modelCaller = TbCaller::find()
                    ->where([
                        'qtran_ids' => $modelQTran['ids'],
                        'q_ids' => $modelQ['q_ids'],
                    ])
                    ->orderBy('caller_ids DESC')
                    ->one();
                if (!$modelCaller) {
                    return '-';
                }
                if (
                    $modelCaller['call_status'] == 'calling' ||
                    $modelCaller['call_status'] == 'callend'
                ) {
                    return 'กำลังเรียก (ห้องตรวจ) ' .
                        $modelCaller->tbCounterservice->counterservice_name;
                }
            }
        } elseif ($modelQTran['service_status_id'] == 3) {
            //พักคิว
            $modelCaller = TbCaller::findOne([
                'qtran_ids' => $modelQTran['ids'],
                'q_ids' => $modelQ['q_ids'],
            ]);
            if (!$modelCaller) {
                return '-';
            }
            if ($modelQ['servicegroupid'] == 1) {
                //ลงทะเบียน
                if ($modelCaller['call_status'] == 'hold') {
                    return 'พักคิว (จุดลงทะเบียน) ' .
                        $modelCaller->tbCounterservice->counterservice_name;
                }
            }
            if (
                $modelQ['servicegroupid'] == 2 &&
                empty($modelQTran['counter_service_id'])
            ) {
                //
                if ($modelCaller['call_status'] == 'hold') {
                    return 'พักคิว (ซักประวัติ) ' .
                        $modelCaller->tbCounterservice->counterservice_name;
                }
            }
            if (
                $modelQ['servicegroupid'] == 2 &&
                !empty($modelQTran['counter_service_id'])
            ) {
                //
                $modelCaller = TbCaller::find()
                    ->where([
                        'qtran_ids' => $modelQTran['ids'],
                        'q_ids' => $modelQ['q_ids'],
                    ])
                    ->orderBy('caller_ids DESC')
                    ->one();
                if (!$modelCaller) {
                    return '-';
                }
                if ($modelCaller['call_status'] == 'hold') {
                    return 'พักคิว (ห้องตรวจ) ' .
                        $modelCaller->tbCounterservice->counterservice_name;
                }
            }
        } elseif ($modelQTran['service_status_id'] == 4) {
            //
            if ($modelQ['servicegroupid'] == 1) {
                //ลงทะเบียน
                return 'เสร็จสิ้น (จุดลงทะเบียน)';
            }
            if (
                $modelQ['servicegroupid'] == 2 &&
                empty($modelQTran['counter_service_id'])
            ) {
                //
                return 'เสร็จสิ้น (ซักประวัติ)';
            }
            if (
                $modelQ['servicegroupid'] == 2 &&
                !empty($modelQTran['counter_service_id'])
            ) {
                //
                return 'รอเรียก (ห้องตรวจ)';
            }
        } elseif ($modelQTran['service_status_id'] == 10) {
            if ($modelQ['servicegroupid'] == 1) {
                //ลงทะเบียน
                return 'เสร็จสิ้น (จุดลงทะเบียน)';
            }
            if ($modelQ['servicegroupid'] == 2) {
                return 'เสร็จสิ้น (ห้องตรวจ)';
            }
        }
    }
}
