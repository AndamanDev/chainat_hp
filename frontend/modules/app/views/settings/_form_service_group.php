<?php

use frontend\modules\app\models\TbServicegroup;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use homer\widgets\dynamicform\DynamicFormWidget;
use yii\icons\Icon;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use unclead\multipleinput\MultipleInput;

$this->registerCss(
    <<<CSS
.modal-dialog{
    width: 90%;
}
.form-horizontal .radio,
.form-horizontal .checkbox,
.form-horizontal .radio-inline,
.form-horizontal .checkbox-inline {
  display: inline-block;
}
CSS
);
?>
<?php $form = ActiveForm::begin([
    'id' => 'form-service-group',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['showLabels' => false],
]); ?>
<div class="form-group">
    <?= Html::activeLabel($model, 'servicegroup_name', ['label' => 'ชื่อกลุ่มบริการ', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'servicegroup_name', ['showLabels' => false])->textInput([
            'placeholder' => 'ชื่อกลุ่มบริการ'
        ]); ?>
    </div>
    <?= Html::activeLabel($model, 'servicegroup_order', ['label' => 'ลำดับ', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-2">
        <?= $form->field($model, 'servicegroup_order', ['showLabels' => false])->textInput([
            'placeholder' => 'ลำดับ'
        ]); ?>
    </div>
</div>

<div class="form-group">
    <?= Html::activeLabel($model, 'servicestatus_default', ['label' => 'เปิดใช้งานบน mobile ', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'servicestatus_default', ['showLabels' => false])->RadioList(
            [
                0 => 'ปิดใช้งาน',
                1 => 'เปิดใช้งาน'
            ],
            [
                'inline' => true,
                'item' => function ($index, $label, $name, $checked, $value) {
                    $return = '<div class="radio"><label style="font-size: 1em">';
                    $return .= Html::radio($name, $checked, ['value' => $value]);
                    $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                    $return .= '</label></div>';
                    return $return;
                }
            ]
        );
        ?>
    </div>
    <?= Html::activeLabel($model, 'servicegroup_clinic', ['label' => 'ประเภท', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'servicegroup_clinic', ['showLabels' => false])->RadioList(
            [
                'T' => 'คลินิก',
                'F' => 'อื่นๆ'
            ],
            [
                'inline' => true,
                'item' => function ($index, $label, $name, $checked, $value) {
                    $return = '<div class="radio"><label style="font-size: 1em">';
                    $return .= Html::radio($name, $checked, ['value' => $value]);
                    $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                    $return .= '</label></div>';
                    return $return;
                }
            ]
        );
        ?>
    </div>
</div>

<div class="form-group">
    <?= Html::activeLabel($model, 'show_on_kiosk', ['label' => 'แสดงบน kiosk', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'show_on_kiosk', ['showLabels' => false])->RadioList(
            [0 => 'No', 1 => 'Yes'],
            [
                'inline' => true,
                'item' => function ($index, $label, $name, $checked, $value) {
                    $return = '<div class="radio"><label style="font-size: 1em">';
                    $return .= Html::radio($name, $checked, ['value' => $value]);
                    $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                    $return .= '</label></div>';
                    return $return;
                }
            ]
        );
        ?>
    </div>
    <?= Html::activeLabel($model, 'show_on_mobile', ['label' => 'แสดงบน mobile', 'class' => 'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
        <?= $form->field($model, 'show_on_mobile', ['showLabels' => false])->RadioList(
            [0 => 'No', 1 => 'Yes'],
            [
                'inline' => true,
                'item' => function ($index, $label, $name, $checked, $value) {
                    $return = '<div class="radio"><label style="font-size: 1em">';
                    $return .= Html::radio($name, $checked, ['value' => $value]);
                    $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                    $return .= '</label></div>';
                    return $return;
                }
            ]
        );
        ?>
    </div>
</div>
<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
    'widgetBody' => '.container-items', // required: css class selector
    'widgetItem' => '.item', // required: css class
    'limit' => \Yii::$app->keyStorage->get('dynamic-limit', 100), // the maximum times, an element can be cloned (default 999)
    'min' => 0, // 0 or 1 (default 1)
    'insertButton' => '.add-item', // css class
    'deleteButton' => '.remove-item', // css class
    'model' => $modelServices[0],
    'formId' => 'form-service-group',
    'formFields' => [
        'service_name',
        'service_groupid',
        'service_route',
        'prn_profileid',
        'prn_copyqty',
        'service_prefix',
        'service_numdigit',
        'service_status',
        'service_md_name_id'
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
        <?= Icon::show('edit') . 'กลุ่มบริการย่อย'; ?>
        <?= Html::button(Icon::show('plus') . 'เพิ่มรายการ', ['class' => 'pull-right add-item btn btn-success btn-xs']); ?>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body container-items">
        <!-- widgetContainer -->
        <?php foreach ($modelServices as $index => $modelService) : ?>
            <div class="item panel panel-default">
                <!-- widgetBody -->
                <div class="panel-heading">
                    <?= Html::tag('span', 'รายการที่ : ' . ($index + 1), ['class' => 'panel-title']); ?>
                    <div style="float: right;">
                        <?= Html::button(Icon::show('minus'), ['class' => 'remove-item btn btn-danger btn-xs']); ?>
                        <?= Html::button(Icon::show('plus'), ['class' => 'add-item btn btn-success btn-xs']); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body">
                    <?php
                    if (!$modelService->isNewRecord) {
                        echo Html::activeHiddenInput($modelService, "[{$index}]serviceid");
                    }
                    ?>
                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]main_dep", ['label' => 'รหัสแผนก', 'class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]main_dep", ['showLabels' => false])->widget(Select2::classname(), [
                                'data' => ArrayHelper::map((new \yii\db\Query())
                                    ->select(['CONCAT(tb_deptcode.deptcode,\' \',\': \',\'\', tb_deptcode.deptname,\'\') AS deptname','tb_deptcode.deptcode'])
                                    ->from('tb_deptcode')
                                    ->all(), 'deptcode', 'deptname'),
                                'options' => ['placeholder' => 'เลือกแผนก...'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                                'theme' => Select2::THEME_BOOTSTRAP,
                            ]) ?>
                        </div>

                        <?= Html::activeLabel($modelService, "[{$index}]service_type_id", ['label' => 'ประเภทบริการ', 'class' => 'col-sm-1 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]service_type_id", ['showLabels' => false])->widget(Select2::classname(), [
                                'data' => ArrayHelper::map((new \yii\db\Query())
                                    ->select(['*'])
                                    ->from('tb_service_type')
                                    ->all(), 'service_type_id', 'service_type_name'),
                                'options' => ['placeholder' => 'เลือกประเภทบริการ...'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                                'theme' => Select2::THEME_BOOTSTRAP,
                            ]) ?>
                        </div>
                        
                        <?php /*
                            <div class="col-sm-4">
                                <?= $form->field($modelService, "[{$index}]main_dep", ['showLabels' => false])->textInput([
                                    'placeholder' => 'รหัสแผนก'
                                ]); ?>
                            </div>
                        */?>
                    </div>
                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]service_name", ['label' => 'ชื่อบริการ', 'class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]service_name", ['showLabels' => false])->textInput([
                                'placeholder' => 'ชื่อบริการ'
                            ]); ?>
                        </div>

                        <?= Html::activeLabel($modelService, "[{$index}]service_route", ['label' => 'ลำดับการบริการ', 'class' => 'col-sm-1 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]service_route", ['showLabels' => false])->textInput([
                                'placeholder' => 'ลำดับการบริการ',
                                'value' => $modelService->isNewRecord ? 1 : $modelService['service_route'],
                            ]); ?>
                        </div>
                    </div><!-- End FormGroup /-->

                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]prn_profileid", ['label' => 'แบบการพิมพ์บัตรคิว', 'class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]prn_profileid", ['showLabels' => false])->widget(Select2::classname(), [
                                'data' => ArrayHelper::map((new \yii\db\Query())
                                    ->select(['tb_ticket.ids', 'tb_ticket.hos_name_th'])
                                    ->from('tb_ticket')
                                    ->all(), 'ids', 'hos_name_th'),
                                'options' => ['placeholder' => 'เลือกแบบการพิมพ์บัตรคิว...'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                                'theme' => Select2::THEME_BOOTSTRAP,
                            ]) ?>
                        </div>

                        <?= Html::activeLabel($modelService, "[{$index}]prn_copyqty", ['label' => 'จำนวนพิมพ์ต่อครั้ง', 'class' => 'col-sm-1 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]prn_copyqty", ['showLabels' => false])->textInput([
                                'placeholder' => 'จำนวนพิมพ์ต่อครั้ง',
                            ]); ?>
                        </div>
                    </div><!-- End FormGroup /-->

                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]prn_profileid_quickly", ['label' => 'แบบการพิมพ์บัตรคิว(ด่วน)', 'class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]prn_profileid_quickly", ['showLabels' => false])->widget(Select2::classname(), [
                                'data' => ArrayHelper::map((new \yii\db\Query())
                                    ->select(['tb_ticket.ids', 'tb_ticket.hos_name_th'])
                                    ->from('tb_ticket')
                                    ->all(), 'ids', 'hos_name_th'),
                                'options' => ['placeholder' => 'เลือกแบบการพิมพ์บัตรคิวด่วน...'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                                'theme' => Select2::THEME_BOOTSTRAP,
                            ]) ?>
                        </div>

                        <?= Html::activeLabel($modelService, "[{$index}]service_prefix", ['label' => 'ตัวอักษร/ตัวเลข นำหน้าคิว', 'class' => 'col-sm-1 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]service_prefix", ['showLabels' => false])->textInput([
                                'placeholder' => 'ตัวอักษร/ตัวเลข นำหน้าคิว'
                            ]); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]btn_kiosk_name", ['class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]btn_kiosk_name", ['showLabels' => false])->textInput([]); ?>
                        </div>

                        <?= Html::activeLabel($modelService, "[{$index}]service_numdigit", ['label' => 'จำนวนหลักหมายเลขคิว', 'class' => 'col-sm-1 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]service_numdigit", ['showLabels' => false])->textInput([
                                'placeholder' => 'จำนวนหลักหมายเลขคิว',
                            ]); ?>
                        </div>
                    </div><!-- End FormGroup /-->

                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]quickly", ['class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]quickly", ['showLabels' => false])->RadioList(
                                [0 => 'No', 1 => 'Yes'],
                                [
                                    'inline' => true,
                                    'item' => function ($index, $label, $name, $checked, $value) {

                                        $return = '<div class="radio"><label style="font-size: 1em">';
                                        $return .= Html::radio($name, $checked, ['value' => $value]);
                                        $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                                        $return .= '</label></div>';

                                        return $return;
                                    }
                                ]
                            ); ?>
                        </div>



                    </div>

                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]print_by_hn", ['class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]print_by_hn", ['showLabels' => false])->RadioList(
                                [0 => 'No', 1 => 'Yes'],
                                [
                                    'inline' => true,
                                    'item' => function ($index, $label, $name, $checked, $value) {

                                        $return = '<div class="radio"><label style="font-size: 1em">';
                                        $return .= Html::radio($name, $checked, ['value' => $value]);
                                        $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                                        $return .= '</label></div>';

                                        return $return;
                                    }
                                ]
                            ); ?>
                        </div>
                    </div><!-- End FormGroup /-->

                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]show_on_kiosk", ['class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]show_on_kiosk", ['showLabels' => false])->RadioList(
                                [0 => 'No', 1 => 'Yes'],
                                [
                                    'inline' => true,
                                    'item' => function ($index, $label, $name, $checked, $value) {

                                        $return = '<div class="radio"><label style="font-size: 1em">';
                                        $return .= Html::radio($name, $checked, ['value' => $value]);
                                        $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                                        $return .= '</label></div>';

                                        return $return;
                                    }
                                ]
                            ); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]show_on_mobile", ['class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]show_on_mobile", ['showLabels' => false])->RadioList(
                                [0 => 'No', 1 => 'Yes'],
                                [
                                    'inline' => true,
                                    'item' => function ($index, $label, $name, $checked, $value) {

                                        $return = '<div class="radio"><label style="font-size: 1em">';
                                        $return .= Html::radio($name, $checked, ['value' => $value]);
                                        $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                                        $return .= '</label></div>';

                                        return $return;
                                    }
                                ]
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= Html::activeLabel($modelService, "[{$index}]service_status", ['label' => 'สถานะคิว', 'class' => 'col-sm-2 control-label']) ?>
                        <div class="col-sm-4">
                            <?= $form->field($modelService, "[{$index}]service_status", ['showLabels' => false])->RadioList(
                                [0 => 'ปิดใช้งาน', 1 => 'เปิดใช้งาน'],
                                [
                                    'inline' => true,
                                    'item' => function ($index, $label, $name, $checked, $value) {

                                        $return = '<div class="radio"><label style="font-size: 1em">';
                                        $return .= Html::radio($name, $checked, ['value' => $value]);
                                        $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
                                        $return .= '</label></div>';

                                        return $return;
                                    }
                                ]
                            ); ?>
                        </div>
                    </div>

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
                <?= Html::button(Icon::show('close') . 'CLOSE', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']); ?>
                <?= Html::submitButton(Icon::show('save') . 'SAVE', ['class' => 'btn btn-primary']); ?>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs(
    <<<JS
//Form Event
var table = $('#tb-service-group').DataTable();
var \$form = $('#form-service-group');
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