<?php
use yii\helpers\Html;
use yii\bootstrap\Tabs;
?>
<?php
echo Tabs::widget([
    'items' => [
        [
            'label' => 'กลุ่มบริการ',
            'content' => $this->render('_content_service_group'),
            'active' => true,
        ],
//        [
//            'label' => 'Kiosk',
//            'content' => $this->render('_content_kiosk'),
//        ],
        [
            'label' => 'อัพโหลดไฟล์เสียง',
            'content' => $this->render('_content_sound_source'),
        ],
        [
            'label' => 'ข้อมูลไฟล์เสียง',
            'content' => $this->render('_content_sound'),
        ],
        [
            'label' => 'จอแสดงผล',
            'content' => $this->render('_content_display'),
        ],
        [
            'label' => 'ช่องบริการ',
            'content' => $this->render('_content_counter'),
        ],
        [
            'label' => 'บัตรคิว',
            'content' => $this->render('_content_ticket'),
        ],
        [
            'label' => 'โปรไฟล์',
            'content' => $this->render('_content_service_profile'),
        ],
        [
            'label' => 'โปรแกรมเสียงเรียก',
            'content' => $this->render('_content_sound_station'),
        ],
        [
            'label' => 'จุดอ่านบัตร',
            'content' => $this->render('_content_cid_station'),
        ],
        [
            'label' => 'จำนวนคิวแจ้งเตือน',
            'content' => $this->render('_content_calling_config'),
        ],
        [
            'label' => 'Lab',
            'content' => $this->render('_content_lab'),
        ],
        // [
        //     'label' => 'รีเซ็ตคิว',
        //     'content' => $this->render('_content_qreset'),
        // ],
    ],
    'encodeLabels' => false,
]);
?>