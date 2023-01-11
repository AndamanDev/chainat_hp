<?php
/* @var $this yii\web\View */
use homer\assets\SocketIOAsset;
use homer\assets\ToastrAsset;
use yii\widgets\Pjax;
use homer\widgets\highcharts\Highcharts;
use yii\web\JsExpression;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Html;

SocketIOAsset::register($this);
ToastrAsset::register($this);

$this->title = \Yii::$app->keyStorage->get('app-name', Yii::$app->name);
$col = 1;
$class = ['primary','info','danger','success','warning'];
$this->registerCss(<<<CSS
    .border-left {
        border-left: 1px solid #e4e5e7;
    }
CSS
);
?>
    <?php Pjax::begin([
        'id' => 'pjax-dashboard'
    ]); ?>
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
                            <?php foreach($data as $item): ?>
                                <?php $badgeClass = $class[array_rand($class)]; ?>
                                <tr>
                                    <td>
                                        <i class="pe-7s-users fa-2x"></i> <span style="font-size: 18px;"><?= $item['service_name']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span style="font-size: 18px;" class="badge badge-<?= $badgeClass ?>"><?= $item['count']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span style="font-size: 18px;" class="badge badge-<?= $badgeClass ?>"><?= $item['wait']; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
                <div class="col-md-7">
                    <?php
                    echo Highcharts::widget([
                        'options' => [
                            "chart" => [
                                "type" => 'bar'
                            ],
                            'title' => ['text' => 'กราฟแสดงผลจำนวนผู้ที่มาใช้บริการ'],
                            'xAxis' => [
                                'categories' => $categories,
                            ],
                            'yAxis' => [
                                'min' => 0,
                                'title' => [
                                    'text' => 'จำนวนคิว',
                                    'align' => 'high'
                                ],
                                'labels' => [
                                    'overflow' => 'justify'
                                ]
                            ],
                            'tooltip' => [
                                'valueSuffix' => ' คิว'
                            ],
                            'legend' => [
                                //'reversed' => true,
                                'layout' => 'vertical',
                                'align' => 'right',
                                'verticalAlign' => 'top',
                                'x' => -40,
                                'y' => 80,
                                'floating' => true,
                                'borderWidth' => 1,
                                'backgroundColor' => new JsExpression(' ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || \'#FFFFFF\') '),
                                'shadow' => true
                            ],
                            'plotOptions' => [
                                // 'series' => [
                                //     'stacking' => 'normal'
                                // ],
                                'bar' => [
                                    'dataLabels' => [
                                        'enabled' => true
                                    ]
                                ]
                            ],
                            'credits' => [
                                'enabled' => false
                            ],
                            'series' => $series
                        ],
                        'scripts' => [
                            'modules/exporting', // adds Exporting button/menu to chart
                            //'themes/grid'        // applies global 'grid' theme to all charts
                        ],
                    ]);
                    ?>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-12">
                    <?php
                    echo Highcharts::widget([
                        'options' => [
                            "chart" => [
                                "type" => 'column'
                            ],
                            'title' => ['text' => 'กราฟแสดงจำนวนผู้ที่มาใช้บริการตามช่วงเวลา'],
                            'subtitle' => [
                                'text' => 'สามารถคลิกที่แท่งกราฟเพื่อดูจำนวนแยกตามประเภทบริการได้'
                            ],
                            'xAxis' => [
                                'type' => 'category',
                                'labels' => [
                                    'rotation' => 45
                                ]
                            ],
                            'yAxis' => [
                                'min' => 0,
                                'title' => [
                                    'text' => 'จำนวน'
                                ]
                            ],
                            'tooltip' => [
                                'headerFormat' => '<span style="font-size:11px">{series.name}</span><br>',
                                'pointFormat' => '<span style="color:{point.color}">{point.name}</span>: <b>จำนวน {point.y} คิว</b><br/>'
                            ],
                            'plotOptions' => [
                                'series' => [
                                    'borderWidth' => 0,
                                    // 'dataLabels' => [
                                    //     'enabled' => true,
                                    //     'format' => '{point.y}'
                                    // ]
                                ]
                            ],
                            'series' => [
                                [
                                    'name' => 'ช่วงเวลา',
                                    "colorByPoint" => true,
                                    'data' => $series2
                                ]
                            ],
                            'legend' => [
                                'enabled' => false
                            ],
                            "drilldown" => [
                                "series" => $subseries2
                            ],
                            'credits' => [
                                'enabled' => false
                            ],
                        ],
                        'scripts' => [
                            'modules/exporting', // adds Exporting button/menu to chart
                            'modules/drilldown'
                            //'themes/grid'        // applies global 'grid' theme to all charts
                        ],
                    ]);
                    ?>
                </div>
            </div>
            <br>
        </div>
    <?php Pjax::end(); ?>
<?php
$this->registerJs(<<<JS
$(function() {
    //hidden menu
    //$('body').addClass('hide-sidebar');
    //socket events
    socket.on('register', (res) => {
        $.pjax.reload({container:'#pjax-dashboard'});//reload data
        toastr.warning(res.modelQ.pt_name, 'คิวใหม่! #'+res.modelQ.q_num, {timeOut: 5000,positionClass: "toast-top-right"});//alert
    });
});
JS
);
?>
