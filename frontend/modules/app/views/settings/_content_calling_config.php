<?php
use homer\widgets\Table;
use homer\widgets\Datatables;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\icons\Icon;
use yii\helpers\Url;
use kartik\switchinput\SwitchInputAsset;

SwitchInputAsset::register($this);
$this->registerCss(
 <<<CSS
 

/* The switch - the box around the slider */
.switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 19px;
}

/* Hide default HTML checkbox */
.switch input {display:none;}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 17px;
    left: 3px;
    bottom: 4px;
    top: 1px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input.default:checked + .slider {
  background-color: #444;
}
input.primary:checked + .slider {
  background-color: #2196F3;
}
input.success:checked + .slider {
  background-color: #62cb31;
}
input.info:checked + .slider {
  background-color: #3de0f5;
}
input.warning:checked + .slider {
  background-color: #FFC107;
}
input.danger:checked + .slider {
  background-color: #f44336;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
CSS
);
?>
<div class="panel-body">
    <?php  
    echo Table::widget([
        'tableOptions' => ['class' => 'table table-hover','width' => '100%','id' => 'tb-calling-config'],
        'beforeHeader' => [
            [
                'columns' => [
                    ['content' => '#','options' => []],
                    ['content' => 'จำนวนคิวที่แจ้งเตือน','options' => []],
                    ['content' => 'สถานะ','options' => []],
                    // ['content' => 'action','options' => ['style' => 'text-align:center']],
                    ['content' => 'ดำเนินการ','options' => []],
                ]
            ]
        ],
    ]);
    ?>
</div>

<?= Datatables::widget([
    'id' => 'tb-calling-config',
    'clientOptions' => [
        'ajax' => [
            // 'url' => Url::base(true).'/app/settings/data-calling-config'
          'url' => '/node/api/v1/setting/calling-configs',
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
            "search" => "_INPUT_ " . Html::a(Icon::show('plus').' เพิ่มรายการ', ['/app/settings/create-calling-config'],['class' => 'btn btn-success','role' => 'modal-remote']),
            "searchPlaceholder" => "ค้นหา..."
        ]),
        "pageLength" => 10,
        "lengthMenu" => [ [10, 25, 50, 75, 100], [10, 25, 50, 75, 100] ],
        "autoWidth" => false,
        "deferRender" => true,
        "order" => [[ 1, "asc" ]],
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
                // initCheckbox();
            }
        '),
        'columns' => [
            ["data" => null,"defaultContent" => "", "className" => "dt-center dt-head-nowrap","title" => "#", "orderable" => false],
            ["data" => "notice_queue","className" => "dt-body-left dt-head-nowrap","title" => "จำนวนคิวแจ้งเตือน"],
            [
              "data" => "notice_queue_status",
              "className" => "dt-center dt-nowrap",
              "orderable" => false,
              "title" => "สถานะ",
              "render" => new JsExpression('function (data, type, row, meta) {
                return row.notice_queue_status === 1 ? `<span class="badge badge-success">เปิดใช้งาน</span>` : `<span class="badge badge-danger">ปิดใช้งาน</span>`;
              }')
            ],
            // ["data" => "notice_queue_status1","className" => "dt-center dt-nowrap","orderable" => false,"title" => "เปิด/ปิด ใช้งาน"],
            [
              "data" => null,
              "defaultContent" => "",
              "className" => "dt-center dt-nowrap",
              "orderable" => false,
              "title" => "ดำเนินการ",
              "render" => new JsExpression('function (data, type, row, meta) {
                return `<a href="/app/settings/update-calling-config?id=${row.calling_id}" class="btn btn-success btn-sm" role="modal-remote" title="แก้ไข"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                <a href="/app/settings/delete-calling-config?id=${row.calling_id}" class="btn btn-danger btn-sm" title="ลบ" data-method="post" data-pjax="0" data-confirm="คุณแน่ใจหรือไม่ที่จะลบรายการนี้?"><i class="fa fa-trash-o" aria-hidden="true"></i></a>`
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

<?php 
$this->registerJs(<<<JS
function initCheckbox(){
    $('#tb-calling-config tbody').on('change', 'input[type="checkbox"]', function(e){
        var value = 0;
        var id = $(this).data('key')
        if(e.target.checked){
            value = 1;
        }else{
            value = 0;
        }
        $.ajax({
            method: "post",
            url: "/app/settings/save-status-notice-queue",
            data:{
                value:value,
                id:id
            },
            dataType: "json",
            success:function(){
                var table = $('#tb-calling-config').DataTable();
                table.ajax.reload();
               // initCheckbox();
            },
            error:function(jqXHR,  textStatus,  errorThrown){
                alert(errorThrown)
            }
        });
    })
}
window.initCheckbox = initCheckbox
JS
);

?>