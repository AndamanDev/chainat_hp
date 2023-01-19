<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\View;
use yii\icons\Icon;
use homer\widgets\Table;
use frontend\modules\app\models\TbCounterservice;
use yii\helpers\ArrayHelper;
use kartik\widgets\DatePicker;
#assets
use homer\assets\SocketIOAsset;
use homer\assets\jPlayerAsset;
use homer\assets\SweetAlert2Asset;
use homer\assets\ToastrAsset;
use kartik\popover\PopoverX;

SweetAlert2Asset::register($this);
ToastrAsset::register($this);
SocketIOAsset::register($this);
jPlayerAsset::register($this);

$this->title = 'เรียกคิวจุดลงทะเบียน';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(
  <<<CSS
html.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown), body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
    overflow-y: unset !important;
}
/* .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
    border: 1px solid #74d348;
    border-bottom-color: transparent;
} */
.modal-open {
    overflow: unset;
}
input[type=search]:focus {
    background-color: #434a54;
    color: white;
}
table.dataTable span.highlight {
    background-color: #f0ad4e;
    color: white;
}
.select2-dropdown {
    z-index: 9999;
}
#row-search {
    margin-bottom: 10px;
}

div.dataTables_wrapper div.dataTables_filter{
    text-align: left;
}
div.dataTables_wrapper div.dataTables_length{
    float: right;
}
div.dt-buttons{
    float: right;
}
@media screen and (max-width: 640px){
    div.dt-buttons {
        float: right !important;
    }
}
/* Medium Devices, Desktops */
/* @media(min-width : 992px) {
    .call-next {
        position: fixed;
        text-align: center;
        right: -1px;
        padding: 10px;
        top: 125px;
        width: 300px;
        height: auto;
        text-transform: uppercase;
        background-color: #ffffff;
        box-shadow: 0 1px 10px 0px rgba(0, 0, 0, 0.05), 10px 12px 7px 3px rgba(0, 0, 0, .1);
        border-radius: 4px 0 0 4px;
        z-index: 100;
        border: 1px solid darkblue;
        cursor: move;
    }
    .last-queue{
        display: block;
    }
} */
/* Medium Devices, Desktops */
@media(max-width : 992px) {
    /* Show Q Last */
    .last-queue{
        display: none;
    }
}
.btn-recall,
.btn-hold,
.btn-end,
.btn-calling,
.btn-waiting {
    border-radius: 25px;
}
footer.footer {
    display: none;
}
#tab-menu > li.active a,
#tab-menu > li.active a:hover {
    background-color: #1a7bb9 !important;
    color: #FFFFFF !important;
}
#back-to-top {
    display: none !important;
}
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

    
.d-flex {
  display: flex !important;
}
.justify-content-end {
  justify-content: end !important;
}
CSS
);
$this->registerJs('var keySelected = [];', View::POS_HEAD);
$this->registerJs('var baseUrl = ' . Json::encode(Url::base(true)) . '; ', View::POS_HEAD);
$this->registerJs('var modelForm = ' . Json::encode($modelForm) . '; ', View::POS_HEAD);
$this->registerJs('var modelProfile = ' . Json::encode($modelProfile) . '; ', View::POS_HEAD);
$this->registerJs('var accesstoken = ' . Json::encode(Yii::$app->user->identity->getAuthKey()) . '; ', View::POS_HEAD);
// $this->registerJs('var select2Data = ' . Json::encode(ArrayHelper::map(TbCounterservice::find()->where(['counterservice_status' => 1])->orderBy(['service_order' => SORT_ASC])->all(), 'counterserviceid', 'counterservice_name')) . '; ', View::POS_HEAD);
?>
<div id="app"></div>
<div class="row">
  <div class="col-xs-12 col-sm-12 col-md-12">
    <div class="hpanel">
      <?php
      echo Tabs::widget([
        'items' => [
          [
            'label' => '<i class="pe-7s-volume"></i> เรียกคิว',
            'active' => true,
            'options' => ['id' => 'tab-1'],
            'linkOptions' => ['style' => 'font-size: 14px;'],
            'headerOptions' => ['class' => 'tab-1'],
          ],
          [
            'label' => '<i class="fa fa-list"></i> รายการคิว ' . Html::tag('span', '0', ['id' => 'count-qdata', 'class' => 'badge']),
            'options' => ['id' => 'tab-2'],
            'linkOptions' => ['style' => 'font-size: 14px;'],
            'headerOptions' => ['class' => 'tab-2'],
          ],
          // [
          //     'label' => '<i class="fa fa-users"></i> รายชื่อผู้ป่วย ' . Html::tag('span', '0', ['id' => 'count-patients', 'class' => 'badge']),
          //     'options' => ['id' => 'tab-3'],
          //     'linkOptions' => ['style' => 'font-size: 14px;'],
          // ],
        ],
        'options' => ['class' => 'nav nav-tabs'],
        'encodeLabels' => false,
        'renderTabContent' => false,
      ]);
      ?>
      <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
          <div class="panel-body" style="padding-bottom: 0px;">
            <div class="row">
              <div class="col-md-12 text-center text-tablet-mode" style="display: none;">
                <p><span style="font-weight: bold;text-align: center;font-size: 18px;">จุดลงทะเบียน</span></p>
              </div>
            </div>
            <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-8">

                <!-- Begin From -->
                <?php echo $this->render('_form_medical', ['modelForm' => $modelForm]); ?>
                <!-- End Form -->
                <div class="row">
                  <div class="col-xs-12 col-sm-12 col-md-12">
                    <!-- Begin Panel -->
                    <div class="hpanel">
                      <?php
                      echo Tabs::widget([
                        'items' => [
                          [
                            'label' => 'คิวรอเรียก ' . Html::tag('span', '0', ['id' => 'count-waiting', 'class' => 'badge count-waiting']),
                            'active' => true,
                            'options' => ['id' => 'tab-watting'],
                            'linkOptions' => ['style' => 'font-size: 14px;'],
                            'headerOptions' => ['class' => 'tab-watting'],
                          ],
                          [
                            'label' => 'กำลังเรียก ' . Html::tag('span', '0', ['id' => 'count-calling', 'class' => 'badge count-calling']),
                            'options' => ['id' => 'tab-calling'],
                            'linkOptions' => ['style' => 'font-size: 14px;', 'class' => 'tabx'],
                            'headerOptions' => ['class' => 'tab-calling'],
                          ],
                          [
                            'label' => 'พักคิว ' . Html::tag('span', '0', ['id' => 'count-hold', 'class' => 'badge count-hold']),
                            'options' => ['id' => 'tab-hold'],
                            'linkOptions' => ['style' => 'font-size: 14px;'],
                            'headerOptions' => ['class' => 'tab-hold']
                          ],
                        ],
                        'options' => ['class' => 'nav nav-tabs', 'id' => 'tab-menu-default'],
                        'encodeLabels' => false,
                        'renderTabContent' => false,
                      ]);
                      ?>
                      <div class="tab-content">
                        <?php echo $this->render('_table_medical'); ?>
                      </div>
                    </div><!-- End hpanel -->
                  </div>
                </div>
              </div>
              <div class="col-xs-12 col-sm-12 col-md-4">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="clast-queue">
                      <ul class="list-group">
                        <li class="list-group-item" style="font-size:18px;">
                          <span class="badge badge-primary" style="font-size:18px;" id="last-queue">-</span>
                          คิวที่เรียกล่าสุด
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <?php /*
                                    <div class="hpanel hpanel-ticket">
                                        <div class="panel-heading hbuilt">
                                            <div class="panel-tools">
                                                <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                                            </div>
                                            <h4><i class="fa fa-hashtag"></i> ออกบัตรคิว</h4>
                                        </div>
                                        <div class="panel-body">
                                            <?php if (!ArrayHelper::isIn($modelForm['service_profile'], explode(",", \Yii::$app->keyStorage->get('hidden-button-ticket', '')))): ?>
                                                <?php foreach ($services as $key => $value) : ?>
                                                    <p><?= Html::a($value['service_prefix'] . ' ' . $value['service_name'], ['/app/kiosk/create-ticket', 'groupid' => $value['servicegroupid'], 'serviceid' => $value['serviceid']], ['class' => 'btn btn-lg btn-block btn-success', 'role' => 'modal-remote', 'style' => 'text-align:left;', 'title' => $value['service_name']]) ?></p>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    */ ?>
              </div>
            </div>
          </div><!-- End panel body -->
        </div><!-- End Tab1 -->
        <div id="tab-2" class="tab-pane">
          <div class="panel-body">
            <!-- <div class="row" id="row-search">
                                <div class="col-md-6">
                                    <input type="text" class="form-control input-lg" name="search" id="search" placeholder="ค้นหาข้อมูล" style="background-color: #434a54;color: #ffffff;">
                                </div>
                            </div> -->
            <?php
            echo Table::widget([
              'tableOptions' => ['class' => 'table table-hover table-bordered table-condensed', 'width' => '100%', 'id' => 'tb-qdata'],
              'beforeHeader' => [
                [
                  'columns' => [
                    ['content' => '#', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'ID', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'คิว', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'HN', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'ชื่อ-นามสกุล', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'ประเภท', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'ช่องบริการ', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'สถานะ', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'วันที่', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'เวลา', 'options' => ['style' => 'text-align: center;']],
                    ['content' => 'ดำเนินการ', 'options' => ['style' => 'text-align: center;']],
                  ]
                ]
              ],
            ]);
            ?>
          </div>
        </div><!-- End Tab2 -->
        <?php /*
                    <div id="tab-3" class="tab-pane">
                        <div class="panel-body">
                            <div class="row" style="margin-bottom: 10px;">
                                <div class="col-md-4 col-md-offset-4">
                                    <?php
                                    echo 'Visit Date';
                                    echo DatePicker::widget([
                                        'id' => 'dp-vstdate',
                                        'name' => 'dp_1',
                                        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                                        'value' => date('d/m/Y'),
                                        'pluginOptions' => [
                                            'autoclose' => true,
                                            'format' => 'dd/mm/yyyy',
                                        ],
                                        'pluginEvents' => [
                                            "changeDate" => "function(e) {
                                            var table = $('#tb-patients').DataTable();
                                            table.ajax.url( '/app/calling/data-patients-list?vstdate='+$('#dp-vstdate').val() ).load();
                                        }",
                                        ],
                                    ]);
                                    ?>
                                </div>
                            </div>
                            <?php
                            echo Table::widget([
                                'tableOptions' => ['class' => 'table table-hover table-bordered table-condensed', 'width' => '100%', 'id' => 'tb-patients'],
                                'beforeHeader' => [
                                    [
                                        'columns' => [
                                            ['content' => '#', 'options' => ['style' => 'text-align: center;']],
                                            ['content' => 'HN', 'options' => ['style' => 'text-align: center;']],
                                            ['content' => 'VN', 'options' => ['style' => 'text-align: center;']],
                                            ['content' => 'CID', 'options' => ['style' => 'text-align: center;']],
                                            ['content' => 'ชื่อ-นามสกุล', 'options' => ['style' => 'text-align: center;']],
                                            ['content' => 'Visit Date', 'options' => ['style' => 'text-align: center;']],
                                            ['content' => 'ดำเนินการ', 'options' => ['style' => 'text-align: center;']],
                                        ]
                                    ]
                                ],
                            ]);
                            ?>
                        </div>
                    </div><!-- End Tab3 -->
                    */ ?>
      </div>
    </div><!-- End hpanel -->
  </div>
</div>
<div class="row">
  <div class="col-xs-12 col-sm-12 col-md-12">
    <div class="footer footer-tabs" style="position: fixed;padding: 20px 18px;z-index: 3;">
      <div class="hpanel">
        <?php
        $icon = '<p style="margin: 0"><i class="fa fa-list" style="font-size: 1.5em;"></i> </p>';
        echo Tabs::widget([
          'items' => [
            [
              'label' => $icon . ' คิวรอเรียก ' . Html::tag('span', '0', ['id' => 'count-waiting', 'class' => 'badge badge-info count-waiting']),
              'active' => true,
              'options' => ['id' => 'tab-watting'],
              'linkOptions' => ['style' => 'font-size: 14px;'],
              'headerOptions' => ['style' => 'width: 33.33%;bottom: 20px;', 'class' => 'tab-watting text-center'],
            ],
            [
              'label' => $icon . ' กำลังเรียก ' . Html::tag('span', '0', ['id' => 'count-calling', 'class' => 'badge badge-info count-calling']),
              'options' => ['id' => 'tab-calling'],
              'linkOptions' => ['style' => 'font-size: 14px;', 'class' => 'tabx'],
              'headerOptions' => ['style' => 'width: 33.33%;bottom: 20px;', 'class' => 'tab-calling text-center'],
            ],
            [
              'label' => $icon . ' พักคิว ' . Html::tag('span', '0', ['id' => 'count-hold', 'class' => 'badge badge-info count-hold']),
              'options' => ['id' => 'tab-hold'],
              'linkOptions' => ['style' => 'font-size: 14px;'],
              'headerOptions' => ['style' => 'width: 33.33%;bottom: 20px;', 'class' => 'tab-hold text-center']
            ],
          ],
          'options' => ['class' => 'nav nav-tabs', 'id' => 'tab-menu'],
          'encodeLabels' => false,
          'renderTabContent' => false,
        ]);
        ?>
      </div>
    </div>
  </div>
</div>
<!-- jPlayer -->
<div id="jplayer_notify"></div>

<?php
$this->registerJsFile(
  YII_ENV_DEV ? '@web/js/vue.js' : '@web/js/vue.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
echo $this->render('modal');
echo $this->render('_datatables', ['modelForm' => $modelForm, 'modelProfile' => $modelProfile, 'action' => Yii::$app->controller->action->id]);
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
  'https://cdn.jsdelivr.net/npm/axios@1.1.2/dist/axios.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
$this->registerJs($this->render(YII_ENV_DEV ? 'script-medical.js' : 'script-medical.min.js'));
$this->registerJs(
  <<<JS
//search data
$('input#search').on( 'keyup', function () {
    dt_tbqdata.search( this.value ).draw();
});

$('#tb-calling tbody,#tb-waiting tbody,#tb-hold tbody').on( 'click', 'tr td a', function () {
    var tr = $(this).closest("tr") , selected = 'active',id = $(this).closest("table").attr('id');
    if ( !$(tr).hasClass(selected) ) {
        if(id == 'tb-waiting'){
            $('#tb-waiting').find('tr.'+selected).removeClass(selected);
        }
        if(id == 'tb-calling'){
            $('#tb-calling').find('tr.'+selected).removeClass(selected);
        }
        if(id == 'tb-hold'){
            $('#tb-hold').find('tr.'+selected).removeClass(selected);
        }
        $(tr).addClass(selected);
    }
} );

$(document).ready(function() {
    var t = $('#tb-patients').DataTable( {
        "ajax": "/app/calling/data-patients-list",
        //"deferRender": true,
        "autoWidth": false,
        "pageLength": 50,
        "dom": "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
        "buttons": [
            'excel',
            {
                text: 'Reload',
                action: function ( e, dt, node, config ) {
                    dt.ajax.reload();
                }
            }
        ],
        "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
        "language": {
            "sProcessing":   "กำลังดำเนินการ...",
            "sLengthMenu":   "_MENU_",
            "sZeroRecords":  "ไม่พบข้อมูล",
            "sInfo":         "แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว",
            "sInfoEmpty":    "แสดง 0 ถึง 0 จาก 0 แถว",
            "sInfoFiltered": "(กรองข้อมูล _MAX_ ทุกแถว)",
            "sInfoPostFix":  "",
            "sSearch":       "ค้นหา: ",
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    "หน้าแรก",
                "sPrevious": "ก่อนหน้า",
                "sNext":     "ถัดไป",
                "sLast":     "หน้าสุดท้าย"
            }
        },
        "columns": [
            { "data": null,"defaultContent": "" ,"className": "dt-center dt-nowrap"},
            { "data": "hn", "className": "dt-center"},
            { "data": "vn", "className": "dt-center" },
            { "data": "cid", "className": "dt-center" },
            { "data": "fullname" },
            { "data": "vstdate" , "className": "dt-center"},
            { "data": "actions","className": "dt-center dt-nowrap" }
        ],
        "drawCallback": function( settings ) {
            var api = this.api();
            var count  = api.data().count();
            $("#count-patients").html(count);
        }
    } );
    t.on( 'order.dt search.dt', function () {
        t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw();
} );

// dt_tbqdata.on('draw.dt order.dt search.dt', function (e, settings) {
//   var info = dt_tbqdata.page.info();
//   console.log(info);
//   let i = info.start === 0 ? 1 : info.start;

//   dt_tbqdata.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
//       this.data(i++);
//   });
// }).draw();
dt_tbqdata.on('draw.dt', function () {
    var info = dt_tbqdata.page.info();
    dt_tbqdata.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
        cell.innerHTML = i + 1 + info.start;
    });
});


JS
);
?>