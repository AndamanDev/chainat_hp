<?php

use homer\assets\DatatablesAsset;
use homer\assets\jPlayerAsset;
use homer\assets\SocketIOAsset;
use homer\assets\ToastrAsset;
use homer\widgets\Table;

ToastrAsset::register($this);
SocketIOAsset::register($this);
jPlayerAsset::register($this);
$bundle = DatatablesAsset::register($this);
$bundle->css[] = 'ext/buttons/css/buttons.dataTables.min.css';
$bundle->js[] = 'ext/buttons/js/dataTables.buttons.min.js';
$bundle->js[] = 'ext/buttons/js/buttons.print.min.js';
$bundle->js[] = 'ext/buttons/js/buttons.html5.min.js';
$bundle->js[] = 'ext/buttons/js/pdfmake.min.js';
$bundle->js[] = 'ext/buttons/js/vfs_fonts.js';
$bundle->js[] = 'ext/buttons/js/jszip.min.js';
$bundle->js[] = 'ext/buttons/js/buttons.flash.min.js';
$bundle->js[] = 'ext/buttons/js/buttons.colVis.min.js';


$this->title = 'เรียกคิว';

$this->registerCssFile("@web/css/materialdesignicons.min.css", [
  'depends' => [\yii\bootstrap\BootstrapAsset::class],
]);
$this->registerCssFile("@web/css/vuetify.min.css", [
  'depends' => [\yii\bootstrap\BootstrapAsset::class],
]);
$this->registerCss(
  <<<CSS
html.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown), body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
    overflow-y: unset !important;
}
.v-text-field--outlined.v-input--dense .v-label {
  top: 15px;
}
.v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
  margin-top: 10px;
}
.counter-content {
    background: rgb(239, 247, 248);
}
.p-3 {
    padding: 1rem!important;
}
div.dataTables_wrapper div.dataTables_filter{
    text-align: left;
}
.justify-content-end {
  justify-content: end !important;
}
select[name="tb-waiting_length"] {
  line-height: normal;
}
select[name="tb-calling_length"] {
  line-height: normal;
}
select[name="tb-hold_length"] {
  line-height: normal;
}
.justify-content-center {
  justify-content: center !important;
}
.justify-content-between {
  justify-content: space-between !important;
}
.align-items-center {
  align-items: center !important;
}
.swal2-select {
  border-radius: 5px;
  border: 1px solid;
}
.btn.disabled, .btn[disabled], fieldset[disabled] .btn {
  background: #f8f9fa;
}
@keyframes blink {
	50% {
		opacity: 0;
	}
}

.blink {
	animation: blink 1s step-start 0s infinite;
}
CSS
);
?>

<div id="app">
  <v-app>
    <v-main>
      <v-container fluid>
        <div class="row">
          <div class="col-md-4 pb-0">
            <v-autocomplete @change="onChangeServiceProfile" :value="form.service_profile_id" @input="updateProfileInput" :loading="loading_profile" :items="service_profiles" outlined dense clearable rounded item-text="service_name" item-value="service_profile_id" label="โปรไฟล์เรียกคิว" height="50" persistent-hint hint=""></v-autocomplete>
          </div>
          <div class="col-md-4 pb-0">
            <v-autocomplete @change="onChangeService" multiple :value="form.service_ids" @input="updateServiceInput" :items="services" outlined dense clearable rounded item-text="service_name" item-value="serviceid" label="งานบริการ" placeholder="งานบริการทั้งหมด" height="50" persistent-hint></v-autocomplete>
          </div>
          <div class="col-md-4 pb-0">
            <v-autocomplete @change="onChangeCounter"  multiple :value="form.counter_service_ids" @input="updateCounterInput" :items="counterServiceOpts" outlined dense clearable rounded item-text="counterservice_name" item-value="counterserviceid" label="ช่องบริการ" placeholder="ช่องบริการทั้งหมด" height="50" persistent-hint hint="หากไม่ได้เลือกจะแสดงช่องบริการทั้งหมด"></v-autocomplete>
          </div>
        </div>

        <div class="row mb-0">
          <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="hpanel">
              <ul class="nav nav-tabs" style="padding-left: 0;">
                <li class="active"><a data-toggle="tab" href="#tab-1"> คิวรอ (<span id="total-wait" class="text-primary font-bold">0</span>)</a></li>
                <li class=""><a data-toggle="tab" href="#tab-2">คิวกำลังเรียก (<span id="total-calling" class="text-primary font-bold">0</span>)</a></li>
                <li class=""><a data-toggle="tab" href="#tab-3">คิวพัก (<span id="total-hold" class="text-primary font-bold">0</span>)</a></li>
              </ul>
              <div class="tab-content">
                <div id="tab-1" class="tab-pane active">
                  <div class="panel-body">
                    <?php
                    echo Table::widget([
                      'tableOptions' => ['class' => 'table table-hover table-bordered table-condensed', 'width' => '100%', 'id' => 'tb-waiting'],
                      //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
                      'beforeHeader' => [
                        [
                          'columns' => [
                            ['content' => '#', 'options' => ['style' => 'text-align: center;width: 35px;']],
                            ['content' => 'คิว', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'HN', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'QN', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ชื่อ', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ประเภท', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'สถานะ', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'วันที่', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'เวลา', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ดำเนินการ', 'options' => ['style' => 'text-align: center;width: 35px;']],
                          ],
                          // 'options' => ['style' => 'background-color:cornsilk;'],
                        ]
                      ],
                    ]);
                    ?>
                  </div>
                </div>
                <div id="tab-2" class="tab-pane">
                  <div class="panel-body">
                    <?php
                    echo Table::widget([
                      'tableOptions' => ['class' => 'table table-striped table-hover table-bordered table-condensed', 'width' => '100%', 'id' => 'tb-calling'],
                      //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
                      'beforeHeader' => [
                        [
                          'columns' => [
                            ['content' => '#', 'options' => ['style' => 'text-align: center;width: 35px;']],
                            ['content' => 'คิว', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'HN', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'QN', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ชื่อ', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ประเภท', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ห้อง/ช่อง/โต๊ะ', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'เวลาเรียกคิว', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ดำเนินการ', 'options' => ['style' => 'text-align: center;width:200px;']],
                          ],
                          // 'options' => ['style' => 'background-color:cornsilk;'],
                        ]
                      ],
                    ]);
                    ?>
                  </div>
                </div>
                <div id="tab-3" class="tab-pane">
                  <div class="panel-body">
                    <?php
                    echo Table::widget([
                      'tableOptions' => ['class' => 'table table-striped table-hover table-bordered table-condensed', 'width' => '100%', 'id' => 'tb-hold'],
                      //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
                      'beforeHeader' => [
                        [
                          'columns' => [
                            ['content' => '#', 'options' => ['style' => 'text-align: center;width: 35px;']],
                            ['content' => 'คิว', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'HN', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'QN', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ชื่อ', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ประเภท', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'จุดบริการ', 'options' => ['style' => 'text-align: center;']],
                            // ['content' => 'สถานะ','options' => ['style' => 'text-align: center;']],
                            ['content' => 'วันที่', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'เวลา', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'เวลาพักคิว', 'options' => ['style' => 'text-align: center;']],
                            ['content' => 'ดำเนินการ', 'options' => ['style' => 'text-align: center;width:150px;']],
                          ],
                          // 'options' => ['style' => 'background-color:cornsilk;'],
                        ]
                      ],
                    ]);
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="iq-card-body p-3 counter-content " style="border-radius: 25px;">
              <div class="row">
                <template v-for="(item, key) in filteredCounters">
                  <div :key="key" class="col-md-6 col-lg-6 col-sm-12">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height animate__animated animate__zoomIn animate__faster" :style="{borderRadius: '0.5rem', 'box-shadow': '0px 2px 5px rgb(3 18 26 / 15%)', background: item.data.caller_ids ? '#b2ebf2' : '#fff' }">
                      <div :id="`counter-card-${item.counterserviceid}`" class="iq-card-body counter-card p-3" :style="{ background: '#fff', borderRadius: '0.5rem', background: item.data.caller_ids ? '#b2ebf2' : '#fff' }">

                        <div class="d-flex justify-content-between">
                          <small class="d-flex justify-content-center align-items-center">
                            {{ item.counterservice_name }}
                          </small>
                          <!-- <h4 class="text-center font-weight-bold" style="color: #777b7f !important;">
                            {{ item.counterservice_callnumber }}
                          </h4> -->
                          <!-- <h4 class="text-center font-weight-bold" style="color: #777b7f !important;">
                            {{ item.counterservice_name }}
                          </h4> -->
                        </div>
                        <div class="iq-card mb-1" style="border: 1px solid rgb(206, 235, 238); border-radius: 0.5rem;background: #fff;">
                          <div class="iq-card-body p-1">
                            <div class="row">
                              <div class="col-6 text-center">
                                <h3 :id="`queue-${item.data.q_num}`">
                                  {{ item.data.q_num }}
                                </h3>
                              </div>
                              <div class="col-6 text-center m-auto">
                                <h3>
                                  {{ item.counterservice_callnumber }}
                                </h3>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- <small class="d-flex">ชื่อผู้บริการ: -</small> -->
                        <span class="customer d-flex">ชื่อผู้ป่วย: {{ item.data.pt_name }}</span>
                        <div class="row">
                          <div class="col-md-6">
                            <button @click="onCall(item)" :disabled="item.disabled_btn_call" data-toggle="tooltip" data-placement="top" type="button" title="เรียกคิว" :class="['btn btn-block', 'btn-info' ]">
                              เรียกคิว
                            </button>
                          </div>
                          <div class="col-md-6">
                            <button @click="onHold(item)" :disabled="item.disabled_btn_hold" data-toggle="tooltip" data-placement="top" type="button" title="พักคิว" :class="['btn btn-block', 'btn-warning', { 'btn-outline': !item.data.caller_ids }]">
                              พักคิว
                            </button>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <button @click="onSendToDoctor(item)" v-show="item.show_btn_doctor" data-toggle="tooltip" data-placement="top" :disabled="item.disabled_btn_doctor" type="button" title="ส่งห้องแพทย์" :class="['btn btn-block', 'btn-primary', { 'btn-outline': !item.data.caller_ids } ]">
                              ส่งห้องแพทย์
                            </button>
                            <button @click="onSendToPharmacy(item)" v-show="item.show_btn_pharmacy" data-toggle="tooltip" data-placement="top" :disabled="item.disabled_btn_doctor" type="button" title="ส่งห้องแพทย์" :class="['btn btn-block', 'btn-primary', { 'btn-outline': !item.data.caller_ids } ]">
                              ส่งห้องยา
                            </button>
                          </div>
                          <div class="col-md-6">
                            <button @click="onFinish(item)" :disabled="item.disabled_btn_finish" data-toggle="tooltip" data-placement="top" type="button" :class="['btn btn-block', 'btn-success', { 'btn-outline': !item.data.caller_ids } ]" title="เสร็จสิ้น">
                              เสร็จสิ้น
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </v-container>
    </v-main>
  </v-app>
</div>

<!-- jPlayer -->
<div id="jplayer_notify"></div>

<?php
$this->registerJsFile(
  YII_ENV_DEV ? '@web/js/vue.js' : '@web/js/vue.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile(
  '@web/js/vuetify.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->registerJsFile(
  '@web/js/lodash.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->registerJsFile(
  '@web/js/vuex-persistedstate.umd.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile(
  '@web/js/vuex.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile(
  '@web/vendor/moment/min/moment-with-locales.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile(
  '@web/js/axios.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile(
  '@web/js/sweetalert2.all.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJs($this->render('app.js'));
?>