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
        'tableOptions' => ['class' => 'table table-hover','width' => '100%','id' => 'tb-service-group'],
        'beforeHeader' => [
            [
                'columns' => [
                    ['content' => '#','options' => []],
                    ['content' => 'ชื่อกลุ่มบริการ','options' => []],
                    ['content' => 'ชื่อบริการ','options' => []],
                    ['content' => 'ประเภทบริการ','options' => []],
                    ['content' => 'ลำดับการบริการ','options' => []],
                    ['content' => 'แบบการพิมพ์บัตรคิว','options' => []],
                    ['content' => 'จำนวนพิมพ์ต่อครั้ง','options' => []],
                    ['content' => 'ตัวอักษร/ตัวเลข นำหน้าคิว','options' => []],
                    ['content' => 'จำนวนหลักหมายเลขคิว','options' => []],
                    ['content' => 'แสดงบน kiosk','options' => []],
                    ['content' => 'แสดงบน mobile','options' => []],
                    ['content' => 'สถานะคิว','options' => []],
                    ['content' => 'ดำเนินการ','options' => []],
                ]
            ]
        ],
    ]);
    ?>
</div>

<?= Datatables::widget([
    'id' => 'tb-service-group',
    'select2' => true,
    'clientOptions' => [
        'ajax' => [
            'url' => Url::base(true).'/app/settings/data-service-group'
        ],
        "dom" => "<'pull-left'f><'pull-right'l>t<'pull-left'i>p",
        "language" => array_merge(Yii::$app->params['dtLanguage'],[
            "search" => "_INPUT_ " . Html::a(Icon::show('plus').' เพิ่ม-ลบ รายการ', ['/app/settings/create-service-group'],['class' => 'btn btn-success','role' => 'modal-remote']),
            "searchPlaceholder" => "ค้นหา..."
        ]),
        "pageLength" => 50,
        "lengthMenu" => [ [10, 25, 50, 75, 100], [10, 25, 50, 75, 100] ],
        "autoWidth" => false,
        "deferRender" => true,
        "drawCallback" => new JsExpression ('function(settings) {
            var api = this.api();
            var rows = api.rows( {page:"current"} ).nodes();
            var columns = api.columns().nodes();
            var last=null;
            api.column(1, {page:"current"} ).data().each( function ( group, i ) {
                var data = api.rows( {page:"current"} ).data()[i]
                
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        \'<tr class="warning"><td colspan="\'+columns.length+\'">\'+group+\' <a href="/app/settings/update-service-group?id=\'+data.servicegroupid+\'" class="btn btn-xs btn-success" role="modal-remote"><i class="fa fa-plus"></i></a> </td></tr>\'
                    );
                    last = group;
                }
            } );
            dtFnc.initConfirm(api);
        }'),
        'initComplete' => new JsExpression ('
            function () {
                var api = this.api();
                dtFnc.initResponsive( api );
                dtFnc.initColumnIndex( api );
            }
        '),
        "columnDefs" => [
            ["visible" => false, "targets" => 1],
        ],
        'columns' => [
            ["data" => null,"defaultContent" => "", "className" => "dt-center dt-head-nowrap","title" => "#", "orderable" => false],
            ["data" => "servicegroup_name","className" => "dt-body-left dt-head-nowrap","title" => "ชื่อกลุ่มบริการ"],
            ["data" => "service_name","className" => "dt-body-left dt-head-nowrap","title" => "ชื่อบริการ"],
            ["data" => "service_type_name","className" => "dt-body-left dt-head-nowrap","title" => "ประเภทบริการ"],
            ["data" => "service_route","className" => "dt-body-left dt-head-nowrap","title" => "ลำดับการบริการ"],
            ["data" => "prn_profileid","className" => "dt-body-left dt-head-nowrap","title" => "แบบการพิมพ์บัตรคิว"],
            ["data" => "prn_copyqty","className" => "dt-body-left dt-head-nowrap","title" => "จำนวนพิมพ์ต่อครั้ง"],
            ["data" => "service_prefix","className" => "dt-body-left dt-head-nowrap","title" => "ตัวอักษร/ตัวเลข นำหน้าคิว"],
            ["data" => "service_numdigit","className" => "dt-body-left dt-head-nowrap","title" => "จำนวนหลักหมายเลขคิว"],
            ["data" => "show_on_kiosk","className" => "dt-body-left dt-head-nowrap","title" => "แสดงบน kiosk"],
            ["data" => "show_on_mobile","className" => "dt-body-left dt-head-nowrap","title" => "แสดงบน mobile"],
            ["data" => "service_status","className" => "dt-body-left dt-head-nowrap","title" => "สถานะคิว"],
            ["data" => "actions","className" => "dt-center dt-nowrap","orderable" => false,"title" => "ดำเนินการ"]
        ],
    ],
    'clientEvents' => [
        'error.dt' => 'function ( e, settings, techNote, message ){
            e.preventDefault();
            swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\',});
        }'
    ]
]); ?>