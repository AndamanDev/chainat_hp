<?php
use yii\bootstrap\BootstrapAsset;
use yii\web\JqueryAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

BootstrapAsset::register($this);
JqueryAsset::register($this);

$this->registerCssFile("@web/css/80mm.css", [
    'depends' => [BootstrapAsset::className()],
]);
$this->registerCssFile("@web/css/site.css", [
    'depends' => [BootstrapAsset::className()],
]);
// $this->registerCssFile("https://fonts.googleapis.com/css?family=Prompt",[
//     'depends' => [BootstrapAsset::className()],
// ]);

$baseUrl = Url::base(true);

$this->registerCss("
div#bcTarget {
    overflow: hidden !important;
    padding-top: 20px !important;
}
div#qrcode img {
    display: none;
}
");

$y = date('Y') + 543;

$this->title = 'รายงาน';
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<!-- 80mm -->
<center>
    <?= $template; ?>
</center>
<?php
$this->registerJsFile(
    '@web/vendor/barcode/jquery-barcode.min.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJsFile(
    '@web/vendor/qrcode/jquery.qrcode.min.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$this->registerJs(<<<JS

});
$(window).on('load', function() {
    //Barcode
    $("#bcTarget").barcode("{$model->q_ids}", "$ticket->barcode_type",{
    fontSize: 10,
    showHRI: true
    });
    //QRCode
    jQuery('#qrcode').qrcode({width: 100,height: 100,text: "{$baseUrl}/mobile-view?id={$model->q_ids}" });
    //Print
    window.print();
    window.onafterprint = function(){
        window.close();
}
JS
);
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
