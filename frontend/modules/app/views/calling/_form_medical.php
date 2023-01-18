<?php

use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use frontend\modules\app\models\TbServiceProfile;
use kartik\depdrop\DepDrop;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="hpanel panel-form" style="margin-bottom: 10px;">
  <div class="panel-heading">
    <div class="panel-tools">
      <a class="showhide"><i class="fa fa-chevron-up"></i></a>
    </div>
    <div class="checkbox" style="display: inline-block;margin-bottom: 0px;">
      <label>
        <input type="checkbox" value="0" name="tablet-mode" id="tablet-mode">
        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
        <i class="pe-7s-phone"></i> Tablet Mode
      </label>
    </div>
    <div class="checkbox" style="display: inline-block;margin-bottom: 0px;">
      <label>
        <input type="checkbox" value="0" name="tablet-mode" id="fullscreen-toggler">
        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
        <i class="pe-7s-expand1"></i> Fullscreen
      </label>
    </div>
    <span class="panel-heading-text" style="font-size: 18px;">&nbsp</span>
  </div>
  <div class="panel-body" style="border: 1.5px dashed lightgrey;padding-left: 10px;padding-bottom: 0px;padding-top: 0px;">
    <?php
    $form = ActiveForm::begin([
      'id' => 'calling-form',
      'type' => 'horizontal',
      'options' => ['autocomplete' => 'off'],
      'formConfig' => ['showLabels' => false],
    ]) ?>

    <div class="form-group" style="margin-bottom: 0px;margin-top: 15px;">
      <div class="col-md-4 service_profile">
        <?=
        $form->field($modelForm, 'service_profile')->widget(Select2::classname(), [
          'data' => ArrayHelper::map(Yii::$app->db->createCommand('SELECT
                    tb_service_profile.*,
                    ( SELECT COUNT( tb_service.serviceid ) FROM tb_service WHERE tb_service.serviceid IN ( tb_service_profile.service_id ) AND tb_service.service_type_id in (1, 3, 4) ) AS a 
                FROM
                    tb_service_profile 
                HAVING
                    a > 0')
            ->queryAll(), 'service_profile_id', 'service_name'),
          'options' => ['placeholder' => 'เลือกโปรไฟล์...'],
          'pluginOptions' => [
            'allowClear' => true
          ],
          'theme' => Select2::THEME_BOOTSTRAP,
          //'size' => Select2::LARGE,
          'pluginEvents' => [
          ]
        ]);
        ?>
      </div>

      <div class="col-md-4 counter_service">
        <?=
        $form->field($modelForm, 'counter_service')->widget(DepDrop::classname(), [
          'type' => DepDrop::TYPE_SELECT2,
          'data' => $modelForm->dataCounter,
          'options' => ['placeholder' => 'เลือก...'],
          'select2Options' => [
            'pluginOptions' => ['allowClear' => true],
            'theme' => Select2::THEME_BOOTSTRAP,
          ],
          'pluginOptions' => [
            'depends' => ['callingform-service_profile', 'counter_service_id'],
            'placeholder' => 'Select...',
            'url' => Url::to(['/app/calling/counters'])
          ],
          //'size' => Select2::LARGE,
          // 'pluginEvents' => [
          //   "change" => "function() {
          //                   if($(this).val() != '' && $(this).val() != null){
          //                       location.replace(baseUrl + \"/app/calling/medical?profileid=\" + " . Json::encode($modelForm['service_profile']) . " + \"&counterid=\" + $(this).val());
          //                   }else{
          //                       location.replace(baseUrl + \"/app/calling/medical?profileid=\" + " . Json::encode($modelForm['service_profile']) . ");
          //                   }
          //               }",
          // ]
        ]);
        ?>
      </div>
      <div class="col-md-4">
        <?= $form->field($modelForm, 'qnum')->textInput([
          //'class' => 'input-lg',
          'placeholder' => 'คีย์หมายเลขคิวที่นี่เพื่อเรียก',
          'style' => 'background-color: #434a54;color: white;',
          'autofocus' => true
        ])->hint(''); ?>
      </div>
    </div>
    <div class="form-group" style="margin-bottom: 5px;">
      <div class="col-md-8" style="padding-bottom: 10px;">
        <?= $modelForm->serviceList; ?>
      </div>
      <div class="col-md-4">
        <p>
          <?= Html::a('CALL NEXT', false, ['class' => 'btn btn-lg btn-block btn-primary activity-callnext', 'data-url' => '/app/calling/call-screening-room']); ?>
        </p>
      </div>
    </div>

    <?php ActiveForm::end() ?>
  </div><!-- End panel body -->
</div><!-- End hpanel -->
<div class="form-group call-next-tablet-mode" style="margin-bottom: 5px;display: none">
  <div class="col-md-4" style="padding-bottom: 5px;">
  </div>
  <div class="col-md-4">
    <p>
      <?= Html::button(\yii\icons\Icon::show('check-square-o') . ' เรียกคิวที่เลือก <span class="count-selected">(0)</span>', ['class' => 'btn btn-lg btn-block btn-success on-call-selected', 'data-url' => '/app/calling/call-sr-selected', 'disabled' => true]); ?>
    </p>
  </div>
  <div class="col-md-4">
    <p>
      <?= Html::a('CALL NEXT', false, ['class' => 'btn btn-lg btn-block btn-primary activity-callnext', 'data-url' => '/app/calling/call-screening-room']); ?>
    </p>
  </div>
</div>
<input type="hidden" name="counter_service_id" id="counter_service_id">