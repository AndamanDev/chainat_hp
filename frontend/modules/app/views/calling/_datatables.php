<?php

use homer\assets\DatatablesAsset;
use homer\widgets\Datatables;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\icons\Icon;
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

if ($action == 'index') {
  #คิวรอเรียก
  /*
  echo Datatables::widget([
    'id' => 'tb-waiting',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbwaiting-sr',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      //"dom" => "<'pull-left'f><'pull-right'Bl>t<'pull-left'i>p",
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "responsive" => true,
      "searchHighlight" => true,
      "order" => [[7, 'asc']],
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-waiting").html(count);
                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(2, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            \'<tr class=""><td style="text-align: center;font-size:16px" colspan="\'+columns.length+\'">\'+group +\'</td></tr>\'
                        );
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix"],
        ["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ"]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 3, 4, 6, 7, 8],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);

  echo Datatables::widget([
    'id' => 'tb-calling',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbcalling-sr',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      //"dom" => "<'pull-left'f><'pull-right'B>t<'pull-left'i>p",
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      "order" => [[8, 'asc']],
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-calling").html(count);

                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(2, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            \'<tr class=""><td style="text-align: center;font-size:16px" colspan="\'+columns.length+\'">\'+group +\'</td></tr>\'
                        );
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix"],
        ["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ"]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 3, 4, 5, 7, 8, 9],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
  */

  echo Datatables::widget([
    'id' => 'tb-hold',
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbhold-sr',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      //"dom" => "<'pull-left'f><'pull-right'B>t<'pull-left'i>p",
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      "order" => [[8, 'asc']],
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                dtFnc.initResponsive( api );
                var count  = api.data().count();
                $(".count-hold").html(count);

                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(2, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            \'<tr class=""><td style="text-align: center;font-size:16px" colspan="\'+columns.length+\'">\'+group +\'</td></tr>\'
                        );
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initColumnIndex( api );
                    dtFnc.initResponsive( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix"],
        ["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ"]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 3, 4, 5, 7, 8, 9],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
} elseif ($action == 'medical') {
  #คิวรอเรียก
  /*
  echo Datatables::widget([
    'id' => 'tb-waiting',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/node/api/v1/calling/waiting-list',
        // 'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        'data' => new JsExpression('function ( d, settings ) {
          var api = new $.fn.dataTable.Api( settings );
          var info = api.page.info();
          var page = {
            number: info.page + 1,
            size: info.length
          }
          if(info.length !== -1) {
            return $.extend( {}, d, { page: page, modelForm: modelForm, modelProfile: modelProfile } );
          }
          return $.extend( {}, d , {
            modelForm: modelForm,
            modelProfile: modelProfile,
          });
        }'),
        "type" => "POST",
        "complete" => new JsExpression('function(jqXHR, textStatus){
                
                }')
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6 d-flex justify-content-end'Bl>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => false,
      'processing' => true,
      'serverSide' => true,
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-waiting").html(count);

                // var rows = api.rows( {page:"current"} ).nodes();
                // var columns = api.columns().nodes();
                // var last=null;
                // api.column(6, {page:"current"} ).data().each( function ( group, i ) {
                //     var data = api.rows(i).data();
                //     if ( last !== group ) {
                //         if(data[0].quickly === 1){
                //             $(rows).eq( i ).before(
                //                 \'<tr class="warning"><td style="text-align: left;font-size:16px" colspan="\'+columns.length+\'">คิวด่วน</td></tr>\'
                //             );
                //         }else{
                //             $(rows).eq( i ).before(
                //                 \'<tr class=""><td style="text-align: left;font-size:16px" colspan="\'+columns.length+\'">\'+group +\'</td></tr>\'
                //             );
                //         }
                        
                //         last = group;
                //     }
                // } );
                if(keySelected.length > 0 && keySelected != undefined  && localStorage.getItem("medical-tablet-mode") == "true"){
                    var indexRemove = [];
                    $.each(keySelected, function (index, value) {
                        var tr = $("#tb-waiting").find("tr#" + value);
                        if (tr.length == 1) {
                            $("#checkbox-"+value).prop("checked", true);
                            $("#tb-waiting tr#"+value).addClass("success");
                        }else{
                            indexRemove.push(index);
                        }
                    });
                    $.each(indexRemove, function (i, k) {
                        keySelected.splice(k, 1);
                    });
                    $(\'.count-selected\').html(\'(\'+keySelected.length+\')\');
                    if(keySelected.length == 0){
                        $(\'button.on-call-selected\').prop(\'disabled\', true);
                    }
                }
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "text-center", "render" => new JsExpression('function ( data, type, row, meta ) {
                    return (meta.row + 1);
                }')],
        ["data" => "checkbox", "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        [
          "data" => "q_num",
          "className" => "dt-body-center dt-head-nowrap",
          "title" => "<i class=\"fa fa-money\"></i> คิว",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return `<span class="badge badge-success">${row.q_num}</span>`;
          }')
        ],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ชื่อ"],
        [
          "data" => "service_name",
          "className" => "dt-body-left dt-head-nowrap",
          "title" => "ประเภท",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            if(row.quickly === 1) {
              return `คิวด่วน`;
            }
            return row.service_name;
          }')
        ],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        [
          "data" => "queue_date",
          "className" => "dt-body-center dt-head-nowrap",
          "title" => "วันที่",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return moment(row.queue_date).format("DD/MM/YYYY");
          }')
        ],
        [
          "data" => "queue_time",
          "className" => "dt-body-center dt-head-nowrap",
          "title" => "เวลา",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return moment(row.queue_time, "HH:mm:ss").format("HH:mm");
          }')
        ],
        [
          "data" => null,
          "defaultContent" => "",
          "className" => "dt-center dt-nowrap",
          "orderable" => false,
          "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return `<a href="/node/api/v1/calling/call?id=${row.q_ids}" class="btn btn-success btn-calling">เรียกคิว</a>`;
          }')
        ]
      ],
      "columnDefs" => [
        [
          "targets" => [4, 6, 7],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
      "order" => [[9, 'asc']]
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
  */

  /*
  echo Datatables::widget([
    'id' => 'tb-calling',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/node/api/v1/calling/calling-list',
        // 'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        'data' => new JsExpression('function ( d ) {
          var table = $("#tb-calling").DataTable();
          var info = table.page.info();
          var page = {
            number: info.page + 1,
            size: info.length
          }
          if(info.length !== -1) {
            return $.extend( {}, d, { page: page, modelForm: modelForm, modelProfile: modelProfile } );
          }
          return $.extend( {}, d , {
            modelForm: modelForm,
            modelProfile: modelProfile,
          });
        }'),
        "type" => "POST"
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6 d-flex justify-content-end'Bl>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => false,
      'processing' => true,
      'serverSide' => true,
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-calling").html(count);

                // var rows = api.rows( {page:"current"} ).nodes();
                // var columns = api.columns().nodes();
                // var last=null;
                // api.column(2, {page:"current"} ).data().each( function ( group, i ) {
                //     var data = api.rows(i).data();
                //     if ( last !== group ) {
                //         if(data[0].quickly == "1"){
                //             $(rows).eq( i ).before(
                //                 \'<tr class="warning"><td style="text-align: left;font-size:16px" colspan="\'+columns.length+\'">\'+group +\'</td></tr>\'
                //             );
                //         }else{
                //             $(rows).eq( i ).before(
                //                 \'<tr class=""><td style="text-align: left;font-size:16px" colspan="\'+columns.length+\'">\'+group +\'</td></tr>\'
                //             );
                //         }
                        
                //         last = group;
                //     }
                // } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        [
          "data" => "q_num", 
          "className" => "dt-body-center dt-head-nowrap", 
          "title" => "<i class=\"fa fa-money\"></i> คิว",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return `<span class="badge badge-success">${row.q_num}</span>`;
          }')
        ],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ชื่อ"],
        [
          "data" => "service_name", 
          "className" => "dt-body-left dt-head-nowrap", 
          "title" => "ประเภท",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            if(row.quickly === 1) {
              return `คิวด่วน`;
            }
            return row.service_name;
          }')
        ],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        // ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        [
          "data" => "queue_date",
          "className" => "dt-body-center dt-head-nowrap",
          "title" => "วันที่",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return moment(row.queue_date).format("DD/MM/YYYY");
          }'),
          "visible" => false
        ],
        [
          "data" => "queue_time",
          "className" => "dt-body-center dt-head-nowrap",
          "title" => "เวลา",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return moment(row.queue_time, "HH:mm:ss").format("HH:mm");
          }'),
          "visible" => false
        ],
        [
          "data" => "call_timestp",
          "className" => "dt-body-center dt-head-nowrap",
          "title" => "เวลาเรียกคิว",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return moment(row.call_timestp, "YYYY-MM-DD HH:mm:ss").format("HH:mm");
          }')
        ],
        [
          "data" => null,
          "defaultContent" => "",
          "className" => "dt-center dt-nowrap",
          "orderable" => false,
          "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return `<a href="/node/api/v1/calling/recall?id=${row.caller_ids}" class="btn btn-success btn-recall">เรียกซ้ำ</a>
            <a href="/node/api/v1/calling/hold?id=${row.caller_ids}" class="btn btn-warning btn-hold">พักคิว</a>
            <a href="/node/api/v1/calling/end?id=${row.caller_ids}" class="btn btn-danger btn-end">เสร็จสิ้น</a>`;
          }')
        ]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 3, 5, 7],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
  */

  /*
  echo Datatables::widget([
    'id' => 'tb-hold',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbhold',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-hold").html(count);

                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(2, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        if(data[0].quickly == "1"){
                            $(rows).eq( i ).before(
                                \'<tr class="warning"><td style="text-align: left;font-size:16px" colspan="\'+columns.length+\'">\'+group +\'</td></tr>\'
                            );
                        }else{
                            $(rows).eq( i ).before(
                                \'<tr class=""><td style="text-align: left;font-size:16px" colspan="\'+columns.length+\'">\'+group +\'</td></tr>\'
                            );
                        }
                        
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ชื่อ"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ"]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 3, 5, 7],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
  */

  echo Datatables::widget([
    'id' => 'tb-qdata',
    'select2' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/node/api/kiosk/queue-list',
        'data' => new JsExpression('function ( d ) {
                  var table = $("#tb-qdata").DataTable();
                  var info = table.page.info();
                  var page = {
                    number: info.page + 1,
                    size: info.length
                  }
                  if(info.length !== -1) {
                    return $.extend( {}, d, { page: page } );
                  }
                  return $.extend( {}, d );
                }')
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6 d-flex justify-content-end'Bl>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา..."
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      'processing' => true,
      'serverSide' => true,
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                // var count  = api.data().count();
                // $("#count-qdata").html(count);

                var rows = api.rows( {page:\'current\'} ).nodes();
                var last=null;
    
                // api.column(2, {page:\'current\'} ).data().each( function ( group, i ) {
                //     if ( last !== group ) {
                //         $(rows).eq( i ).before(
                //             \'<tr class="group"><td colspan="9">\'+group+\'</td></tr>\'
                //         );
    
                //         last = group;
                //     }
                // } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    // dtFnc.initColumnIndex( api );
                    $($.fn.dataTable.tables(true)).DataTable()
                      .columns.adjust()
                      .responsive.recalc();
                }
            '),
      "order" => [[1, 'desc']],
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_ids", "className" => "dt-body-center dt-head-nowrap", "title" => "ID", "visible" => false],
        [
          "data" => "q_num",
          "className" => "dt-body-center dt-head-nowrap",
          "title" => "คิว",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return `<span class="badge badge-success">${row.q_num}</span>`;
          }')
        ],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ชื่อ-นามสกุล"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "counterservice_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ช่องบริการ"],
        ["data" => "service_status_name", "className" => "dt-body-left dt-head-nowrap", "title" => "สถานะ"],
        [
          "data" => "queue_date", 
          "className" => "dt-body-center dt-head-nowrap dt-center", 
          "title" => "วันที่",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return moment(row.queue_date).format(\'DD/MM/YYYY\')
          }')
        ],
        [
          "data" => "queue_time", 
          "className" => "dt-body-center dt-head-nowrap dt-center", 
          "title" => "เวลา",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            return moment(row.queue_time, \'HH:mm:ss\').format(\'HH:mm\')
          }')
        ],
        [
          "data" => null,
          "defaultContent" => "",
          "className" => "dt-center dt-nowrap",
          "orderable" => false,
          "title" => "ดำเนินการ",
          "render" => new JsExpression('function ( data, type, row, meta ) {
            var btndel = `<button type="button" disabled class="btn btn-sm btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> ลบ</button>`
            if(row.q_status_id === 4) {
              return `<button class="btn btn-sm btn-primary" disabled><i class="fa fa-edit"></i> แก้ไข</button>
              <a href="javascript:void(0);" onclick="return window.open(\'/app/kiosk/print-ticket?id=${row.q_ids}\', \'myPrint\', \'width=800, height=600\')" class="btn btn-sm btn-success"><i class="pe-7s-print"></i> พิมพ์บัตรคิว</a>
              ${btndel}`;
            }
            if(row.q_status_id === 1) {
              btndel = `<a href="/app/calling/del?id=${row.q_ids}" class="btn btn-sm btn-danger btn-del"><i class="fa fa-trash" aria-hidden="true"></i> ลบ</a>`
            }
            return `<a href="/app/kiosk/update-queue?id=${row.q_ids}" class="btn btn-sm btn-primary" role="modal-remote"><i class="fa fa-edit"></i> แก้ไข</a>
            <a href="javascript:void(0);" onclick="return window.open(\'/app/kiosk/print-ticket?id=${row.q_ids}\', \'myPrint\', \'width=800, height=600\')" class="btn btn-sm btn-success"><i class="pe-7s-print"></i> พิมพ์บัตรคิว</a>
            ${btndel}`;
          }')
        ],
      ],
      'buttons' => [
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
  ]);
} elseif ($action == 'examination-room') {
  #คิวรอเรียก
  /*
  echo Datatables::widget([
    'id' => 'tb-waiting',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbwaiting-ex',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      //"order" => [[8, 'asc']],
      // "drawCallback" => new JsExpression ('function(settings) {
      //     var api = this.api();
      //     dtFnc.initConfirm(api);
      //     var count  = api.data().count();
      //     $(".count-waiting").html(count);

      //     var rows = api.rows( {page:"current"} ).nodes();
      //     var columns = api.columns().nodes();
      //     var last=null;
      //     api.column(3, {page:"current"} ).data().each( function ( group, i ) {
      //         var data = api.rows(i).data();
      //         if ( last !== group ) {
      //             $(rows).eq( i ).before(
      //                 \'<tr class=""><td style="text-align: left;font-size: 16px;" colspan="\'+columns.length+\'">\'+group+\'</td></tr>\'
      //             );
      //             last = group;
      //         }
      //     } );
      // }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง", "visible" => false],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ", "visible" => false],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix", "visible" => false],
        // ["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ", "responsivePriority" => 1]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 3, 4, 6, 7, 8, 9],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
  */

  /*
  echo Datatables::widget([
    'id' => 'tb-calling',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbcalling-ex',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      "order" => [[9, 'asc']],
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-calling").html(count);

                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(3, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            \'<tr class=""><td style="text-align: left;font-size: 16px;" colspan="\'+columns.length+\'">\'+group+\'</td></tr>\'
                        );
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix"],
        //["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ", "responsivePriority" => 1]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 3, 6, 7, 8, 9],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
  */

  /*
  echo Datatables::widget([ //พักคิว
    'id' => 'tb-hold',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbhold-ex',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      "order" => [[9, 'asc']],
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-hold").html(count);

                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(3, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            \'<tr class=""><td style="text-align: center;font-size: 16px;" colspan="\'+columns.length+\'">\'+group+\'</td></tr>\'
                        );
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix"],
        //["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ"]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 3, 6, 7, 8, 9],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
  */
} elseif ($action == 'medicine-room') {
  #คิวรอเรียก
  echo Datatables::widget([
    'id' => 'tb-waiting',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbwaiting-medicine',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      "order" => [[8, 'asc']],
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-waiting").html(count);

                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(3, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            \'<tr class=""><td style="text-align: left;font-size: 16px;" colspan="\'+columns.length+\'">\'+group+\'</td></tr>\'
                        );
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง"],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix"],
        ["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ"]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 4, 5, 7, 8, 9],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);

  echo Datatables::widget([
    'id' => 'tb-calling',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbcalling-medicine',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      "order" => [[8, 'asc']],
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-calling").html(count);

                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(3, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            \'<tr class=""><td style="text-align: left;font-size: 16px;" colspan="\'+columns.length+\'">\'+group+\'</td></tr>\'
                        );
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix"],
        ["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ"]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 4, 5, 7, 8, 9],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);

  echo Datatables::widget([
    'id' => 'tb-hold',
    'buttons' => true,
    'clientOptions' => [
      'ajax' => [
        'url' => Url::base(true) . '/app/calling/data-tbhold-medicine',
        'data' => ['modelForm' => $modelForm, 'modelProfile' => $modelProfile],
        "type" => "POST"
      ],
      "dom" => "<'row'<'col-xs-6'f><'col-xs-6'lB>> <'row'<'col-xs-12'tr>> <'row'<'col-xs-5'i><'col-xs-7'p>>",
      "language" => array_merge(Yii::$app->params['dtLanguage'], [
        "search" => "_INPUT_ ",
        "searchPlaceholder" => "ค้นหา...",
        "lengthMenu" => "_MENU_"
      ]),
      "pageLength" => 10,
      "lengthMenu" => [[10, 25, 50, 75, 100, -1], [10, 25, 50, 75, 100, 'All']],
      "autoWidth" => false,
      "deferRender" => true,
      //"searching" => false,
      "searchHighlight" => true,
      "responsive" => true,
      "order" => [[8, 'asc']],
      "drawCallback" => new JsExpression('function(settings) {
                var api = this.api();
                dtFnc.initConfirm(api);
                var count  = api.data().count();
                $(".count-hold").html(count);

                var rows = api.rows( {page:"current"} ).nodes();
                var columns = api.columns().nodes();
                var last=null;
                api.column(3, {page:"current"} ).data().each( function ( group, i ) {
                    var data = api.rows(i).data();
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            \'<tr class=""><td style="text-align: left;font-size: 16px;" colspan="\'+columns.length+\'">\'+group+\'</td></tr>\'
                        );
                        last = group;
                    }
                } );
            }'),
      'initComplete' => new JsExpression('
                function () {
                    var api = this.api();
                    dtFnc.initResponsive( api );
                    dtFnc.initColumnIndex( api );
                }
            '),
      'columns' => [
        ["data" => null, "defaultContent" => "", "className" => "dt-center dt-head-nowrap", "title" => "#", "orderable" => false],
        ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-money\"></i> คิว"],
        ["data" => "q_hn", "className" => "dt-body-center dt-head-nowrap", "title" => "HN"],
        ["data" => "q_qn", "className" => "dt-body-center dt-head-nowrap", "title" => "QN"],
        ["data" => "service_name", "className" => "dt-body-left dt-head-nowrap", "title" => "ประเภท"],
        ["data" => "pt_name", "className" => "dt-body-left dt-head-nowrap", "title" => "<i class=\"fa fa-user\"></i> ชื่อ-นามสกุล"],
        ["data" => "counterservice_name", "className" => "dt-body-center dt-head-nowrap", "title" => "จุดบริการ"],
        ["data" => "checkin_date", "className" => "dt-body-center dt-head-nowrap", "title" => "<i class=\"fa fa-clock-o\"></i> เวลามาถึง"],
        ["data" => "service_status_name", "className" => "dt-body-center dt-head-nowrap", "title" => "สถานะ"],
        ["data" => "service_prefix", "className" => "dt-body-center dt-head-nowrap", "title" => "prefix"],
        ["data" => "lab_confirm", "className" => "dt-center", "title" => "ผล Lab"],
        ["data" => "actions", "className" => "dt-center dt-nowrap", "orderable" => false, "title" => "<i class=\"fa fa-cogs\"></i> ดำเนินการ"]
      ],
      "columnDefs" => [
        [
          "targets" => [2, 4, 5, 7, 8, 9],
          "visible" => false
        ]
      ],
      "buttons" => [
        [
          'extend' => 'colvis',
          'text' => Icon::show('list', [], Icon::BSG)
        ],
        [
          'text' => Icon::show('refresh', [], Icon::BSG),
          'action' => new JsExpression('function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }'),
        ]
      ],
    ],
    'clientEvents' => [
      'error.dt' => 'function ( e, settings, techNote, message ){
                e.preventDefault();
                swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
            }'
    ]
  ]);
}
