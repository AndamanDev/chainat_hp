<?php
use homer\widgets\Table;
use homer\widgets\Datatables;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\icons\Icon;
use yii\helpers\Url;
?>
<div class="panel-body">
    <?php
    echo Table::widget([
        'tableOptions' => ['class' => 'table table-hover','width' => '100%','id' => 'tb-sound'],
        'beforeHeader' => [
            [
                'columns' => [
                    ['content' => '#','options' => []],
                    ['content' => 'ชื่อไฟล์','options' => []],
                    ['content' => 'โฟรเดอร์ไฟล์','options' => []],
                    ['content' => 'เสียงเรียก','options' => []],
                    ['content' => 'ประเภทเสียง','options' => []],
                    ['content' => 'ดำเนินการ','options' => []],
                ]
            ]
        ],
    ]);
    ?>
</div>

<?= Datatables::widget([
    'id' => 'tb-sound',
    'select2' => true,
    'clientOptions' => [
        // 'ajax' => [
        //     'url' => Url::base(true).'/app/settings/data-sound'
        // ],
        'ajax' => [
          'url' => '/node/api/v1/setting/sounds',
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
        "language" => array_merge(Yii::$app->params['dtLanguage'],[
            "search" => "_INPUT_ " . Html::a(Icon::show('plus').' เพิ่ม-ลบ รายการ', ['/app/settings/create-sound'],['class' => 'btn btn-success','role' => 'modal-remote']),
            "searchPlaceholder" => "ค้นหา..."
        ]),
        "pageLength" => 10,
        "lengthMenu" => [ [10, 25, 50, 75, 100], [10, 25, 50, 75, 100] ],
        "autoWidth" => false,
        "deferRender" => true,
        "drawCallback" => new JsExpression ('function(settings) {
            var api = this.api();
            var rows = api.rows( {page:"current"} ).nodes();
            var columns = api.columns().nodes();
            var last=null;
            dtFnc.initConfirm(api);
        }'),
        'initComplete' => new JsExpression ('
            function () {
                var api = this.api();
                dtFnc.initResponsive( api );
                // dtFnc.initColumnIndex( api );
            }
        '),
        'columns' => [
            ["data" => null,"defaultContent" => "", "className" => "dt-center dt-head-nowrap","title" => "#", "orderable" => false],
            ["data" => "sound_name","className" => "dt-body-left dt-head-nowrap","title" => "ชื่อไฟล์"],
            ["data" => "sound_path_name","className" => "dt-body-left dt-head-nowrap","title" => "โฟรเดอร์ไฟล์"],
            ["data" => "sound_th","className" => "dt-body-left dt-head-nowrap","title" => "เสียงเรียก"],
            [
              "data" => "sound_type",
              "className" => "dt-body-center dt-head-nowrap dt-center",
              "title" => "ประเภทเสียง",
              "render" => new JsExpression('function (data, type, row, meta) {
                return row.sound_type === 1 ? \'เสียงผู้หญิง\' : \'เสียงผู้ชาย\'
              }')
            ],
            [
              "data" => null,
              "defaultContent" => "",
              "className" => "dt-center dt-nowrap",
              "orderable" => false,
              "title" => "ดำเนินการ",
              "render" => new JsExpression('function (data, type, row, meta) {
                return `<a href="/app/settings/update-sound?id=${row.sound_id}" class="btn btn-success btn-sm" role="modal-remote" title="แก้ไข"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                <a href="/app/settings/delete-sound?id=${row.sound_id}" class="btn btn-danger btn-sm" title="ลบ" data-method="post" data-pjax="0" data-confirm="คุณแน่ใจหรือไม่ที่จะลบรายการนี้?"><i class="fa fa-trash-o" aria-hidden="true"></i></a>`
              }')
            ]
        ],
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