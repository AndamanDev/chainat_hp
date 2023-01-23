<?php

use homer\duallistbox\DualListbox;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\icons\Icon;

$this->registerCss('
.modal-dialog{
	width: 90%;
}
.modal-header{
	padding: 10px;
}
');
?>

<?php $form = ActiveForm::begin([
  'id' => 'form-sound-station', 'type' => ActiveForm::TYPE_HORIZONTAL,
  'formConfig' => ['showLabels' => false],
]); ?>
<div class="form-group">
  <?= Html::activeLabel($model, 'sound_station_name', ['label' => 'ชื่อ', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
    <?= $form->field($model, 'sound_station_name', ['showLabels' => false])->textInput([]); ?>
  </div>
</div>

<div class="form-group">
  <?= Html::activeLabel($model, 'counterserviceid', ['label' => 'ช่องบริการที่ต้องการเล่นเสียง', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-10">
    <?php
    echo $form->field($model, 'counterserviceid')->widget(DualListbox::className(), [
      'items' => ArrayHelper::map((new \yii\db\Query())
        ->select(['tb_counterservice.counterserviceid', 'concat(tb_counterservice_type.counterservice_type, \' : \', tb_counterservice.counterservice_name) as counterservice_name'])
        ->from('tb_counterservice')
        ->innerJoin('tb_counterservice_type', 'tb_counterservice_type.counterservice_typeid = tb_counterservice.counterservice_type')
        ->where(['tb_counterservice.counterservice_status' => 1])
        ->all(), 'counterserviceid', 'counterservice_name'),
      'options' => [
        'multiple' => true,
        'size' => 20,
      ],
      'clientOptions' => [
        'moveOnSelect' => false,
        'selectorMinimalHeight' => 200,
        'selectedListLabel' => 'รายการที่เลือก',
        'nonSelectedListLabel' => 'รายการทั้งหมด'
      ],
    ]);
    ?>
  </div>
</div>

<div class="form-group">
  <?= Html::activeLabel($model, 'sound_station_status', ['label' => 'สถานะ', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
  <?php
    echo $form->field($model, 'sound_station_status')->widget(SwitchInput::classname(), [
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
var table = $('#tb-sound-station').DataTable();
var \$form = $('#form-sound-station');
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