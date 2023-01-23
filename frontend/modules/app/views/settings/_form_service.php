<?php

use frontend\modules\app\models\TbServicegroup;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\icons\Icon;

/* @var $this yii\web\View */
/* @var $model frontend\modules\app\models\TbService */
/* @var $form yii\widgets\ActiveForm */

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
.toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
  .toggle.ios .toggle-handle { border-radius: 20px; }
  .checkbox label:after, 
    .radio label:after {
        content: '';
        display: table;
        clear: both;
    }

    .checkbox .cr,
    .radio .cr {
        position: relative;
        display: inline-block;
        border: 1px solid #a9a9a9;
        border-radius: .25em;
        width: 1.3em;
        height: 1.3em;
        float: left;
        margin-right: .5em;
    }

    .radio .cr {
        border-radius: 50%;
    }

    .checkbox .cr .cr-icon,
    .radio .cr .cr-icon {
        position: absolute;
        font-size: .8em;
        line-height: 0;
        top: 50%;
        left: 20%;
    }

    .radio .cr .cr-icon {
        margin-left: 0.04em;
    }

    .checkbox label input[type="checkbox"],
    .radio label input[type="radio"] {
        display: none;
    }

    .checkbox label input[type="checkbox"] + .cr > .cr-icon,
    .radio label input[type="radio"] + .cr > .cr-icon {
        transform: scale(3) rotateZ(-20deg);
        opacity: 0;
        transition: all .3s ease-in;
    }

    .checkbox label input[type="checkbox"]:checked + .cr > .cr-icon,
    .radio label input[type="radio"]:checked + .cr > .cr-icon {
        transform: scale(1) rotateZ(0deg);
        opacity: 1;
    }

    .checkbox label input[type="checkbox"]:disabled + .cr,
    .radio label input[type="radio"]:disabled + .cr {
        opacity: .5;
    }
CSS
);
?>

<div class="tb-service-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-service-group',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['showLabels' => false],
    ]); ?>

    <div class="form-group">
        <?= Html::activeLabel($model, "service_groupid", ['label' => 'กลุ่มแผนก', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "service_groupid", ['showLabels' => false])->widget(Select2::classname(), [
                'data' => ArrayHelper::map(TbServicegroup::find()->all(), 'servicegroupid', 'servicegroup_name'),
                'options' => ['placeholder' => 'เลือกกลุ่มแผนก...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
                'theme' => Select2::THEME_BOOTSTRAP,
            ]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::activeLabel($model, "main_dep", ['label' => 'รหัสแผนก', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "main_dep", ['showLabels' => false])->widget(Select2::classname(), [
                'data' => ArrayHelper::map((new \yii\db\Query())
                    ->select(['CONCAT(tb_deptcode.deptcode,\' \',\': \',\'\', tb_deptcode.deptname,\'\') AS deptname', 'tb_deptcode.deptcode'])
                    ->from('tb_deptcode')
                    ->all(), 'deptcode', 'deptname'),
                'options' => ['placeholder' => 'เลือกแผนก...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
                'theme' => Select2::THEME_BOOTSTRAP,
            ]) ?>
        </div>

        <?= Html::activeLabel($model, "service_type_id", ['label' => 'ประเภทบริการ', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "service_type_id", ['showLabels' => false])->widget(Select2::classname(), [
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
    </div>
    <div class="form-group">
        <?= Html::activeLabel($model, "service_name", ['label' => 'ชื่อบริการ', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "service_name", ['showLabels' => false])->textInput([
                'placeholder' => 'ชื่อบริการ'
            ]); ?>
        </div>

        <?= Html::activeLabel($model, "service_route", ['label' => 'ลำดับการบริการ', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "service_route", ['showLabels' => false])->textInput([
                'placeholder' => 'ลำดับการบริการ',
                'value' => $model->isNewRecord ? 1 : $model['service_route'],
            ]); ?>
        </div>
    </div><!-- End FormGroup /-->

    <div class="form-group">
        <?= Html::activeLabel($model, "prn_profileid", ['label' => 'แบบการพิมพ์บัตรคิว', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "prn_profileid", ['showLabels' => false])->widget(Select2::classname(), [
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

        <?= Html::activeLabel($model, "prn_copyqty", ['label' => 'จำนวนพิมพ์ต่อครั้ง', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "prn_copyqty", ['showLabels' => false])->textInput([
                'placeholder' => 'จำนวนพิมพ์ต่อครั้ง',
            ]); ?>
        </div>
    </div><!-- End FormGroup /-->

    <div class="form-group">
        <?= Html::activeLabel($model, "prn_profileid_quickly", ['label' => 'แบบการพิมพ์บัตรคิว(ด่วน)', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "prn_profileid_quickly", ['showLabels' => false])->widget(Select2::classname(), [
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

        <?= Html::activeLabel($model, "service_prefix", ['label' => 'ตัวอักษร/ตัวเลข หน้าคิว', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "service_prefix", ['showLabels' => false])->textInput([
                'placeholder' => 'ตัวอักษร/ตัวเลข นำหน้าคิว'
            ]); ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::activeLabel($model, "btn_kiosk_name", ['class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "btn_kiosk_name", ['showLabels' => false])->textInput([]); ?>
        </div>

        <?= Html::activeLabel($model, "service_numdigit", ['label' => 'จำนวนหลักหมายเลขคิว', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "service_numdigit", ['showLabels' => false])->textInput([
                'placeholder' => 'จำนวนหลักหมายเลขคิว',
            ]); ?>
        </div>
    </div><!-- End FormGroup /-->

    <div class="form-group">
        <?= Html::activeLabel($model, "quickly", ['class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "quickly", ['showLabels' => false])->RadioList(
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
        <?= Html::activeLabel($model, "print_by_hn", ['class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "print_by_hn", ['showLabels' => false])->RadioList(
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
        <?= Html::activeLabel($model, "show_on_kiosk", ['class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "show_on_kiosk", ['showLabels' => false])->RadioList(
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
        <?= Html::activeLabel($model, "show_on_mobile", ['class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "show_on_mobile", ['showLabels' => false])->RadioList(
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
        <?= Html::activeLabel($model, "service_status", ['label' => 'สถานะคิว', 'class' => 'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, "service_status", ['showLabels' => false])->RadioList(
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

    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12" style="text-align: right;">
            <div class="form-group">
                <div class="col-sm-12">
                    <?= Html::button(Icon::show('close') . 'ยกเลิก', ['class' => 'btn btn-danger', 'data-dismiss' => 'modal']); ?>
                    <?= Html::submitButton(Icon::show('save') . 'บันทึก', ['class' => 'btn btn-success']); ?>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

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