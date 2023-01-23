<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\icons\Icon;
use kartik\widgets\Select2;
use frontend\modules\app\models\TbService;
use frontend\modules\app\models\TbCounterserviceType;
use yii\helpers\ArrayHelper;
use kartik\widgets\ColorInput;
use homer\assets\SweetAlert2Asset;


SweetAlert2Asset::register($this);
$this->registerCss('
.modal-dialog{
	width: 90%;
}
.modal-header{
	padding: 10px;
}
.padding-v-sm {
    padding-top: 20px;
}
.line-dashed {
    background-color: transparent;
    border-bottom: 1px dashed #dee5e7 !important;
}
');
$this->registerCss($this->render('./css/display.css'));
$this->registerCss('
    table.table-display thead tr {
        width: 50%;
        border-radius: 15px;
        border: 5px solid white;
        background-color: '.$model['header_color'].';
        color: '.$model['font_color'].';
        font-weight: bold;
    }
    table.table-display2 thead tr {
        width: 50%;
        border-radius: 15px;
        border: 5px solid white;
        background-color: '.$model['header_latest_color'].';
        color: '.$model['title_latest_right_color'].';
        font-weight: bold;
    }
    table.table-display tbody tr {
        width: 50%;
        border-radius: 15px;
        border: 5px solid white;
        background-color: '.$model['column_color'].';
        color: '.$model['font_cell_display_color'].';
        font-weight: bold;
    }
    table.table-display2 tbody tr {
        width: 50%;
        border-radius: 15px;
        border: 5px solid white;
        background-color: '.$model['cell_latest_color'].';
        color: '.$model['font_cell_latest_color'].';
        font-weight: bold;
    }
    table.table-hold tbody tr td.td-hold-left{
        width: 50%;
        border-top: 5px solid '.$model['hold_border_color'].' !important;
        border-bottom: 5px solid '.$model['hold_border_color'].' !important;
        border-right: 5px solid '.$model['hold_border_color'].' !important;
        border-left: 5px solid '.$model['hold_border_color'].' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        background-color: '.$model['hold_bg_color'].';
        color: '.$model['hold_font_color'].';
        vertical-align: middle;
    }
    table.table-display tbody tr td.td-left{
        border-top: 5px solid '.$model['border_color'].' !important;
        border-bottom: 5px solid '.$model['border_color'].' !important;
        border-left: 5px solid '.$model['border_color'].' !important;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    table.table-display tbody tr td.td-right{
        border-top: 5px solid '.$model['border_color'].' !important;
        border-bottom: 5px solid '.$model['border_color'].' !important;
        border-right: 5px solid '.$model['border_color'].' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    table.table-display thead tr th.th-right{
        border-top: 5px solid '.$model['border_color'].' !important;
        border-bottom: 5px solid '.$model['border_color'].' !important;
        border-right: 5px solid '.$model['border_color'].' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    table.table-display thead tr th.th-left{
        border-top: 5px solid '.$model['border_color'].' !important;
        border-bottom: 5px solid '.$model['border_color'].' !important;
        border-left: 5px solid '.$model['border_color'].' !important;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    /*  */
    table.table-display2 tbody tr td.td-left{
        border-top: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-bottom: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-left: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    table.table-display2 tbody tr td.td-right{
        border-top: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-bottom: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-right: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    table.table-display2 thead tr th.th-right{
        border-top: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-bottom: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-right: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    table.table-display2 thead tr th.th-left{
        border-top: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-bottom: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-left: 5px solid '.$model['border_cell_latest_color'].' !important;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
');

$this->title = 'ตั้งค่าจอแสดงผล';
$bundle = \homer\assets\HomerAdminAsset::register($this);
$bundle->js[] = 'vendor/iCheck/icheck.min.js';
?>
<div class="container" style="background-color:<?= $model['background_color'] ?>">
	<div class="row">
	    <div class="col-xs-4 col-sm-4 col-md-4 border-right" style="text-align: center;">
	        <h1 class="text-success" style="color: <?= $model['title_left_color']; ?>"><?= $model['title_left'] ?></h1>
	    </div>
	    <div class="col-xs-4 col-sm-4 col-md-4 border-right" style="text-align: center;">
	        <h1 class="text-success" style="color: <?= $model['title_right_color']; ?>"><?= $model['title_right'] ?></h1>
	    </div>
        <div class="col-xs-4 col-sm-4 col-md-4" style="text-align: center;">
	        <h1 class="text-success" style="color: <?= $model['title_latest_color']; ?>"><?= $model['title_latest'] ?></h1>
	    </div>
	</div>
	<div class="row">
	    <div class="col-xs-8 col-sm-8 col-md-8">
	        <table class="table table-display" id="table-display" width="100%"> 
	        	<thead> 
	        		<tr> 
	        			<th style="width: 50%;color: <?= $model['table_title_left_color']; ?>" class="th-left"><?= $model['table_title_left'] ?></th> 
	        			<th style="width: 50%;color: <?= $model['table_title_right_color']; ?>" class="th-right"><?= $model['table_title_right'] ?></th>
	        		</tr> 
	        	</thead> 
	        	<tbody>
                    <tr>
                        <td class="td-left">1001</td>
                        <td class="td-right">1</td>
                    </tr>
	        	</tbody> 
	        </table>
	    </div>
        <div class="col-xs-4 col-sm-4 col-md-4">
            <table class="table table-display2" id="table-display2" width="100%"> 
	        	<thead> 
	        		<tr> 
	        			<th style="width: 50%;color: <?= $model['title_latest_right_color']; ?>" class="th-left">#</th>
                        <th style="width: 50%;color: <?= $model['title_latest_right_color']; ?>" class="th-right"><?= $model['title_latest_right'];?></th>
	        		</tr> 
	        	</thead> 
	        	<tbody>
                    <tr>
                        <td class="td-left">1</td>
                        <td class="td-right">1001</td>
                    </tr>
	        	</tbody> 
	        </table>
        </div>
	</div>
    <div class="row">
	    <div class="col-xs-12 col-sm-12 col-md-12">
            <table class="table table-hold" id="table-hold" width="100%"> 
	        	<tbody> 
	        		<tr> 
	        			<td class="td-hold-left"><?= $model['hold_label'] ?></td> 
	        			<td class="td-hold-right"></td>
	        		</tr>
	        	</tbody> 
	        </table>
        </div>
    </div>
    <?php if(!empty($model['text_marquee'])): ?>
    <div class="row">
	    <div class="col-xs-12 col-sm-12 col-md-12">
            <marquee id="marquee" style="color: <?= $model['font_marquee_color'] ?>;" direction="left"><?= $model['text_marquee'] ?></marquee>
        </div>
    </div>
    <?php endif; ?>
</div>
<hr>
<?php $form = ActiveForm::begin([
    'id' => 'form-display', 'type' => ActiveForm::TYPE_HORIZONTAL, 
    'formConfig' => ['showLabels' => false],
]);?>
    <div class="form-group">
	    <div class="col-sm-2 col-sm-offset-2">
            <h3>ส่วนหัว</h3>
	    </div>
    </div>
    
	<div class="form-group">
	    <?= Html::activeLabel($model, 'title_left', ['label' => 'ข้อความ 1','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'title_left',['showLabels'=>false])->textInput([]); ?>
	    </div>

	    <?= Html::activeLabel($model, 'title_right', ['label' => 'ข้อความ 2','class'=>'col-sm-1 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'title_right',['showLabels'=>false])->textInput([]); ?>
	    </div>

        <?= Html::activeLabel($model, 'title_latest', ['label' => 'ข้อความ 3','class'=>'col-sm-1 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'title_latest',['showLabels'=>false])->textInput([]); ?>
	    </div>
	</div>

    <div class="form-group">
        <?= Html::activeLabel($model, 'title_left_color', [ 'label' => 'สีตัวอักษร','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'title_left_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

        <?= Html::activeLabel($model, 'title_right_color', [ 'label' => 'สีตัวอักษร','class'=>'col-sm-1 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'title_right_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

        <?= Html::activeLabel($model, 'title_latest_color', [ 'label' => 'สีตัวอักษร','class'=>'col-sm-1 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'title_latest_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

    <div class="padding-v-sm">
        <div class="line line-dashed"></div>
    </div>

    <div class="form-group">
	    <div class="col-sm-2 col-sm-offset-2">
            <h3>ข้อความส่วนหัวตาราง</h3>
	    </div>
    </div>
	<div class="form-group">
	    <?= Html::activeLabel($model, 'table_title_left', ['label' => 'ข้อความ 1','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'table_title_left',['showLabels'=>false])->textInput([]); ?>
	    </div>

	    <?= Html::activeLabel($model, 'table_title_right', ['label' => 'ข้อความ 2','class'=>'col-sm-1 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'table_title_right',['showLabels'=>false])->textInput([]); ?>
	    </div>

        <?= Html::activeLabel($model, 'title_latest_right', ['label'=>'ข้อความ 3','class'=>'col-sm-1 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'title_latest_right',['showLabels'=>false])->textInput([]); ?>
	    </div>
	</div>

    <div class="form-group">
        <?= Html::activeLabel($model, 'table_title_left_color', [ 'label' => 'สีตัวอักษร','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'table_title_left_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

        <?= Html::activeLabel($model, 'table_title_right_color', [ 'label' => 'สีตัวอักษร','class'=>'col-sm-1 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'table_title_right_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

        <?= Html::activeLabel($model, 'title_latest_right_color', [ 'label' => 'สีตัวอักษร','class'=>'col-sm-1 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'title_latest_right_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

    <div class="padding-v-sm">
        <div class="line line-dashed"></div>
    </div>

    <div class="form-group">
	    <div class="col-sm-2 col-sm-offset-2">
            <h3>สีตารางแสดงผล</h3>
	    </div>
    </div>

	<div class="form-group">
	    <?= Html::activeLabel($model, 'header_color', ['label'=>'สีพื้นหลังส่วนหัว','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'header_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

	    <?= Html::activeLabel($model, 'column_color', ['label'=>'สีพื้นหลังแถว','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'column_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

    <div class="form-group">
	    <?= Html::activeLabel($model, 'border_color', ['label'=>'สีเส้นขอบตาราง','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'border_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

        <?= Html::activeLabel($model, 'font_cell_display_color', ['label'=>'สีตัวอักษรแถว','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'font_cell_display_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

    <div class="padding-v-sm">
        <div class="line line-dashed"></div>
    </div>

    <div class="form-group">
	    <div class="col-sm-2 col-sm-offset-2">
            <h3>สีตารางคิวล่าสุด</h3>
	    </div>
    </div>

    <div class="form-group">
	    <?= Html::activeLabel($model, 'header_latest_color', ['label'=>'สีพื้นหลังส่วนหัว','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'header_latest_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

	    <?= Html::activeLabel($model, 'cell_latest_color', ['label'=>'สีพื้นหลังแถว','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'cell_latest_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

    <div class="form-group">
        <?= Html::activeLabel($model, 'border_cell_latest_color', ['label'=>'สีเส้นขอบตาราง','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'border_cell_latest_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

        <?= Html::activeLabel($model, 'font_cell_latest_color', ['label'=>'สีตัวอักษรแถว','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'font_cell_latest_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

    <div class="padding-v-sm">
        <div class="line line-dashed"></div>
    </div>

    <div class="form-group">
	    <div class="col-sm-4 col-sm-offset-2">
            <h3>สี/ข้อความ ตารางพักคิว</h3>
	    </div>
    </div>

    <div class="form-group">
	    <?= Html::activeLabel($model, 'hold_label', ['label'=>'ข้อความ','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'hold_label',['showLabels'=>false])->textInput([]); ?>
	    </div>

        <?= Html::activeLabel($model, 'hold_bg_color', ['label'=>'สีพื้นหลัง','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'hold_bg_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

    <div class="form-group">
        <?= Html::activeLabel($model, 'hold_font_color', ['label'=>'สีตัวอักษร','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'hold_font_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>

        <?= Html::activeLabel($model, 'hold_border_color', ['label'=>'สีเส้นขอบตาราง','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'hold_border_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
	</div>

    <div class="padding-v-sm">
        <div class="line line-dashed"></div>
    </div>

    <div class="form-group">
	    <div class="col-sm-2 col-sm-offset-2">
            <h3>สีพื้นหลังหน้าจอ</h3>
	    </div>
    </div>

    <div class="form-group">
	    <?= Html::activeLabel($model, 'background_color', ['label'=>'สี','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'background_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
    </div>

    <div class="padding-v-sm">
        <div class="line line-dashed"></div>
    </div>

    <div class="form-group">
	    <div class="col-sm-2 col-sm-offset-2">
            <h3>ข้อความวิ่ง</h3>
	    </div>
    </div>

	<div class="form-group">
	    <?= Html::activeLabel($model, 'text_marquee', ['label' => 'ข้อความวิ่ง','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-6">
            <?= $form->field($model, 'text_marquee',['showLabels'=>false])->textInput() ?>
        </div>
	</div>

    <div class="form-group">
        <?= Html::activeLabel($model, 'font_marquee_color', ['label'=>'สีตัวอักษร','class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-2">
	        <?= $form->field($model, 'font_marquee_color')->widget(ColorInput::classname(), [
			    'options' => ['placeholder' => 'Select color ...'],
			]) ?>
	    </div>
    </div>

	<div class="form-group">
        <div class="col-sm-8" style="text-align:right;">
            <?= Html::a('ปิด',['/app/settings/index'],['class' => 'btn btn-default']); ?>
            <?= Html::a('รีเซ็ต',['/app/settings/update-display','id' => $model['display_ids']],['class' => 'btn btn-danger']); ?>
            <?= Html::submitButton('ดูตัวอย่าง',['class' => 'btn btn-primary']); ?>
            <?= Html::button('บันทึก',['class' => 'btn btn-success btn-save']); ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>
<br>
<?php
$this->registerJs(<<<JS
$('button.btn-save').on('click',function(){
    var \$form = $('#form-display');
    var \$btn = $(this).button('loading');//loading btn
    var data = new FormData($(\$form)[0]);//\$form.serialize();
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
});
JS
);
?>