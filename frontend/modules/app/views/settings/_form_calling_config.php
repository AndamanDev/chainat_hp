<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\checkbox\CheckboxX;
use kartik\widgets\SwitchInput;
use yii\icons\Icon;
?>
<?php $form = ActiveForm::begin([
  'id' => 'form-calling-config', 'type' => ActiveForm::TYPE_HORIZONTAL,
  'formConfig' => ['showLabels' => false],
]); ?>
<div class="form-group">
  <?= Html::activeLabel($model, 'notice_queue', ['label' => 'จำนวนคิว', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
    <?= $form->field($model, 'notice_queue', ['showLabels' => false])->textInput([
      'placeholder' => 'จำนวนคิวที่แจ้งเตือน'
    ]); ?>
  </div>

</div>
<div class="form-group">
  <?= Html::activeLabel($model, 'notice_queue_status', ['label' => 'สถานะการใช้งาน', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
    <?php
    echo $form->field($model, 'notice_queue_status')->widget(SwitchInput::classname(), [
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
  <div class="col-sm-12" style="text-align: right;">
    <?= Html::button(Icon::show('close') . 'ปิด', ['class' => 'btn btn-danger', 'data-dismiss' => 'modal']); ?>
    <?= Html::submitButton(Icon::show('save') . 'บันทึก', ['class' => 'btn btn-success']); ?>
  </div>
</div>
<?php ActiveForm::end(); ?>


<?php
$this->registerJs(
  <<<JS
var table = $('#tb-calling-config').DataTable();
var \$form = $('#form-calling-config');
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
JS
);
?>