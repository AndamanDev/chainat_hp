<?php

use homer\widgets\highcharts\HighchartsAsset;
use homer\assets\SocketIOAsset;
use homer\assets\ToastrAsset;
use frontend\assets\Select2Asset;
use yii\helpers\Json;
use yii\web\View;

ToastrAsset::register($this);
SocketIOAsset::register($this);
Select2Asset::register($this);
$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@homer/widgets/highcharts/assets');
$bundle = HighchartsAsset::register($this);
$bundle->js[] = $directoryAsset . '/highcharts.js';
$bundle->js[] = $directoryAsset . '/modules/exporting.js';
$bundle->js[] = $directoryAsset . '/modules/drilldown.js';
$bundle->js[] = $directoryAsset . '/modules/export-data.js';

$this->title = 'หน้าหลัก';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(
  <<<CSS
  .swal2-popup {
    font-size: 1.6rem !important;
  }
CSS
);
$this->registerCssFile("@web/css/select2-bootstrap.min.css", [
  'depends' => [\yii\bootstrap\BootstrapAsset::class],
]);
$this->registerJs('var accesstoken = ' . Json::encode(Yii::$app->user->identity->getAuthKey()) . '; ', View::POS_HEAD);
?>

<div id="app">
  <div class="panel-body" style="background-color: #fff">
    <form>
      <div class="form-group row">
        <div class="col-md-6 col-md-offset-3">
          <label class="col-sm-2 control-label">งานบริการ</label>

          <div class="col-sm-10">
            <select id="serviceid" @change="onChangeServiceId" v-model="service_id" class="form-control m-b" name="account">
              <option value="">งานบริการทั้งหมด</option>
              <option v-for="(item, k) in services" :key="k" :value="item.serviceid">
                {{ item.service_name }}
              </option>
            </select>
          </div>
        </div>

      </div>
    </form>
  </div>
  <div class="panel-body" style="background-color: #fff">
    <div class="row">
      <div class="col-md-12 text-center">
        <span class="badge">ข้อมูล ณ วันที่ <?= Yii::$app->formatter->asDate('now', 'php:d/m/Y'); ?></span>
      </div>
    </div>
    <div class="row">
      <div class="col-md-5 border-right">
        <div class="panel-body no-padding">
          <p class="text-center">
          <h4>จำนวนคิว</h4>
          </p>
          <table class="table table-condensed">
            <tr>
              <th style="border-top: 1px solid #fff;"></th>
              <th class="text-center" style="border-top: 1px solid #fff;">คิวทั้งหมด</th>
              <th class="text-center" style="border-top: 1px solid #fff;">คิวรอ</th>
            </tr>
            <tr v-for="(item, k) in filteredServices" :key="k">
              <td>
                <i class="pe-7s-users fa-2x"></i>
                <span style="font-size: 18px;"> {{ item.service_name }}</span>
              </td>
              <td class="text-center">
                <span style="font-size: 18px;" :class="[`badge badge-${item.color}`]">{{ item.count }}</span>
              </td>
              <td class="text-center">
                <span style="font-size: 18px;" :class="[`badge badge-${item.color}`]">{{ item.wait }}</span>
              </td>
            </tr>
            <tr v-show="limit <= filteredServices.length">
              <td colspan="3" class="text-center">
                <button type="button" @click="loadMore" class="btn btn-sm btn-outline-primary">แสดงเพิ่มเพิ่ม</button>
              </td>
            </tr>
          </table>
          <br>
        </div>
      </div>
      <div class="col-md-7">
        <div id="chart1"></div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 border-right">
        <div id="chart2"></div>
      </div>
    </div>
  </div>
</div>
<br>
<br>

<?php
$this->registerJsFile(
  '@web/js/lodash.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile(
  '@web/vendor/moment/min/moment-with-locales.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJsFile(
  YII_ENV_DEV ? '@web/js/vue.js' : '@web/js/vue.min.js',
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
$this->registerJs($this->render('dashboard.js'));
?>