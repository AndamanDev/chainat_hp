<?php

namespace frontend\modules\app\models\mobile;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

/**
 * This is the model class for table "tb_quequ".
 *
 * @property int $q_ids running
 * @property string $q_num หมายเลขคิว
 * @property string $q_timestp วันที่ออกคิว
 * @property int $q_arrive_time เวลามาถึง
 * @property int $q_appoint_time เวลานัดหมาย
 * @property int $pt_id
 * @property string $q_vn Visit number ของผู้ป่วย
 * @property string $q_hn หมายเลข HN ผู้ป่วย
 * @property string $pt_name ชื่อผู้ป่วย
 * @property int $pt_visit_type_id ประเภท
 * @property int $pt_appoint_sec_id แผนกที่นัดหมาย
 * @property int $serviceid ประเภทบริการ
 * @property int $servicegroupid
 * @property int $quickly คิวด่วน
 * @property int $q_status_id สถานะ
 * @property int $doctor_id
 * @property int $counterserviceid เลขที่ช่องบริการ
 * @property string $created_at วันที่บันทึก
 * @property string $updated_at วันที่แก้ไข
 */
class TbQuequ extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
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
            [['q_timestp', 'created_at', 'updated_at','q_qn'], 'safe'],
            [['q_arrive_time', 'q_appoint_time',  'pt_visit_type_id', 'appoint_id', 'servicegroupid', 'serviceid', 'q_status_id', 'counterserviceid', 'tslotid', 'created_from', 'quickly','countdrug','qfinace'], 'integer'],
            [['q_num', 'q_vn', 'q_hn'], 'string', 'max' => 20],
            [['rx_q'], 'string', 'max' => 10],
            [['doctor_id','age'], 'string', 'max' => 11],
            [['cid'], 'string', 'max' => 13],
            [['pt_name'], 'string', 'max' => 200],
            [['doctor_name'], 'string', 'max' => 250],
            [['token', 'u_id'], 'string'],
            [['pt_pic', 'pt_sound', 'maininscl_name'], 'string', 'max' => 255],
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
            'quickly' => 'ความด่วนของคิว',
            'maininscl_name' => 'สิทธิ์',
            'u_id' => 'รหัสผู้ใช้งาน Mobile',
            'token' => 'รหัส token',
            'age' => 'อายุ',
            'countdrug' => 'มีรายการยา',
            'qfinace' => 'มีจ่ายเงิน'
        ];
    }

    /**
     * {@inheritdoc}
     * @return TbQuequQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TbQuequQuery(get_called_class());
    }

    public static function getQuequ($serviceid, $q_date)
    {
        $rows = (new \yii\db\Query())
            ->select([
                'CONCAT(`tb_service`.`service_prefix`,lpad(substr( `tb_quequ`.`q_num`, octet_length( `tb_service`.`service_prefix` ) + 1, `tb_service`.`service_numdigit` ) + 1,
			`tb_service`.`service_numdigit`,
			\'0\')) AS `next_q_num`'
            ])
            ->from('`tb_quequ`')
            ->join('`tb_service`', '`tb_service`.`serviceid` = `tb_quequ`.`serviceid`')
            ->where([
                '`tb_service`' => $serviceid
            ])
            ->andWhere('cast( `tb_quequ`.`q_timestp` AS date )', ['date' => $q_date])
            ->orderBy('`tb_quequ`.`q_ids` DESC')
            ->limit(1)
            ->all(Yii::$app->db);
        return $rows;
    }

    public function generateQnumber($params)
    {
        $maxid = $this->find()
            ->where([
                'serviceid' => $params['serviceid']
            ])
            ->andWhere('DATE(q_timestp) = CURRENT_DATE')
            ->max('q_ids');
        $number = null;
        if ($maxid) {
            $model = $this->findOne($maxid);
            $number = $model['q_num'];
        }
        $component = \Yii::createObject([
            'class'     => \common\components\AutoNumber::class,
            'prefix'    => $params['service_prefix'],
            'number'    => $number,
            'digit'     => $params['service_numdigit'],
        ]);
        return $component->generate();
    }

    public function sendMessage($serviceid)
    {
        //$config = TbCallingConfig::findOne(1);
        $config = TbCallingConfig::find(1)->where(['notice_queue_status' => 1])->one(); // จำนวนคิวที่ตั้งส่งข้อความ
        $limit = ($config ? $config['notice_queue'] + 1 : 4); // defalse 4 คิว

        $last = (new \yii\db\Query())
            ->select(['*'])
            ->from('tb_quequ')
            ->where(['tb_quequ.serviceid' => $serviceid, 'tb_quequ.q_status_id' => 2])
            ->orderBy('tb_quequ.q_ids DESC')
            ->one();
        if ($last) {
            $rows = (new \yii\db\Query())
                ->select(['*'])
                ->from('tb_quequ')
                ->where(['tb_quequ.serviceid' => $serviceid, 'tb_quequ.q_status_id' => 1])
                ->andWhere('tb_quequ.q_ids < :q_ids', [':q_ids' => $last['q_ids']])
                ->limit($limit)
                ->all();
            if (count($rows) == $limit) {
                $modelQueue = ArrayHelper::getValue($rows, $limit - 1);
                if ($modelQueue && !empty($modelQueue['token'])) {
                    $client = new Client();
                    $client->createRequest()
                        ->setFormat(Client::FORMAT_JSON)
                        ->setMethod('POST')
                        ->setUrl(Yii::$app->params['messageURL'])
                        ->addHeaders(['content-type' => 'application/json'])
                        ->setData([
                            'message' => [
                                'data' => [
                                    'type' => 'waiting'
                                ],
                                'notification' => [
                                    'title' => 'อีก ' . ($limit - 1) . ' คิว. ถึงคุณ',
                                    'body' => 'โปรดรอที่จุดบริการ!'
                                ],
                                'token' => $modelQueue['token']
                            ],
                        ])
                        ->send();
                }
            }
        }
    }
}
