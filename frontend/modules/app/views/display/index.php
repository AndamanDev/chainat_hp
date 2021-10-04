<?php

use frontend\assets\ModernBlinkAsset;
use homer\assets\SocketIOAsset;
use homer\assets\ToastrAsset;
use homer\widgets\Datatables;
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\View;
use yii\helpers\Html;

ModernBlinkAsset::register($this);
// SocketIOAsset::register($this);
ToastrAsset::register($this);

$this->title = 'Display ' . $config['display_name'];

$this->registerCss($this->render('./css/display.css'));
$this->registerCss('
    body {
        background-color: ' . $config['background_color'] . ';
    }
    table.table-display thead tr {
        width: 50%;
        border-radius: 15px;
        border: 5px solid white;
        background-color: ' . $config['header_color'] . ';
        color: ' . $config['font_color'] . ';
        font-weight: bold;
    }
    table.table-display2 thead tr {
        width: 50%;
        border-radius: 15px;
        border: 5px solid white;
        background-color: ' . $config['header_latest_color'] . ';
        color: ' . $config['title_latest_right_color'] . ';
        font-weight: bold;
    }
    table.table-display tbody tr {
        width: 50%;
        border-radius: 15px;
        border: 5px solid white;
        background-color: ' . $config['column_color'] . ';
        color: ' . $config['font_cell_display_color'] . ';
        font-weight: bold;
    }
    table.table-display2 tbody tr {
        width: 50%;
        border-radius: 15px;
        border: 5px solid white;
        background-color: ' . $config['cell_latest_color'] . ';
        color: ' . $config['font_cell_latest_color'] . ';
        font-weight: bold;
    }
    table.table-hold tbody tr td.td-hold-left{
        width: 25%;
        border-top: 5px solid ' . $config['hold_border_color'] . ' !important;
        border-bottom: 5px solid ' . $config['hold_border_color'] . ' !important;
        border-right: 5px solid ' . $config['hold_border_color'] . ' !important;
        border-left: 5px solid ' . $config['hold_border_color'] . ' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        background-color: ' . $config['hold_bg_color'] . ';
        color: ' . $config['hold_font_color'] . ';
        vertical-align: middle;
    }
    table.table-display tbody tr td.td-left{
        border-top: 5px solid ' . $config['border_color'] . ' !important;
        border-bottom: 5px solid ' . $config['border_color'] . ' !important;
        border-left: 5px solid ' . $config['border_color'] . ' !important;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        border: 5px solid #ffffff !important;
        border-radius: 10px;
    }
    table.table-display tbody tr td.td-right{
        border-top: 5px solid ' . $config['border_color'] . ' !important;
        border-bottom: 5px solid ' . $config['border_color'] . ' !important;
        border-right: 5px solid ' . $config['border_color'] . ' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    table.table-display thead tr th.th-right{
        border-top: 5px solid ' . $config['border_color'] . ' !important;
        border-bottom: 5px solid ' . $config['border_color'] . ' !important;
        border-right: 5px solid ' . $config['border_color'] . ' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    table.table-display thead tr th.th-left{
        border-top: 5px solid ' . $config['border_color'] . ' !important;
        border-bottom: 5px solid ' . $config['border_color'] . ' !important;
        border-left: 5px solid ' . $config['border_color'] . ' !important;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        border: 5px solid #ffffff !important;
        border-radius: 10px;
    }
    /*  */
    table.table-display2 tbody tr td.td-left{
        border-top: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-bottom: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-left: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    table.table-display2 tbody tr td.td-right{
        border-top: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-bottom: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-right: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    table.table-display2 thead tr th.th-right{
        border-top: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-bottom: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-right: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
    table.table-display2 thead tr th.th-left{
        border-top: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-bottom: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-left: 5px solid ' . $config['border_cell_latest_color'] . ' !important;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    .vertical-align-middle{
        vertical-align: middle !important;
    }
');
$this->registerJs('var counter = ' . Json::encode($counter) . '; ', View::POS_HEAD);
$this->registerJs('var config = ' . Json::encode($config) . '; ', View::POS_HEAD);
?>
<div class="container">
    <div class="row">
        <div class="col-xs-8 col-sm-8 col-md-8 border-right" style="text-align: center;">
            <h1 class="text-success" style="color: <?= $config['title_left_color']; ?>"><?= $config['title_left'] ?></h1>
        </div>
        <?php /*
	    <div class="col-xs-4 col-sm-4 col-md-4 border-right" style="text-align: center;">
	        <h1 class="text-success" style="color: <?= $config['title_right_color']; ?>"><?= $config['title_right'] ?></h1>
	    </div>
	    */ ?>
        <div class="col-xs-4 col-sm-4 col-md-4" style="text-align: center;">
            <h1 class="text-success" style="color: <?= $config['title_latest_color']; ?>"><?= $config['title_latest'] ?></h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8 col-sm-8 col-md-8">
            <table class="table table-display" id="table-display" width="100%">
                <thead>
                    <tr>
                        <th style="width: 100%;color: <?= $config['table_title_left_color']; ?>" class="th-left">
                            <div style="display: flex;">
                                <div style="width: 50%"><?= $config['table_title_left'] ?></div>
                                <div style="width: 50%"><?= $config['table_title_right'] ?></div>
                            </div>
                        </th>
                        <th></th>
                        <th></th>

                        <?php /*
                        <th style="width: 50%;color: <?= $config['table_title_left_color']; ?>" class="th-left"><?= $config['table_title_left'] ?></th>
                        
                        <th style="width: 50%;color: <?= $config['table_title_right_color']; ?>" class="th-right"><?= $config['table_title_right'] ?></th>
                   */ ?>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4">
            <table class="table table-display2" id="table-display2" width="100%">
                <thead>
                    <tr>
                        <th></th>
                        <th style="width: 50%;color: <?= $config['title_latest_right_color']; ?>" class="th-left">#</th>
                        <th style="width: 50%;color: <?= $config['title_latest_right_color']; ?>" class="th-right"><?= $config['title_latest_right']; ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <table class="table table-hold" id="table-hold" width="100%">
                <tbody>
                    <tr>
                        <td class="td-hold-left"><?= $config['hold_label'] ?></td>
                        <td class="td-hold-right"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($config['text_marquee'])) : ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <marquee id="marquee" style="color: <?= $config['font_marquee_color'] ?>;" direction="left"><?= $config['text_marquee'] ?></marquee>
            </div>
        </div>
    <?php endif; ?>
</div>

<div style="position: fixed;left: 0;right: 0;bottom: 0; display:none">
<?php echo $this->render('play-sound' ,['model' => $station]) ?>
</div>




<?php
#Table Display
echo Datatables::widget([
    'id' => 'table-display',
    'select2' => true,
    'clientOptions' => [
        'ajax' => [
            'url' => Url::to(['/displaylist', 'id' => $config['display_ids']]) ,
            // 'data' => [
            //     'config' => $config
            // ],
            'type' => 'GET'
        ],
        "dom" => "t",
        // "language" => array_merge(Yii::$app->params['dtLanguage'], [
        //     "search" => "_INPUT_ ",
        //     "emptyTable" => "-",
        // ]),
        "pageLength" => empty($config['display_limit']) ? -1 : $config['display_limit'],
        "autoWidth" => false,
        "deferRender" => true,
        "ordering" => false,
        //"order" => [[ 2, "asc" ]],
        "info" => false,
        'columns' => [
            [
                "data" => "q_num",
                "className" => "dt-center dt-head-nowrap th-left td-left",
                "title" => $config['table_title_left'],
                "orderable" => false
            ],
            [
                "data" => "service_number",
                "className" => "dt-body-center dt-head-nowrap th-right td-right vertical-align-middle",
                 "title" => $config['table_title_right'],
                "orderable" => false
            ],
            [
                "data" => "call_timestp",
                "className" => "dt-body-center dt-head-nowrap th-right td-right",
                // "title" => 'call_timestp',
            ],
        ],
        "columnDefs" => [
            ["visible" => false, "targets" => [1, 2]]
        ],
    ],
    'clientEvents' => [
        'error.dt' => 'function ( e, settings, techNote, message ){
            e.preventDefault();
            //swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\'});
            console.warn(message);
        }'
    ]
]);


#Table Hold
echo Datatables::widget([
    'id' => 'table-hold',
    'select2' => true,
    'clientOptions' => [
        'ajax' => [
            'url' => Url::base(true) . '/app/display/data-display-hold',
            'data' => [
                'config' => $config
            ],
            'type' => 'POST'
        ],
        "dom" => "t",
        "language" => array_merge(Yii::$app->params['dtLanguage'], [
            "search" => "_INPUT_ ",
            "emptyTable" => "-"
        ]),
        "pageLength" => empty($config['display_limit']) ? -1 : $config['display_limit'],
        "autoWidth" => false,
        "deferRender" => true,
        "ordering" => false,
        "info" => false,
        "drawCallback" => new JsExpression('function(settings) {
            var api = this.api();
        }'),
        'initComplete' => new JsExpression('
            function () {
                var api = this.api();
                $(".table-hold thead").hide();
            }
        '),
        'columns' => [
            ["data" => null, "defaultContent" => $config['hold_label'], "className" => "dt-center dt-head-nowrap td-hold-left", "orderable" => false],
            ["data" => "q_num", "className" => "dt-body-center dt-head-nowrap td-hold-right"],
        ],
    ],
    'clientEvents' => [
        'error.dt' => 'function ( e, settings, techNote, message ){
            e.preventDefault();
            //swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\'});
            console.warn(message);
        }'
    ]
]);

echo Datatables::widget([
    'id' => 'table-display2',
    'clientOptions' => [
        'ajax' => [
            'url' => Url::base(true) . '/app/display/data-display2',
            'data' => [
                'config' => $config
            ],
            'type' => 'POST'
        ],
        "dom" => "t",
        "language" => array_merge(Yii::$app->params['dtLanguage'], [
            "search" => "_INPUT_ ",
            "emptyTable" => "-"
        ]),
        "pageLength" => empty($config['display_limit']) ? -1 : $config['display_limit'],
        "autoWidth" => false,
        "deferRender" => true,
        "order" => [[0, "asc"]],
        "info" => false,
        "drawCallback" => new JsExpression('function(settings) {
            var api = this.api();
        }'),
        'initComplete' => new JsExpression('
            function () {
                var api = this.api();
                dtFnc.initColumnIndex( api );
            }
        '),
        'columns' => [
            ["data" => null, "defaultContent" => ""],
            [
                "data" => "prefix",
                "className" => "dt-center dt-head-nowrap th-left td-left",
                "title" => "#",
                //"orderable" => false
            ],
            [
                "data" => "qnum",
                "className" => "dt-body-center dt-head-nowrap th-right td-right",
                "title" => $config['title_latest_right'],
                "orderable" => false
            ],
        ],
        "columnDefs" => [
            [
                "targets" => [0],
                "visible" => false
            ]
        ],
    ],
    'clientEvents' => [
        'error.dt' => 'function ( e, settings, techNote, message ){
            e.preventDefault();
            //swal({title: \'Error...!\',html: \'<small>\'+message+\'</small>\',type: \'error\'});
            console.warn(message);
        }'
    ]
]);
?>
<?php
$this->registerJsFile(
    '@web/vendor/moment/moment.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJsFile(
    '@web/js/socket.io.min.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJs(
    <<<JS
socket
.on('display', (res) => {
    console.log(res)
	if(Display.checkService(res)) {
        Display.reloadDisplay();
        Display.reloadDisplay2();
		Display.reloadHold();
		setTimeout(function(){
			if(undefined != dt_tabledisplay.row( '#' + res.artist.data.caller_ids ).data()){
			}else{
                /* var th = moment().locale('th');
				dt_tabledisplay.row( ':eq(0)' ).remove();
				dt_tabledisplay.row.add( {
			        "q_num": '<text class="'+res.title+'">' + res.title + '</text>',
                    "service_number": '<text class="'+res.title+'">' + res.artist.counter.counterservice_callnumber + '</text>',
                    "call_timestp": th.format("YYYY-MM-DD H:mm:ss")
			    } ).draw().node(); */
			}
			Display.blink(res);
		}, 1000);
    }
})
.on('hold', (res) => {
	if( jQuery.inArray((res.modelQueue.serviceid).toString(), config.service_id) != -1 && jQuery.inArray((res.counter.counterservice_type).toString(), config.counterservice_id) != -1) {
        Display.reloadDisplay();
        Display.reloadDisplay2();
		Display.reloadHold();
        Display.removeRow(res);
        Display.addRows();
	}
})
.on('finish', (res) => {
	if( jQuery.inArray((res.modelQueue.serviceid).toString(), config.service_id) != -1 && jQuery.inArray((res.counter.counterservice_type).toString(), config.counterservice_id) != -1) {
        Display.reloadDisplay();
        Display.reloadDisplay2();
		Display.reloadHold();
        Display.removeRow(res);
        Display.addRows();
	}
});

Display = {
	blink: function(res){//สั่งกระพริบ
		//$("td.td-left text, td.td-right text").removeClass("modernBlink");
		$('td.td-left text.' + res.title + ', td.td-right text.'+ res.title).modernBlink({
			duration: 1000,
			iterationCount: 7,
			auto: true
		});
		//$("text." + res.title).addClass("modernBlink");
    },
    addRows: function(){
        var th = moment().locale('th');
        var data = dt_tabledisplay.rows().data();
        if(data.length < config.display_limit){
            for (i = data.length; i < config.display_limit; i++) { 
                dt_tabledisplay.row.add( {
                    "q_num": '<text>-</text>',
                    "service_number": '<text>-</text>',
                    "call_timestp": th.format("YYYY-MM-DD H:mm:ss")
                } ).draw().node();
            }
        }
    },
    reloadHold: function(){
        dt_tablehold.ajax.reload();//โหลดข้อมูลแสดงผล
    },
    reloadDisplay: function(){
        dt_tabledisplay.ajax.reload();//โหลดข้อมูลแสดงผล
    },
    reloadDisplay2: function(){
        dt_tabledisplay2.ajax.reload();//โหลดข้อมูลแสดงผล
    },
    removeRow: function(res){
        dt_tabledisplay.row( '#' + res.data.caller_ids ).remove().draw();
    },
    checkService: function(res){
        if( jQuery.inArray((res.artist.modelQueue.serviceid).toString(), config.service_id) != -1 && jQuery.inArray((res.artist.counter.counterservice_type).toString(), config.counterservice_id) != -1) {
            return true;
        }else{
            return false;
        }
    }
};

var order = 'asc';
setInterval(function(){
    var dataOnShow = [];
    var temp = [];
    var rowsData = dt_tabledisplay2.rows().data();
    if(rowsData.length > config.display_limit){
        if(order == 'asc'){
            order = 'desc';
        }else{
            order = 'asc';
        }
        dt_tabledisplay2.order( [ 0, order ] ).draw();
    }
}, 5000);
JS
);
?>