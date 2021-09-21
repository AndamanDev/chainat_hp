<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\icons\Icon;

/* @var $this yii\web\View */
/* @var $model frontend\modules\app\models\TbServicegroup */
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

<div class="tb-servicegroup-form">

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


    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12" style="text-align: right;">
            <div class="form-group">
                <div class="col-sm-12">
                    <?= Html::button(Icon::show('close') . 'ยกเลิก', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']); ?>
                    <?php if (!$model->isNewRecord) { ?>
                        <?= Html::a('<i class="fa fa-trash" aria-hidden="true"></i> ลบ', ['/app/settings/delete-service-group', 'id' => $model['servicegroupid']], ['class' => 'btn btn-danger btn-delete']) ?>
                    <?php } ?>
                    <?= Html::submitButton(Icon::show('save') . 'บันทึก', ['class' => 'btn btn-primary']); ?>
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

$('a.btn-delete').on('click', function(e){
    e.preventDefault();
    var url = $(this).attr('href')
    swal({
        title: "ยืนยัน?",
        text: "",
        type: "question",
        showCancelButton: true,
        confirmButtonText: "ยืนยัน",
        cancelButtonText: "ยกเลิก",
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        preConfirm: function() {
        return new Promise(function(resolve, reject) {
            $.ajax({
                method: "POST",
                url: url,
                dataType: "json",
                success: function(res) {
                    $('#ajaxCrudModal').modal('hide');//hide modal
                    table.ajax.reload();//reload table
                    resolve()
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    swal('Oops...',errorThrown,'error');
                },
            });
        });
        },
    }).then((result) => {
        if (result.value) {
            swal({//alert completed!
                type: 'success',
                title: 'บันทึกสำเร็จ!',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
})
JS
);
?>