<?php

use frontend\assets\ModernBlinkAsset;
use homer\assets\DatatablesAsset;
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
DatatablesAsset::register($this);

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
    .blink {
        animation: blinker 1s linear infinite;
    }
    
    @keyframes blinker {
        50% {
            opacity: 0;
        }
    }
');
$this->registerJs('var counter = ' . Json::encode($counter) . '; ', View::POS_HEAD);
$this->registerJs('var config = ' . Json::encode($config) . '; ', View::POS_HEAD);
?>
<div id="app" class="container">
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

                        <?php /*
                        <th style="width: 50%;color: <?= $config['table_title_left_color']; ?>" class="th-left"><?= $config['table_title_left'] ?></th>
                        
                        <th style="width: 50%;color: <?= $config['table_title_right_color']; ?>" class="th-right"><?= $config['table_title_right'] ?></th>
                   */ ?>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, key) in filteredQueues" :id="item.caller_ids" :data-key="item.caller_ids" role="row" class="odd">
                        <td colspan="2" class=" dt-center dt-head-nowrap th-left td-left">
                            <table class="table" style="background-color: inherit;margin-bottom: 0px;">
                                <tbody>
                                    <tr style="border:0px;">

                                        <td style="border-top:0px; width: 80%">
                                            <span :class="item.q_num">
                                                {{ item.q_num }}
                                            </span>
                                        </td>

                                        <td rowspan="2" style="border-top:0px; width: 20%;vertical-align: middle;">
                                            <span :class="item.q_num">
                                                {{ item.counterservice_callnumber }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr style="border:0px;">
                                        <td style="border-top:0px; text-align:center;width:80%">
                                            <span :class="item.q_num">
                                                {{ item.pt_name }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4">
            <table class="table table-display2" id="table-display2" width="100%">
                <thead>
                    <tr>
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
    <?php echo $this->render('play-sound', ['model' => $station]) ?>
</div>




<?php
/*
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
*/
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
$this->registerJsFile(
    YII_ENV_DEV ? '@web/js/vue.js' : '@web/js/vue.min.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJsFile(
    '@web/js/lodash.min.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

// $this->registerJS("var config = ". Json::encode($config).";", View::POS_HEAD);

$this->registerJs(
    <<<JS
socket
.on('display', (res) => {
    console.log('display',res)
	if(Display.checkService(res)) {
        // Display.reloadDisplay();
        // Display.reloadDisplay2();
		// Display.reloadHold();
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
        // Display.reloadDisplay();
        // Display.reloadDisplay2();
		// Display.reloadHold();
        // Display.removeRow(res);
        // Display.addRows();
	}
})
.on('finish', (res) => {
    console.log('finish',res)
	if( jQuery.inArray((res.modelQueue.serviceid).toString(), config.service_id) != -1 && jQuery.inArray((res.counter.counterservice_type).toString(), config.counterservice_id) != -1 &&  myPlaylist.playlist.filter(r => r.title === res.modelQueue.q_num).length === 0) {
        // Display.reloadDisplay();
        // Display.reloadDisplay2();
		// Display.reloadHold();
        // Display.removeRow(res);
        // Display.addRows();
	}
});

Display = {
	blink: function(res){//สั่งกระพริบ
		$("td.td-left span.blink, td.td-right span.blink").removeClass("blink");
        // console.log($('td.td-left .' + res.title));
        // if($('td.td-left .' + res.title)) {
        //     $('td.td-left .' + res.title + ', td.td-right .'+ res.title).removeAttr('style')
        //     $('td.td-left .' + res.title + ', td.td-right .'+ res.title).modernBlink({
        //         duration: 1000,
        //         iterationCount: 7,
        //         auto: true
        //     });
        // }
		$("." + res.title).addClass("blink");
        setTimeout(() => {
            $("." + res.title).removeClass("blink");
        }, 7000);
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
    reloadDisplay: function(q_ids = null){
        if(q_ids) {
            var query = yii.getQueryParams(window.location.search)
            dt_tabledisplay.ajax.url( '/app/display/data-display?id='+ query.id + '&q_ids='+q_ids).load();
        } else {
            dt_tabledisplay.ajax.reload();//โหลดข้อมูลแสดงผล
        }
       
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
// setInterval(function(){
//     var dataOnShow = [];
//     var temp = [];
//     var rowsData = dt_tabledisplay2.rows().data();
//     if(rowsData.length > config.display_limit){
//         if(order == 'asc'){
//             order = 'desc';
//         }else{
//             order = 'asc';
//         }
//         dt_tabledisplay2.order( [ 0, order ] ).draw();
//     }
// }, 5000);

var thold, tlastq;

var app = new Vue({
  el: '#app',
  data: {
    qlist: [],
    caller_ids: null
  },
  mounted() {
      this.initTableLastQueue();
      this.initTableHold();
      this.initSocket();
      this.fatchDataDisplay();
  },
  computed: {
    filteredQueues: function() {
        let rows = _.orderBy(this.qlist, ['call_timestp'],['desc'])
        if(this.caller_ids) {
            const item = this.qlist.find(r => parseInt(r.caller_ids) === parseInt(this.caller_ids))
            // rows = rows.filter(r => parseInt(r.caller_ids) <= this.caller_ids)
            if(item) {
                rows = rows.filter(r => parseFloat(moment(r.call_timestp).format('X')) <= parseFloat(moment(item.call_timestp).format('X')))
            }
        }
        var th = moment().locale('th');
        const limit = config.display_limit || 2
        let items = []
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if(items.length < limit) {
                items.push(row)
            }
        }
        if(items.length < limit) {
            const item = {
                q_num: "-",
                caller_ids: "-",
                pt_name: "-",
                counterservice_callnumber: "-",
                call_timestp: th.format("YYYY-MM-DD H:mm:ss")
            }
            
            for (let i = 0; i < limit; i++) {
                if(items.length < limit) {
                    items.push(item)
                }
            }
        }
        return _.orderBy(items, ['call_timestp'],['desc'])
    }
  },
  methods: {
        initTableLastQueue: function() {
            tlastq = $('#table-display2').DataTable({
                dom: "t",
                ajax: {
                    url: "/app/display/data-display2",
                    type: "POST",
                    data: { config: config }
                },
                language: {
                    emptyTable: '-'
                },
                pageLength: config.display_limit || -1,
                autoWidth: false,
                deferRender: true,
                ordering: false,
                order: [[0, "asc"]],
                info: false,
                columns: [
                    {
                        data: "prefix",
                        className: "dt-center dt-head-nowrap th-left td-left",
                        title: "#"
                    },
                    {
                        data: "qnum",
                        className: "dt-body-center dt-head-nowrap th-right td-right",
                        orderable: false,
                        title: config.title_latest_right
                    }
                ]
            });
        },
        initTableHold: function() {
            thold = $('#table-hold').DataTable({
                dom: "t",
                ajax: {
                    url: "/app/display/data-display-hold",
                    type: "POST",
                    data: { config: config }
                },
                language: {
                    emptyTable: '-'
                },
                pageLength: config.display_limit || -1,
                autoWidth: false,
                deferRender: true,
                ordering: false,
                order: [[0, "asc"]],
                info: false,
                columns: [
                    {
                        data: "",
                        defaultContent: config.hold_label,
                        className: "dt-center dt-head-nowrap td-hold-left",
                        orderable: false,
                    },
                    {
                        data: "q_num",
                        className: "dt-body-center dt-head-nowrap td-hold-right",
                        orderable: false,
                    }
                ],
                initComplete: function(settings, json) {
                    $(".table-hold thead").hide();
                }
            });
        },
        initSocket: function() {
            const _this = this
            socket.on('call', (res) => {
                if(model != null && Object.keys(model).length && myPlaylist.playlist.filter(r => r.title === res.modelQueue.q_num).length === 0){
                    var counters = (model.counterserviceid).split(',').map(v => parseInt(v));
                    if(jQuery.inArray(parseInt(res.counter.counterserviceid), counters) != -1) {
                        if(jQuery.inArray((res.modelQueue.serviceid).toString(), config.service_id) != -1 && jQuery.inArray((res.counter.counterservice_type).toString(), config.counterservice_id) != -1) {
                            tlastq.ajax.reload();//โหลดข้อมูลคิวล่าสุด
                            thold.ajax.reload();//โหลดข้อมูลคิวพัก
                            setTimeout(function(){
                                Queue.addMedia(res);
                            }, 500);
                            const item = _this.qlist.find(r => r.q_num === res.modelQueue.q_num)
                            if(item){
                                _this.qlist = _this.qlist.filter(row => row !== item)
                            }
                            _this.qlist.push({
                                q_num: res.modelQueue.q_num,
                                caller_ids: res.modelCaller.caller_ids,
                                pt_name: res.modelQueue.pt_name,
                                counterservice_callnumber: res.counter.counterservice_callnumber,
                                call_timestp: res.modelCaller.call_timestp
                            })
                           
                        }
                    }
                }
            }).on('hold', (res) => {
                _this.qlist = _this.qlist.filter(row => parseInt(row.caller_ids) !== parseInt(res.modelCaller.caller_ids))
                if( jQuery.inArray((res.modelQueue.serviceid).toString(), config.service_id) != -1 && jQuery.inArray((res.counter.counterservice_type).toString(), config.counterservice_id) != -1) {
                    tlastq.ajax.reload();//โหลดข้อมูลคิวล่าสุด
                    thold.ajax.reload();//โหลดข้อมูลคิวพัก
                }
            }).on('finish', (res) => {
                if(myPlaylist.playlist.filter(r => r.title === res.modelQueue.q_num).length === 0) {
                    _this.qlist = _this.qlist.filter(row => parseInt(row.caller_ids) !== parseInt(res.modelCaller.caller_ids))
                }
                if( jQuery.inArray((res.modelQueue.serviceid).toString(), config.service_id) != -1 && jQuery.inArray((res.counter.counterservice_type).toString(), config.counterservice_id) != -1 &&  myPlaylist.playlist.filter(r => r.title === res.modelQueue.q_num).length === 0) {
                    tlastq.ajax.reload();//โหลดข้อมูลคิวล่าสุด
                    thold.ajax.reload();//โหลดข้อมูลคิวพัก
                }
            }).on('display', (res) => {
                if(Display.checkService(res)) {
                    tlastq.ajax.reload();//โหลดข้อมูลคิวล่าสุด
                    thold.ajax.reload();//โหลดข้อมูลคิวพัก
                    setTimeout(() => {
                        Display.blink(res);
                    }, 500);
                }
            });
        },
        fatchDataDisplay: function() {
            var _this = this
            $.ajax({
                method: "POST",
                url: "/app/display/data-display?id=" + config.display_ids,
                data: { config: config },
                dataType: "json",
                success: function(res) {
                    for (let i = 0; i < res.query.length; i++) {
                        const row = res.query[i];
                        // if(i === 0) {
                        //     _this.caller_ids = parseInt(row.caller_ids)
                        // }
                        _this.qlist.push({
                            q_num: row.q_num,
                            caller_ids: row.caller_ids,
                            pt_name: row.pt_name,
                            counterservice_callnumber: row.counterservice_callnumber,
                            call_timestp: row.call_timestp
                        })
                    }
                }
            });
        }
  },
})
JS
);
?>