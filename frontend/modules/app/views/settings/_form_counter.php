<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use homer\widgets\dynamicform\DynamicFormWidget;
use yii\icons\Icon;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\DepDrop;
use frontend\modules\app\models\TbSound;
use yii\helpers\Url;

$this->registerCss(<<<CSS
.modal-dialog{
    width: 90%;
}
CSS
);
?>
<?php $form = ActiveForm::begin([
    'id' => 'form-counter', 
    'type' => ActiveForm::TYPE_HORIZONTAL, 
    'formConfig' => ['showLabels' => false],
]);?>
    <div class="form-group">
        <?= Html::activeLabel($model, 'counterservice_type', ['label' => 'ชื่อประเภท','class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, 'counterservice_type',['showLabels'=>false])->textInput([
                'placeholder' => 'ชื่อประเภท'
            ]); ?>
        </div>
        <?php /*
        <?= Html::activeLabel($model, 'sound_id', ['label' => 'ไฟล์เสียง','class'=>'col-sm-1 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, 'sound_id',['showLabels'=>false])->widget(Select2::classname(), [
                'data' => ArrayHelper::map(
                    (new \yii\db\Query())
                        ->select(['CONCAT(tb_sound.sound_name,\' \',\'(\',tb_sound.sound_th,\')\') AS sound_name', 'tb_sound.sound_id'])
                        ->from('tb_sound')
                        ->all(),'sound_id','sound_name'),
                'options' => ['placeholder' => 'Select a state ...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
                'theme' => Select2::THEME_BOOTSTRAP,
            ]); ?>
        </div>
        */?>
    </div>
    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-items', // required: css class selector
        'widgetItem' => '.item', // required: css class
        'limit' => \Yii::$app->keyStorage->get('dynamic-limit', 20), // the maximum times, an element can be cloned (default 999)
        'min' => 0, // 0 or 1 (default 1)
        'insertButton' => '.add-item', // css class
        'deleteButton' => '.remove-item', // css class
        'model' => $modelCounterservices[0],
        'formId' => 'form-counter',
        'formFields' => [
            'counterserviceid',
            'counterservice_name',
            'counterservice_callnumber',
            'counterservice_type',
            'servicegroupid',
            'userid',
            'serviceid',
            'sound_stationid',
            'sound_id',
            'counterservice_status'
        ],
        'clientEvents' => [
            'afterInsert' => 'function(e, item) {
                jQuery(".dynamicform_wrapper .panel-title").each(function(index) {
                    jQuery(this).html("รายการที่ : " + (index + 1));
                });
            }',
            'afterDelete' => 'function(e, item) {
                jQuery(".dynamicform_wrapper .panel-title").each(function(index) {
                    jQuery(this).html("รายการที่ : " + (index + 1));
                });
            }'
        ],
    ]); ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <?= Icon::show('edit').'ช่องบริการย่อย'; ?>
            <?= Html::button(Icon::show('plus').'เพิ่มรายการ',['class' => 'pull-right add-item btn btn-success btn-xs']); ?>
            <div class="clearfix"></div>
        </div>
        <div class="panel-body container-items"><!-- widgetContainer -->
            <?php foreach ($modelCounterservices as $index => $modelCounterservice): ?>
                <div class="item panel panel-default"><!-- widgetBody -->
                    <div class="panel-heading">
                        <?= Html::tag('span','รายการที่ : '.($index + 1),['class' => 'panel-title']); ?>
                        <div style="float: right;">
                            <?= Html::button(Icon::show('minus'),['class' => 'remove-item btn btn-danger btn-xs']); ?>
                            <?= Html::button(Icon::show('plus'),['class' => 'add-item btn btn-success btn-xs']); ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-body">
                        <?php
                            if (! $modelCounterservice->isNewRecord) {
                                echo Html::activeHiddenInput($modelCounterservice, "[{$index}]counterserviceid");
                            }
                        ?>
                        <div class="form-group">
                            <?= Html::activeLabel($modelCounterservice, "[{$index}]counterservice_name", ['label' => 'ชื่อช่องบริการ','class'=>'col-sm-2 control-label']) ?>
                            <div class="col-sm-4">
                                <?= $form->field($modelCounterservice, "[{$index}]counterservice_name",['showLabels'=>false])->textInput([
                                    'placeholder' => 'ชื่อช่องบริการ'
                                ]); ?>
                            </div>

                            <?= Html::activeLabel($modelCounterservice, "[{$index}]counterservice_callnumber", ['label' => 'หมายเลข','class'=>'col-sm-1 control-label']) ?>
                            <div class="col-sm-4">
                                <?= $form->field($modelCounterservice, "[{$index}]counterservice_callnumber",['showLabels'=>false])->textInput([
                                    'placeholder' => 'หมายเลข',
                                ]); ?>
                            </div>
                        </div><!-- End FormGroup /-->

                        <div class="form-group">
                            <?= Html::activeLabel($modelCounterservice, "[{$index}]servicegroupid", ['label' => 'กลุ่มบริการ','class'=>'col-sm-2 control-label']) ?>
                            <div class="col-sm-4">
                                <?= $form->field($modelCounterservice, "[{$index}]servicegroupid",['showLabels'=>false])->widget(Select2::classname(), [
                                    'data' => ArrayHelper::map((new \yii\db\Query())
                                        ->select(['tb_servicegroup.servicegroupid', 'tb_servicegroup.servicegroup_name'])
                                        ->from('tb_servicegroup')
                                        ->all(),'servicegroupid','servicegroup_name'),
                                    'options' => ['placeholder' => 'เลือกกลุ่มบริการ...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                    'theme' => Select2::THEME_BOOTSTRAP,
                                ]) ?>
                            </div>

                            <?= Html::activeLabel($modelCounterservice, "[{$index}]sound_service_id", ['label' => 'เสียงบริการ','class'=>'col-sm-1 control-label']) ?>
                            <div class="col-sm-4">
                                <?= $form->field($modelCounterservice, "[{$index}]sound_service_id",['showLabels'=>false])->widget(Select2::classname(), [
                                    'data' => ArrayHelper::map(
                                        (new \yii\db\Query())
                                        ->select(['CONCAT(tb_sound.sound_name,\' \',\'(\',tb_sound.sound_th,\')\') AS sound_name', 'tb_sound.sound_id'])
                                        ->from('tb_sound')
                                        ->where('sound_name LIKE :query')
                                        ->addParams([':query'=>'%Service%'])
                                        ->all(),'sound_id','sound_name'),
                                    'options' => ['placeholder' => 'เลือกไฟล์เสียง...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                    'theme' => Select2::THEME_BOOTSTRAP,
                                ])->hint('<small class="text-danger">Prompt1 = เสียงผู้หญิง , Prompt2 = เสียงผู้ชาย</small>') ?>
                            </div>
                        <?php /*
                            <?= Html::activeLabel($modelCounterservice, "[{$index}]serviceid", ['label' => 'กลุ่มบริการย่อย','class'=>'col-sm-1 control-label']) ?>
                            <div class="col-sm-4">
                                <?= $form->field($modelCounterservice, "[{$index}]serviceid",['showLabels'=>false])->widget(DepDrop::classname(), [
                                    'pluginOptions'=>[
                                        'depends'=>['tbcounterservice-'.$index.'-servicegroupid'],
                                        'placeholder'=>'Select...',
                                        'url'=>Url::to(['child-service-group'])
                                    ],
                                    'type'=>DepDrop::TYPE_SELECT2,
                                    'data'=>ArrayHelper::map((new \yii\db\Query())
                                        ->select(['tb_service.serviceid', 'tb_service.service_name'])
                                        ->from('tb_service')
                                        ->where(['service_groupid' => $modelCounterservice['servicegroupid']])
                                        ->all(),'serviceid','service_name'),
                                    'options'=>['placeholder'=>'Select ...'],
                                    'select2Options'=>[
                                        'pluginOptions'=>['allowClear'=>true],
                                        'theme' => Select2::THEME_BOOTSTRAP,
                                        'options' => [
                                            'placeholder' => 'Select...',
                                        ],
                                    ],
                                ]); ?>
                               
                            </div>
                             */?>
                        </div><!-- End FormGroup /-->

                        <div class="form-group">
                          <?php /*
                            <?= Html::activeLabel($modelCounterservice, "[{$index}]sound_stationid", ['label' => 'เครื่องเล่นเสียงที่','class'=>'col-sm-2 control-label']) ?>
                            <div class="col-sm-4">
                                <?= $form->field($modelCounterservice, "[{$index}]sound_stationid",['showLabels'=>false])->textInput([
                                    'placeholder' => 'เครื่องเล่นเสียงที่'
                                ]); ?>
                            </div>
                            */?>

                           
                        </div><!-- End FormGroup /-->

                        <div class="form-group">
                            <?= Html::activeLabel($modelCounterservice, "[{$index}]counterservice_status", ['label' => 'สถานะ','class'=>'col-sm-2 control-label']) ?>
                            <div class="col-sm-4">
                                <?= $form->field($modelCounterservice, "[{$index}]counterservice_status",['showLabels'=>false])->widget(Select2::classname(), [
                                    'data' => [0 => 'Disabled', 1 => 'Enabled'],
                                    'options' => ['placeholder' => 'สถานะ...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                    'theme' => Select2::THEME_BOOTSTRAP,
                                ]) ?>
                            </div>

                            <?= Html::activeLabel($modelCounterservice, "[{$index}]sound_id", ['label' => 'เสียงหมายเลข','class'=>'col-sm-1 control-label']) ?>
                            <div class="col-sm-4">
                                <?= $form->field($modelCounterservice, "[{$index}]sound_id",['showLabels'=>false])->widget(Select2::classname(), [
                                    'data' => ArrayHelper::map(
                                        (new \yii\db\Query())
                                        ->select(['CONCAT(tb_sound.sound_name,\' \',\'(\',tb_sound.sound_th,\')\') AS sound_name', 'tb_sound.sound_id'])
                                        ->from('tb_sound')
                                        ->where('sound_name NOT LIKE :query')
                                        ->addParams([':query'=>'%Service%'])
                                        ->all(),'sound_id','sound_name'),
                                    'options' => ['placeholder' => 'เลือกไฟล์เสียง...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                    'theme' => Select2::THEME_BOOTSTRAP,
                                ])->hint('<small class="text-danger">Prompt1 = เสียงผู้หญิง , Prompt2 = เสียงผู้ชาย</small>') ?>
                            </div>
                        </div><!-- End FormGroup /-->

                    </div><!-- End Body Panel /-->
                </div><!-- End Panel /-->
            <?php endforeach; ?>
        </div><!-- End Body Panel /-->
    </div><!-- End Panel /-->
    <?php DynamicFormWidget::end(); ?>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12" style="text-align: right;">
            <div class="form-group">
                <div class="col-sm-12">
                  <?= Html::button(Icon::show('close') . 'ปิด', ['class' => 'btn btn-danger', 'data-dismiss' => 'modal']); ?>
                  <?= Html::submitButton(Icon::show('save') . 'บันทึก', ['class' => 'btn btn-success']); ?>
                </div>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
//Form Event
var table = $('#tb-counter').DataTable();
var \$form = $('#form-counter');
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
                setTimeout(function(){ \$btn.button('reset'); }, 1500);//clear button loading
            }else if(data.validate != null){
                $.each(data.validate, function(key, val) {
                    $(\$form).yiiActiveForm('updateAttribute', key, [val]);
                });
            }
            \$btn.button('reset');
        },
        error: function(jqXHR, errMsg) {
            swal('Oops...',errMsg,'error');
            \$btn.button('reset');
        }
    });
    return false; // prevent default submit
});
JS
);
?>