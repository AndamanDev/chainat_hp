<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\icons\Icon;
use kartik\select2\Select2;

$this->registerCss(
  <<<CSS
.modal-dialog{
    width: 90%;
}
CSS
);
?>
<?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL, 'id' => 'form-sound']); ?>
<div class="form-group">
  <?= Html::activeLabel($model, 'sound_name', ['label' => 'ชื่อไฟล์', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
    <?= $form->field($model, 'sound_name', ['showLabels' => false])->textInput(['placeholder' => 'ชื่อไฟล์']); ?>
  </div>

  <?= Html::activeLabel($model, 'sound_path_name', ['label' => 'โฟรเดอร์ไฟล์', 'class' => 'col-sm-1 control-label']) ?>
  <div class="col-sm-4">
    <?= $form->field($model, 'sound_path_name', ['showLabels' => false])->textInput(['placeholder' => 'โฟรเดอร์ไฟล์']); ?>
  </div>
</div>
<div class="form-group">
  <?= Html::activeLabel($model, 'sound_th', ['label' => 'เสียงเรียก', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
    <?= $form->field($model, 'sound_th', ['showLabels' => false])->textInput(['placeholder' => 'เสียงเรียก']); ?>
  </div>

  <?= Html::activeLabel($model, 'sound_type', ['label' => 'ประเภทเสียง', 'class' => 'col-sm-1 control-label']) ?>
  <div class="col-sm-4">
    <?= $form->field($model, 'sound_type', ['showLabels' => false])->widget(Select2::classname(), [
      'data' => [1 => 'เสียงผู้หญิง', 2 => 'เสียงผู้ชาย'],
      'language' => 'de',
      'options' => ['placeholder' => 'ประเภทเสียง'],
      'pluginOptions' => [
        'allowClear' => true
      ],
      'theme' => Select2::THEME_BOOTSTRAP,
    ]); ?>
  </div>
</div>
<div class="form-group">
  <div class="col-sm-12" style="text-align: right;">
    <?= Html::button(Icon::show('close') . 'ปิด', ['class' => 'btn btn-danger', 'data-dismiss' => 'modal']); ?>
    <?= Html::submitButton(Icon::show('save') . 'บันทึก', ['class' => 'btn btn-success']); ?>
  </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$this->registerJs(
  <<<JS
//Form Event
var table = $('#tb-sound').DataTable();
var \$form = $('#form-sound');
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