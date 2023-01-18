<?php
use homer\widgets\Table;
?>
<div id="tab-watting" class="tab-pane active">
    <div class="panel-body" style="padding-buttom: 0px;">
        <div class="row">
            <div class="col-md-12 text-center text-tablet-mode" style="display: none;">
                <p>
                    <span class="label label-primary" style="font-weight: bold;text-align: center;font-size: 1em;">คิวรอเรียก</span>
                </p>
            </div>
        </div>
        <?php  
        echo Table::widget([
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered table-condensed','width' => '100%','id' => 'tb-waiting'],
            //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
            'beforeHeader' => [
                [
                    'columns' => [
                        ['content' => '#','options' => ['style' => 'text-align: center;width: 35px;']],
                        ['content' => '#','options' => ['style' => 'text-align: center;width: 35px;']],
                        ['content' => 'คิว','options' => ['style' => 'text-align: center;']],
                        ['content' => 'HN','options' => ['style' => 'text-align: center;']],
                        ['content' => 'QN','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ชื่อ','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ประเภท','options' => ['style' => 'text-align: center;']],
                        ['content' => 'สถานะ','options' => ['style' => 'text-align: center;']],
                        ['content' => 'วันที่','options' => ['style' => 'text-align: center;']],
                        ['content' => 'เวลา','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ดำเนินการ','options' => ['style' => 'text-align: center;width: 35px;']],
                    ],
                    'options' => ['style' => 'background-color:cornsilk;'],
                ]
            ],
        ]);
        ?>
    </div>
</div>
<div id="tab-calling" class="tab-pane">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12 text-center text-tablet-mode" style="display: none;">
                <p>
                    <span class="label label-primary" style="font-weight: bold;text-align: center;font-size: 1em;">คิวกำลังเรียก</span>
                </p>
            </div>
        </div>
        <?php  
        echo Table::widget([
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered table-condensed','width' => '100%','id' => 'tb-calling'],
            //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
            'beforeHeader' => [
                [
                    'columns' => [
                        ['content' => '#','options' => ['style' => 'text-align: center;width: 35px;']],
                        ['content' => 'คิว','options' => ['style' => 'text-align: center;']],
                        ['content' => 'HN','options' => ['style' => 'text-align: center;']],
                        ['content' => 'QN','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ชื่อ','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ประเภท','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ห้อง/ช่อง/โต๊ะ','options' => ['style' => 'text-align: center;']],
                        // ['content' => 'สถานะ','options' => ['style' => 'text-align: center;']],
                        ['content' => 'วันที่','options' => ['style' => 'text-align: center;']],
                        ['content' => 'เวลา','options' => ['style' => 'text-align: center;']],
                        ['content' => 'เวลาเรียกคิว','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ดำเนินการ','options' => ['style' => 'text-align: center;width:200px;']],
                    ],
                    'options' => ['style' => 'background-color:cornsilk;'],
                ]
            ],
        ]);
        ?>
    </div><!-- End panel body -->
</div>
<div id="tab-hold" class="tab-pane">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12 text-center text-tablet-mode" style="display: none;">
                <p>
                    <span class="label label-primary" style="font-weight: bold;text-align: center;font-size: 1em;">พักคิว</span>
                </p>
            </div>
        </div>
        <?php  
        echo Table::widget([
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered table-condensed','width' => '100%','id' => 'tb-hold'],
            //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
            'beforeHeader' => [
                [
                    'columns' => [
                        ['content' => '#','options' => ['style' => 'text-align: center;width: 35px;']],
                        ['content' => 'คิว','options' => ['style' => 'text-align: center;']],
                        ['content' => 'HN','options' => ['style' => 'text-align: center;']],
                        ['content' => 'QN','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ชื่อ','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ประเภท','options' => ['style' => 'text-align: center;']],
                        ['content' => 'จุดบริการ','options' => ['style' => 'text-align: center;']],
                        // ['content' => 'สถานะ','options' => ['style' => 'text-align: center;']],
                        ['content' => 'วันที่','options' => ['style' => 'text-align: center;']],
                        ['content' => 'เวลา','options' => ['style' => 'text-align: center;']],
                        ['content' => 'เวลาพักคิว','options' => ['style' => 'text-align: center;']],
                        ['content' => 'ดำเนินการ','options' => ['style' => 'text-align: center;width:150px;']],
                    ],
                    'options' => ['style' => 'background-color:cornsilk;'],
                ]
            ],
        ]);
        ?>
    </div>
</div>