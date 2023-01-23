<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use trntv\filekit\widget\Upload;
use kartik\widgets\Select2;
use yii\bootstrap\BootstrapAsset;
use kartik\checkbox\CheckboxX;
use kartik\widgets\SwitchInput;
use yii\icons\Icon;
use yii\web\JsExpression;

$this->registerCss(
    <<<CSS
.modal-header {
    padding: 10px;
}
.modal-dialog {
    width: 90%;
}
.modal-title {
    font-size: 20px;
}
div#bcTarget {
    overflow: hidden !important;
    padding-top: 20px !important;
}

form#form-ticket{
.cke_editable
{
    font-size: 13px;
    line-height: 1.6;

    /* Fix for missing scrollbars with RTL texts. (#10488) */
    word-wrap: break-word;
}

blockquote
{
    font-style: italic;
    font-family: Georgia, Times, "Times New Roman", serif;
    padding: 2px 0;
    border-style: solid;
    border-color: #ccc;
    border-width: 0;
}

.cke_contents_ltr blockquote
{
    padding-left: 20px;
    padding-right: 8px;
    border-left-width: 5px;
}

.cke_contents_rtl blockquote
{
    padding-left: 8px;
    padding-right: 20px;
    border-right-width: 5px;
}

a
{
    color: #0782C1;
}

ol,ul,dl
{
    /* IE7: reset rtl list margin. (#7334) */
    *margin-right: 0px;
    /* preserved spaces for list items with text direction other than the list. (#6249,#8049)*/
    padding: 0 40px;
}

h1,h2,h3,h4,h5,h6
{
    font-weight: normal;
    line-height: 1.2;
}

hr
{
    border: 0px;
    border-top: 1px solid #ccc;
}

img.right
{
    border: 1px solid #ccc;
    float: right;
    margin-left: 15px;
    padding: 5px;
}

img.left
{
    border: 1px solid #ccc;
    float: left;
    margin-right: 15px;
    padding: 5px;
}

pre
{
    white-space: pre-wrap; /* CSS 2.1 */
    word-wrap: break-word; /* IE7 */
    -moz-tab-size: 4;
    tab-size: 4;
}

.marker
{
    background-color: Yellow;
}

span[lang]
{
    font-style: italic;
}

figure
{
    text-align: center;
    border: solid 1px #ccc;
    border-radius: 2px;
    background: rgba(0,0,0,0.05);
    padding: 10px;
    margin: 10px 20px;
    display: inline-block;
}

figure > figcaption
{
    text-align: center;
    display: block; /* For IE8 */
}

a > img {
    padding: 1px;
    margin: 1px;
    border: none;
    outline: 1px solid #0782C1;
}

/* Widget Styles */
.code-featured
{
    border: 5px solid red;
}

.math-featured
{
    padding: 20px;
    box-shadow: 0 0 2px rgba(200, 0, 0, 1);
    background-color: rgba(255, 0, 0, 0.05);
    margin: 10px;
}

.image-clean
{
    border: 0;
    background: none;
    padding: 0;
}

.image-clean > figcaption
{
    font-size: .9em;
    text-align: right;
}

.image-grayscale
{
    background-color: white;
    color: #666;
}

.image-grayscale img, img.image-grayscale
{
    filter: grayscale(100%);
}

.embed-240p
{
    max-width: 426px;
    max-height: 240px;
    margin:0 auto;
}

.embed-360p
{
    max-width: 640px;
    max-height: 360px;
    margin:0 auto;
}

.embed-480p
{
    max-width: 854px;
    max-height: 480px;
    margin:0 auto;
}

.embed-720p
{
    max-width: 1280px;
    max-height: 720px;
    margin:0 auto;
}

.embed-1080p
{
    max-width: 1920px;
    max-height: 1080px;
    margin:0 auto;
}
}
.qwaiting > h4 {
    margin-top: 0px !important;
    margin-bottom: 0px !important;
    text-align: center !important;
}
/*
Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.md or https://ckeditor.com/legal/terms-of-use/#open-source-licences
*/

body
{
	/* Font */

	/* Text color */
	color: #333;

	/* Remove the background color to make it transparent */
	background-color: #fff;

	/* margin: 20px; */
}



CSS
);
$this->registerCssFile("@web/css/80mm.css", [
    'depends' => [BootstrapAsset::className()],
]);
?>
<?php $form = ActiveForm::begin([
    'id' => 'form-ticket', 'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['showLabels' => false],
]); ?>
<div class="form-group">
    <?= Html::activeLabel($model, 'logo', ['label' => 'โลโก้บัตรคิว', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'logo')->widget(Upload::classname(), [
            'url' => ['file-upload'],
        ])->hint('<span class="text-warning">ภาพที่จะนำไปแสดงบนบัตรคิว</span>') ?>
    </div>
</div>

<div class="form-group">
    <?= Html::activeLabel($model, 'hos_name_th', ['label' => 'ชื่อบัตรคิว', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'hos_name_th', ['showLabels' => false])->textInput([
            'placeholder' => 'ชื่อบัตรคิว'
        ]); ?>
    </div>
    <?php /*
        <?= Html::activeLabel($model, 'hos_name_en', ['label' => 'ชื่อ รพ. (อังกฤษ)','class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, 'hos_name_en',['showLabels'=>false])->textInput([
                'placeholder' => 'ชื่อ รพ. (อังกฤษ)'
            ]); ?>
        </div>
    */ ?>
</div>

<div class="form-group">
    <?= Html::activeLabel($model, 'barcode_type', ['label' => 'รหัสบาร์โค้ด', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'barcode_type')->widget(Select2::classname(), [
            'data' => [
                'codabar' => 'codabar',
                'code11' => 'code11',
                'code39' => 'code39',
                'code93' => 'code93',
                'code128' => 'code128',
                'ean8' => 'ean8',
                'ean13' => 'ean13',
                'std25' => 'std25',
                'int25' => 'int25',
                'msi' => 'msi',
                'datamatrix' => 'datamatrix'
            ],
            'options' => ['placeholder' => 'เลือกรหัสโค้ด...', 'value' => $model->isNewRecord ? 'code128' : $model['barcode_type']],
            'pluginOptions' => [
                'allowClear' => true
            ],
            'theme' => Select2::THEME_BOOTSTRAP,
        ])->hint('<span class="text-danger">แนะนำให้ใช้โค้ด code128 </span>') ?>
    </div>

    <?= Html::activeLabel($model, 'status', ['label' => 'สถานะการใช้งาน', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?php
        echo $form->field($model, 'status')->widget(SwitchInput::classname(), [
          'pluginOptions' => [
            // 'size' => 'mini',
            'onColor' => 'success',
            'offColor' => 'danger',
            'onText' => 'ใช้งาน',
            'offText' => 'ปิดใช้งาน',
        ]
        ]);
        ?>
    </div>
</div>

<div class="form-group">
    <?= Html::activeLabel($model, 'template', ['label' => 'บัตรคิว', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'template')->textarea([
            'value' => $model->isNewRecord || empty($model['template']) ? $model->defaultTemplate : $model['template'],
        ])->hint('<span class="text-danger">หมายเหตุ. ห้าม!!! เปลี่ยนข้อความที่มีเครื่องหมาย {} </span>') ?>
    </div>
    <?= Html::activeLabel($model, 'template', ['label' => 'ตัวอย่างบัตรคิว', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <div id="editor-preview">
            <?= $model->isNewRecord || empty($model['template']) ? $model->exampleTemplate : $model->ticketPreview; ?>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="col-md-12">
        <pre>
{time} : เวลาพิมพ์บัตรคิว
{qwaiting} : จำนวนคิวที่รอฃ
{hos_name_th} : ชื่อโรงพยาบาล
{pharmacy_drug_name} : ชื่อร้านขายยา
<?= implode("\n", $description) ?>
    </pre>

    </div>
</div>
<?= Html::activeHiddenInput($model, 'default_template', ['value' => $model->defaultTemplate]) ?>

<div class="form-group">
    <div class="col-sm-12" style="text-align: right;">
      <?= Html::button(Icon::show('close') . 'ปิด', ['class' => 'btn btn-danger', 'data-dismiss' => 'modal']); ?>
      <?= Html::submitButton(Icon::show('save') . 'บันทึก', ['class' => 'btn btn-success']); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$this->registerJsFile(
    '@web/vendor/ckeditor/ckeditor.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJsFile(
    '@web/vendor/moment/moment.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJsFile(
    '@web/vendor/moment/locale/th.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$this->registerJs(
    <<<JS
var d = new Date();
var y = d.getFullYear() + 543;
moment.locale('th');
var editor  = CKEDITOR.inline( 'tbticket-template',{
    contenteditable: true,
    language: 'th',
    extraPlugins: 'sourcedialog',
    uiColor: '#f1f3f6'
});

editor.on('change',function(){
    var data = editor.getData()
    .replace('{hos_name_th}', $('#tbticket-hos_name_th').val())
    .replace('{q_hn}','0008962222')
    .replace('{pt_name}','Banbung Hospital')
    .replace('{q_num}','A001')
    .replace('{pt_visit_type}','ผู้ป่วยนัดหมาย')
    .replace('{sec_name}','แผนกห้องยา')
    .replace('{rx_q}','เลขที่ใบสั่งยา')
    .replace('{pharmacy_drug_name}','ชื่อร้านขายยา')
    .replace('{time}',moment().format("D MMM ") + (y.toString()).substr(2))
    .replace('{user_print}','Admin Hospital');
    data.replace('{hos_name_th}', $('#tbticket-hos_name_th').val())
    $('#editor-preview').html(data);
    editor.updateElement();
});

var table = $('#tb-ticket').DataTable();
var \$form = $('#form-ticket');
\$form.on('beforeSubmit', function() {
    var data = new FormData($(\$form)[0]);//\$form.serialize();
    var \$btn = $('button[type="submit"]').button('loading');//loading btn
    \$.ajax({
        url: \$form.attr('action'),
        type: 'POST',
        data: data,
        async: false,
        processData: false,
        contentType: false,
        success: function (data) {
            if(data.status == '200'){
                $('#ajaxCrudModal').modal('hide');//hide modal
                table.ajax.reload();//reload table
                swal({//alert completed!
                    type: 'success',
                    title: 'บันทึกสำเร็จ!',
                    showConfirmButton: false,
                    timer: 1500
                });
                setTimeout(function(){ 
                    \$btn.button('reset');
                }, 1000);//clear button loading
            }else if(data.validate != null){
                $.each(data.validate, function(key, val) {
                    $(\$form).yiiActiveForm('updateAttribute', key, [val]);
                });
                \$btn.button('reset');
            }
        },
        error: function(jqXHR, errMsg) {
            swal('Oops...',errMsg,'error');
            \$btn.button('reset');
        }
    });
    return false; // prevent default submit
});
/*
$("#bcTarget").barcode("1234567890128", "code128",{
    fontSize: 10,
    showHRI: true
});
jQuery('#qrcode').qrcode({width: 100,height: 100,text: "size doesn't matter"});*/
JS
);
?>