<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\icons\Icon;
use kartik\widgets\Select2;
use frontend\modules\app\models\TbService;
use frontend\modules\app\models\TbCounterserviceType;
use homer\duallistbox\DualListbox;
use yii\helpers\ArrayHelper;
use kartik\widgets\ColorInput;
use kartik\widgets\SwitchInput;

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
  'id' => 'form-display', 'type' => ActiveForm::TYPE_HORIZONTAL,
  'formConfig' => ['showLabels' => false],
]); ?>
<div class="form-group">
  <?= Html::activeLabel($model, 'display_name', ['class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
    <?= $form->field($model, 'display_name', ['showLabels' => false])->textInput([]); ?>
  </div>
</div>

<div class="form-group">
  <?= Html::activeLabel($model, 'sound_station_id', ['label' => 'โปรแกรมเล่นเสียง', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
    <?= $form->field($model, 'sound_station_id', ['showLabels' => false])->widget(Select2::classname(), [
      'data' => ArrayHelper::map((new \yii\db\Query())
        ->select([
          'tb_sound_station.sound_station_id',
          'tb_sound_station.sound_station_name',
          'tb_sound_station.counterserviceid',
          'tb_sound_station.sound_station_status'
        ])
        ->from('tb_sound_station')
        ->all(), 'sound_station_id', 'sound_station_name'),
      'options' => ['placeholder' => 'เลือกเครื่องเล่นเสียง...'],
      'pluginOptions' => [
        'allowClear' => true
      ],
      'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>
  </div>
</div>

<?php /*

	<div class="form-group">
	    <?= Html::activeLabel($model, 'title_left', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'title_left',['showLabels'=>false])->textInput([]); ?>
	    </div>

	    <?= Html::activeLabel($model, 'title_right', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'title_right',['showLabels'=>false])->textInput([]); ?>
	    </div>
	</div>

	<div class="form-group">
	    <?= Html::activeLabel($model, 'table_title_left', ['label' => 'Header Left','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'table_title_left',['showLabels'=>false])->textInput([]); ?>
	    </div>

	    <?= Html::activeLabel($model, 'table_title_right', ['label' => 'Header Right','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'table_title_right',['showLabels'=>false])->textInput([]); ?>
	    </div>
	</div>

	<div class="form-group">
	    <?= Html::activeLabel($model, 'display_limit', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'display_limit',['showLabels'=>false])->textInput([]); ?>
	    </div>

	    <?= Html::activeLabel($model, 'hold_label', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'hold_label',['showLabels'=>false])->textInput([]); ?>
	    </div>
	</div>

	<div class="form-group">
	    <?= Html::activeLabel($model, 'header_color', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'header_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

	    <?= Html::activeLabel($model, 'column_color', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'column_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

	<div class="form-group">
	    <?= Html::activeLabel($model, 'background_color', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'background_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

	    <?= Html::activeLabel($model, 'font_color', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'font_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

	<div class="form-group">
	    <?= Html::activeLabel($model, 'border_color', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'border_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

	    <?= Html::activeLabel($model, 'title_color', ['class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'title_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>
	<div class="form-group">
	    <?= Html::activeLabel($model, 'text_marquee', ['label' => 'ข้อความวิ่ง','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-10">
            <?= $form->field($model, 'text_marquee',['showLabels'=>false])->textInput() ?>
        </div>
	</div>
	*/ ?>
<?php
$model->lab_display = $model->isNewRecord ? 0 : $model['lab_display'];
?>

<?php /*
<div class="form-group">
  <?= Html::activeLabel($model, 'lab_display', ['class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-2">
    <?= $form->field($model, 'lab_display', ['showLabels' => false])->RadioList(
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
*/ ?>

<div class="form-group">
  <?= Html::activeLabel($model, 'pt_name', ['label' => 'แสดงชื่อผู้ป่วย', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-2">
  <?php
    echo $form->field($model, 'pt_name')->widget(SwitchInput::classname(), [
      'pluginOptions' => [
        // 'size' => 'mini',
        'onColor' => 'success',
        'offColor' => 'danger',
        'onText' => 'แสดง',
        'offText' => 'ไม่แสดง',
    ]
    ]);
    ?>
    <?php /*
    <?= $form->field($model, 'pt_name', ['showLabels' => false])->RadioList(
      [
        0 => 'ไม่แสดง',
        1 => 'แสดง'
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
    ); ?>
    */ ?>
  </div>

  <?= Html::activeLabel($model, 'pt_pic', ['label' => 'แสดงภาพผู้ป่วย', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-2">
  <?php
    echo $form->field($model, 'pt_pic')->widget(SwitchInput::classname(), [
      'pluginOptions' => [
        // 'size' => 'mini',
        'onColor' => 'success',
        'offColor' => 'danger',
        'onText' => 'แสดง',
        'offText' => 'ไม่แสดง',
    ]
    ]);
    ?>
    <?php /*
    <?= $form->field($model, 'pt_pic', ['showLabels' => false])->RadioList(
      [
        0 => 'ไม่แสดง',
        1 => 'แสดง'
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
    ); ?>
    */ ?>
  </div>
</div>

<div class="form-group">
  <?= Html::activeLabel($model, 'display_limit', ['label' => 'จำนวนแถวที่แสดง', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-2">
    <?= $form->field($model, 'display_limit', ['showLabels' => false])->textInput([]); ?>
  </div>
</div>

<div class="form-group">
  <?= Html::activeLabel($model, 'counterservice_id', ['label' => 'ช่องบริการที่ต้องการแสดง', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-8">
    <?php
    echo $form->field($model, 'counterservice_id')->widget(DualListbox::className(), [
      'items' => ArrayHelper::map(TbCounterserviceType::find()->asArray()->all(), 'counterservice_typeid', 'counterservice_type'),
      'options' => [
        'multiple' => true,
        'size' => 20,
      ],
      'clientOptions' => [
        'moveOnSelect' => false,
        'selectorMinimalHeight' => 200,
        'selectedListLabel' => 'รายการที่แสดงผล',
        'nonSelectedListLabel' => 'รายการทั้งหมด'
      ],
    ]);
    ?>
    <?php /*
    <?= $form->field($model, 'counterservice_id', ['showLabels' => false])->checkBoxList(
      ArrayHelper::map(TbCounterserviceType::find()->asArray()->all(), 'counterservice_typeid', 'counterservice_type'),
      [
        'inline' => false,
        'item' => function ($index, $label, $name, $checked, $value) {

          $return = '<div class="checkbox"><label style="font-size: 1em">';
          $return .= Html::checkbox($name, $checked, ['value' => $value]);
          $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
          $return .= '</label></div>';

          return $return;
        }
      ]
    ); ?>
    */ ?>
  </div>
</div>
<div class="form-group">
  <?= Html::activeLabel($model, 'service_id', ['label' => 'กลุ่มบริการที่ต้องการแสดง', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-10">
  <?php
    echo $form->field($model, 'service_id')->widget(DualListbox::className(), [
      'items' => ArrayHelper::map((new \yii\db\Query())
      ->select(['tb_service.serviceid', 'CONCAT(tb_service.service_prefix,\': \', tb_service.service_name)  as service_name'])
      ->from('tb_service')
      ->where(['service_status' => 1])
      ->all(), 'serviceid', 'service_name'),
      'options' => [
        'multiple' => true,
        'size' => 20,
      ],
      'clientOptions' => [
        'moveOnSelect' => false,
        'selectorMinimalHeight' => 200,
        'selectedListLabel' => 'รายการที่แสดงผล',
        'nonSelectedListLabel' => 'รายการทั้งหมด'
      ],
    ]);
    ?>
    <?php /*
    <?= $form->field($model, 'service_id', ['showLabels' => false])->checkBoxList(
      ArrayHelper::map((new \yii\db\Query())
        ->select(['tb_service.serviceid', 'CONCAT(tb_service.service_prefix,\': \', tb_service.service_name)  as service_name'])
        ->from('tb_service')
        ->where(['service_status' => 1])
        ->all(), 'serviceid', 'service_name'),
      [
        'inline' => false,
        'item' => function ($index, $label, $name, $checked, $value) {

          $return = '<div class="checkbox"><label style="font-size: 1em">';
          $return .= Html::checkbox($name, $checked, ['value' => $value]);
          $return .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>' . ucwords($label);
          $return .= '</label></div>';

          return $return;
        }
      ]
    ); ?>
    */ ?>
  </div>
</div>

<div class="form-group">
  <?= Html::activeLabel($model, 'display_status', ['label' => 'สถานะ', 'class' => 'col-sm-2 control-label']) ?>
  <div class="col-sm-4">
    <?php
    echo $form->field($model, 'display_status')->widget(SwitchInput::classname(), [
      'pluginOptions' => [
        // 'size' => 'mini',
        'onColor' => 'success',
        'offColor' => 'danger',
        'onText' => 'ใช้งาน',
        'offText' => 'ปิดใช้งาน',
    ]
    ]);
    ?>
    <?php /*

    <?= $form->field($model, 'display_status', ['showLabels' => false])->RadioList(
      [0 => 'Disabled', 1 => 'Enabled'],
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
    */ ?>
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
var table = $('#tb-display').DataTable();
var \$form = $('#form-display');
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

// Initialize iCheck plugin
/* $('.i-checks').iCheck({
    checkboxClass: 'icheckbox_square-green',
    radioClass: 'iradio_square-green'
}); */

JS
);
?>