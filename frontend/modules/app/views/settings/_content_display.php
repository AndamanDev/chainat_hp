<?php

use homer\widgets\Table;
use homer\widgets\Datatables;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\icons\Icon;
use yii\helpers\Url;
?>
<div class="panel-body">
  <div class="table-responsive">
    <?php
    echo Table::widget([
      'tableOptions' => ['class' => 'table table-hover', 'width' => '100%', 'id' => 'tb-display'],
      'beforeHeader' => [
        [
          'columns' => [
            ['content' => 'ID', 'options' => []],
            ['content' => 'Display Name', 'options' => []],
            ['content' => 'Counter', 'options' => []],
            ['content' => 'Service', 'options' => []],
            // ['content' => 'Title Left','options' => []],
            // ['content' => 'Title Right','options' => []],
            // ['content' => 'Title Color','options' => []],
            // ['content' => 'Header Left','options' => []],
            // ['content' => 'Heeader Right','options' => []],
            // ['content' => 'Display Limit','options' => []],
            // ['content' => 'Hold Label','options' => []],
            // ['content' => 'Header Color','options' => []],
            // ['content' => 'Column Color','options' => []],
            // ['content' => 'Background Color','options' => []],
            // ['content' => 'Font Color','options' => []],
            // ['content' => 'Boder Color','options' => []],
            ['content' => 'ดำเนินการ', 'options' => []],
          ]
        ]
      ],
    ]);
    ?>
  </div>
</div>

<?= Datatables::widget([
  'id' => 'tb-display',
  'select2' => true,
  'clientOptions' => [
    'ajax' => [
      // 'url' => Url::base(true).'/app/settings/data-display',
      'url' => '/node/api/v1/setting/displays',
      'data' => new JsExpression('function ( d, settings ) {
          var api = new $.fn.dataTable.Api(settings)
          var info = api.page.info();
          var page = {
            number: info.page + 1,
            size: info.length
          }
          if(info.length !== -1) {
            return $.extend( {}, d, { page: page, \'access-token\': accesstoken } );
          }
          return $.extend( {}, d, { \'access-token\': accesstoken } );
        }')
    ],
    "dom" => "<'row'<'col-xs-6 d-flex justify-content-start'f><'col-xs-6 d-flex justify-content-end'Bl>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
    "language" => array_merge(Yii::$app->params['dtLanguage'], [
      "search" => "_INPUT_ " . Html::a(Icon::show('plus') . ' เพิ่ม-ลบ รายการ', ['/app/settings/create-display'], ['class' => 'btn btn-success', 'role' => 'modal-remote']),
      "searchPlaceholder" => "ค้นหา..."
    ]),
    "pageLength" => 10,
    "lengthMenu" => [[10, 25, 50, 75, 100], [10, 25, 50, 75, 100]],
    "autoWidth" => false,
    "deferRender" => true,
    "drawCallback" => new JsExpression('function(settings) {
        var api = this.api();
        dtFnc.initConfirm(api);
    }'),
    'initComplete' => new JsExpression('
        function () {
            var api = this.api();
            dtFnc.initResponsive( api );
            // dtFnc.initColumnIndex( api );
        }
    '),
    'columns' => [
      ["data" => null, "defaultContent" => "", "className" => "dt-center ", "title" => "#", "orderable" => false],
      ["data" => "display_name", "className" => "dt-body-left ", "title" => "ชื่อจอแสดงผล"],
      [
        "data" => "counterservice_id", 
        "className" => "dt-body-left dt-nowrap", 
        "title" => "ช่องบริการ",
        "render" => new JsExpression('function (data, type, row, meta) {
          return `<ol>${row.counters.map(item => `<li>${item.counterservice_name}</li>`).join(\'\')}</ol>`
        }')
      ],
      [
        "data" => "service_id", 
        "className" => "dt-body-left dt-nowrap", 
        "title" => "งานบริการ",
        "render" => new JsExpression('function (data, type, row, meta) {
          return `<ol>${row.services.map(item => `<li>${item.service_name}</li>`).join(\'\')}</ol>`
        }')
      ],
      // ["data" => "title_left","className" => "dt-body-left ","title" => "Title Left"],
      // ["data" => "title_right","className" => "dt-body-left ","title" => "Title Right"],
      // ["data" => "title_color","className" => "dt-body-left ","title" => "Title Color"],
      // ["data" => "table_title_left","className" => "dt-body-left ","title" => "Header Left"],
      // ["data" => "table_title_right","className" => "dt-body-left ","title" => "Header Right"],
      // ["data" => "display_limit","className" => "dt-body-left ","title" => "Display Limit"],
      // ["data" => "hold_label","className" => "dt-body-left ","title" => "Hold Label"],
      // ["data" => "header_color","className" => "dt-body-left ","title" => "Header Color"],
      // ["data" => "column_color","className" => "dt-body-left ","title" => "Column Color"],
      // ["data" => "background_color","className" => "dt-body-left ","title" => "Background Color"],
      // ["data" => "font_color","className" => "dt-body-left ","title" => "Font Color"],
      // ["data" => "border_color","className" => "dt-body-left ","title" => "Boder Color"],
      [
        "data" => null,
        "defaultContent" => "",
        "className" => "dt-center dt-nowrap",
        "orderable" => false,
        "title" => "ดำเนินการ",
        "render" => new JsExpression('function (data, type, row, meta) {
          return `<a href="/app/display/index?id=${row.display_ids}" class="btn btn-success btn-sm" title="ตัวอย่าง" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i></a>
          <a href="/app/settings/update-display?id=${row.display_ids}" class="btn btn-success btn-sm" target="_blank" data-pjax="0"><i class="fa fa-desktop" aria-hidden="true"></i></a>
          <a href="/app/settings/update-display?id=${row.display_ids}" class="btn btn-success btn-sm" role="modal-remote" title="แก้ไข"><i class="fa fa-pencil" aria-hidden="true"></i></a>
          <a href="/app/settings/delete-display?id=${row.display_ids}" class="btn btn-danger btn-sm" title="ลบ" data-method="post" data-pjax="0" data-confirm="คุณแน่ใจหรือไม่ที่จะลบรายการนี้?"><i class="fa fa-trash-o" aria-hidden="true"></i></a>`
        }')
      ]
    ],
    // "columnDefs" => [
    //     [ "visible" => false, "targets" => [4,5,6,7,8,9,10,11,12,13,14,15] ]
    // ]
    'processing' => true,
    'serverSide' => true,
    'stateSave' => true,
    'buttons' => [
      'colvis',
      'excel',
      [
        'text' => 'Reload',
        'action' => new JsExpression('function ( e, dt, node, config ) {
                dt.ajax.reload();
            }')
      ]
    ],
    "searchDelay" => 350,
  ],
  'clientEvents' => [
    'error.dt' => 'function ( e, settings, techNote, message ){
            e.preventDefault();
            swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
        }'
  ]
]); ?>