<?php
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use yii\helpers\Url;

?>
<?php
echo Tabs::widget([
    'items' => [
        [
            'label' => 'กลุ่มบริการ',
            'content' => $this->render('_content_service_group'),
            'active' => Yii::$app->controller->action->id == 'service',
            'url' => Url::to(['/app/settings/service']),
            'options' => ['id' => 'tab-service'],
        ],
//        [
//            'label' => 'Kiosk',
//            'content' => $this->render('_content_kiosk'),
//        ],
        [
            'label' => 'อัพโหลดไฟล์เสียง',
            'content' => $this->render('_content_sound_source'),
            'active' => Yii::$app->controller->action->id == 'files',
            'url' => Url::to(['/app/settings/files']),
            'options' => ['id' => 'tab-files'],
        ],
        [
            'label' => 'ข้อมูลไฟล์เสียง',
            'content' => $this->render('_content_sound'),
            'active' => Yii::$app->controller->action->id == 'sound',
            'url' => Url::to(['/app/settings/sound']),
            'options' => ['id' => 'tab-sound'],
        ],
        [
            'label' => 'จอแสดงผล',
            'content' => $this->render('_content_display'),
            'active' => Yii::$app->controller->action->id == 'display',
            'url' => Url::to(['/app/settings/display']),
            'options' => ['id' => 'tab-display'],
        ],
        [
            'label' => 'ช่องบริการ',
            'content' => $this->render('_content_counter'),
            'active' => Yii::$app->controller->action->id == 'counter',
            'url' => Url::to(['/app/settings/counter']),
            'options' => ['id' => 'tab-counter'],
        ],
        [
            'label' => 'บัตรคิว',
            'content' => $this->render('_content_ticket'),
            'active' => Yii::$app->controller->action->id == 'ticket',
            'url' => Url::to(['/app/settings/ticket']),
            'options' => ['id' => 'tab-ticket'],
        ],
        [
            'label' => 'โปรไฟล์เรียกคิว',
            'content' => $this->render('_content_service_profile'),
            'active' => Yii::$app->controller->action->id == 'service-profile',
            'url' => Url::to(['/app/settings/service-profile']),
            'options' => ['id' => 'tab-service-profile'],
        ],
        [
            'label' => 'โปรแกรมเสียงเรียก',
            'content' => $this->render('_content_sound_station'),
            'active' => Yii::$app->controller->action->id == 'player',
            'url' => Url::to(['/app/settings/player']),
            'options' => ['id' => 'tab-player'],
        ],
        // [
        //     'label' => 'จุดอ่านบัตร',
        //     'content' => $this->render('_content_cid_station'),
        // ],
        [
            'label' => 'จำนวนคิวแจ้งเตือน',
            'content' => $this->render('_content_calling_config'),
            'active' => Yii::$app->controller->action->id == 'config-notification',
            'url' => Url::to(['/app/settings/config-notification']),
            'options' => ['id' => 'tab-config-notification'],
        ],
        // [
        //     'label' => 'Lab',
        //     'content' => $this->render('_content_lab'),
        // ],
        // [
        //     'label' => 'รีเซ็ตคิว',
        //     'content' => $this->render('_content_qreset'),
        // ],
    ],
    'encodeLabels' => false,
    'renderTabContent' => false
]);
?>