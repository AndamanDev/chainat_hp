<?php
use yii\bootstrap\Tabs;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\View;
use yii\icons\Icon;
use homer\widgets\Table;
use frontend\modules\app\models\TbCounterservice;
use frontend\modules\app\models\TbServiceProfile;
use yii\helpers\ArrayHelper;
#assets
use homer\assets\SocketIOAsset;
use homer\assets\jPlayerAsset;
use homer\assets\SweetAlert2Asset;
use homer\assets\ToastrAsset;
use homer\assets\ICheckAsset;
use homer\assets\HomerAdminAsset;

SweetAlert2Asset::register($this);
ToastrAsset::register($this);
SocketIOAsset::register($this);
jPlayerAsset::register($this);
ICheckAsset::register($this);

$this->title = 'เรียกคิวห้องตรวจ';

$this->registerCss(<<<CSS
/* .normalheader {
	display: none;
} */
html.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown), body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
    overflow-y: unset !important;
}
.nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
    border: 1px solid #74d348;
    border-bottom-color: transparent;
}
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
@media (max-width: 1920px) {
    .radio-inline + .radio-inline, .checkbox-inline + .checkbox-inline {
        margin-left: 0px;
    }
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
.btn-recall,
.btn-hold,
.btn-end,
.btn-calling,
.btn-primary {
    border-radius: 25px;
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
.d-flex {
  display: flex !important;
}
.justify-content-end {
  justify-content: end !important;
}
CSS
);
$this->registerJs('var baseUrl = '.Json::encode(Url::base(true)).'; ',View::POS_HEAD);
$this->registerJs('var modelForm = '.Json::encode($modelForm).'; ',View::POS_HEAD);
$this->registerJs('var modelProfile = '.Json::encode($modelProfile).'; ',View::POS_HEAD);
$this->registerJs('var select2Data = '.Json::encode(ArrayHelper::map(TbCounterservice::find()->where([
        'counterservice_type' => $modelProfile['counterservice_typeid'],
        'counterservice_status' => 1
    ])->asArray()->orderBy(['service_order' => SORT_ASC])->all(),'counterserviceid','counterservice_name')).'; ',View::POS_HEAD);
$this->registerJs('var accesstoken = ' . Json::encode(Yii::$app->user->identity->getAuthKey()) . '; ', View::POS_HEAD);
?>
<div id="app"></div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="hpanel">
        	<?php
            echo Tabs::widget([
                'items' => [
                    [
                        'label' => '<i class="pe-7s-volume"></i> '.$this->title,
                        'active' => true,
                        'options' => ['id' => 'tab-1'],
                        'linkOptions' => ['style' => 'font-size: 14px;'],
                    ],
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
                                <p><span style="font-weight: bold;text-align: center;font-size: 18px;">ห้องตรวจ</span></p>
                            </div>
                        </div>
                		<!-- Begin From -->
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="hpanel panel-form">
                                <div class="panel-heading">
                                    <div class="panel-tools">
                                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                                    </div>
                                    <div class="checkbox" style="display: inline-block;margin-bottom: 0px;">
                                        <label>
                                            <input type="checkbox" value="0" name="tablet-mode" id="tablet-mode">
                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                            <i class="pe-7s-phone"></i> Tablet Mode
                                        </label>
                                    </div>
                                    <div class="checkbox" style="display: inline-block;margin-bottom: 0px;">
                                        <label>
                                            <input type="checkbox" value="0" name="tablet-mode" id="fullscreen-toggler">
                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                            <i class="pe-7s-expand1"></i> Fullscreen
                                        </label>
                                    </div>
                                    <span class="panel-heading-text" style="font-size: 18px;">&nbsp;</span>
                                </div>
                                <div class="panel-body" style="border: 1.5px dashed lightgrey;padding-left: 10px;padding-bottom: 0px;padding-top: 0px;">
                                    <?php
                                    $form = ActiveForm::begin([
                                        'id' => 'calling-form',
                                        'type' => 'horizontal',
                                        'options' => ['autocomplete' => 'off'],
                                        'formConfig' => ['showLabels' => false],
                                    ]) ?>
                                    <div class="form-group" style="margin-bottom: 0px;margin-top: 15px;">
                                        <div class="col-md-3 service_profile">
                                            <?=
                                            $form->field($modelForm, 'service_profile')->widget(Select2::classname(), [
                                                'data' => ArrayHelper::map(Yii::$app->db->createCommand('SELECT
                                                tb_service_profile.*,
                                                ( SELECT COUNT( tb_service.serviceid ) FROM tb_service WHERE tb_service.serviceid IN ( tb_service_profile.service_id ) AND tb_service.service_type_id in (2) ) AS a 
                                            FROM
                                                tb_service_profile 
                                            HAVING
                                                a > 0')
                                                    ->queryAll(), 'service_profile_id', 'service_name'),
                                                'options' => ['placeholder' => 'เลือกโปรไฟล์...'],
                                                'pluginOptions' => [
                                                    'allowClear' => true
                                                ],
                                                'theme' => Select2::THEME_BOOTSTRAP,
                                                'size' => Select2::LARGE,
                                                'pluginEvents' => [
                                                    "change" => "function() {
                                                    if($(this).val() != '' && $(this).val() != null){
                                                        location.replace(baseUrl + \"/app/calling/examination-room?profileid=\" + $(this).val());
                                                    }else{
                                                        location.replace(baseUrl + \"/app/calling/examination-room\");
                                                    }
                                                }",
                                                ]
                                            ]);
                                            ?>
                                        </div>

                                        <div class="col-md-3 last-queue">
                                            <ul class="list-group">
                                                <li class="list-group-item" style="font-size:18px;">
                                                    <span class="badge badge-primary" style="font-size:18px;" id="last-queue">-</span>
                                                    คิวที่เรียกล่าสุด
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-md-3">
                                            <?= $form->field($modelForm,'qnum')->textInput([
                                                'class' => 'input-lg',
                                                'placeholder' => 'คีย์หมายเลขคิวที่นี่เพื่อเรียก',
                                                'style' => 'background-color: #434a54;color: white;',
                                                'autofocus' => true
                                            ])->hint(''); ?>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <?= Html::a('CALL NEXT',false,['class' => 'btn btn-lg btn-block btn-primary activity-callnext']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 5px;">
                                        <div class="col-md-12">
                                            <?php // $modelForm->serviceList; ?>
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0px;margin-top: 15px;">
                                        <div class="col-md-12">
                                            <?= $form->field($modelForm, 'counter_service')->checkboxList($modelForm->getDataCounterserviceEx($modelProfile),[
                                                'inline'=>true,
                                                'class' => 'i-checks'
                                            ]) ?>
                                        </div>
                                    </div>
                                    <?php ActiveForm::end() ?>
                                </div><!-- End panel body -->
                            </div><!-- End hpanel -->
                        </div>

                        <div class="form-group call-next-tablet-mode" style="margin-bottom: 5px;display: none">
                            <div class="col-md-9" style="padding-bottom: 5px;">
                                <ul class="list-group">
                                    <li class="list-group-item text-primary" style="font-size:18px;">
                                        <span class="badge badge-primary last-queue" style="font-size:18px;" id="last-queue">-</span>
                                        คิวที่เรียกล่าสุด
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-3">
                                <p>
                                    <?= Html::a('CALL NEXT',false,['class' => 'btn btn-lg btn-block btn-primary activity-callnext']); ?>
                                </p>
                            </div>
                        </div>
                		<!-- End Form -->
                        <div class="col-xs-12 col-sm-12 col-md-6" >
                            <!-- Begin Panel -->
                            <div class="hpanel">
                                <?php
                                echo Tabs::widget([
                                    'items' => [
                                        [
                                            'label' => 'คิวรอเรียก '. Html::tag('span','0',['id' => 'count-waiting','class' => 'badge count-waiting']),
                                            'active' => true,
                                            'options' => ['id' => 'tab-watting'],
                                            'linkOptions' => ['style' => 'font-size: 14px;'],
                                        ],
                                    ],
                                    'options' => ['class' => 'nav nav-tabs','id' => 'tab-menu-default1'],
                                    'encodeLabels' => false,
                                    'renderTabContent' => false,
                                ]);
                                ?>
                                <div class="tab-content">
                                    <div id="tab-watting" class="tab-pane active">
                                        <div class="panel-body" style="padding-bottom: 0px;">
                                            <div class="row">
                                                <div class="col-md-12 text-center text-tablet-mode" style="display: none">
                                                    <p><span class="label label-primary" style="font-weight: bold;text-align: center;font-size: 1em;">คิวรอเรียก</span></p>
                                                </div>
                                            </div>
                                            <?php  
                                            echo Table::widget([
                                                'tableOptions' => ['class' => 'table table-hover table-bordered table-condensed','width' => '100%','id' => 'tb-waiting'],
                                                //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
                                                'beforeHeader' => [
                                                    [
                                                        'columns' => [
                                                            ['content' => '#','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'หมายเลขคิว','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'HN','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'QN','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ประเภท','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ชื่อ-นามสกุล','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'เวลามาถึง','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ห้องตรวจ','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'สถานะ','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'prefix','options' => ['style' => 'text-align: center;']],
                                                            // ['content' => 'ผล Lab','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ดำเนินการ','options' => ['style' => 'text-align: center;']],
                                                        ],
                                                        'options' => ['style' => 'background-color:cornsilk;'],
                                                    ]
                                                ],
                                            ]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- End hpanel -->
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                    		<!-- Begin Panel -->
                    		<div class="hpanel">
                                <?php
                                echo Tabs::widget([
                                    'items' => [
                                        [
                                            'label' => 'กำลังเรียก '. Html::tag('span','0',['id' => 'count-calling','class' => 'badge count-calling']),
                                            'active' => true,
                                            'options' => ['id' => 'tab-calling'],
                                            'linkOptions' => ['style' => 'font-size: 14px;','class' => 'tabx'],
                                            'headerOptions' => ['class' => 'tab-calling']
                                        ],
                                        [
                                            'label' => 'พักคิว '. Html::tag('span','0',['id' => 'count-hold','class' => 'badge count-hold']),
                                            'options' => ['id' => 'tab-hold'],
                                            'linkOptions' => ['style' => 'font-size: 14px;'],
                                            'headerOptions' => ['class' => 'tab-hold']
                                        ],
                                    ],
                                    'options' => ['class' => 'nav nav-tabs','id' => 'tab-menu-default'],
                                    'encodeLabels' => false,
                                    'renderTabContent' => false,
                                ]);
                                ?>
                                <div class="tab-content">
                                    <div id="tab-calling" class="tab-pane active">
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-12 text-center text-tablet-mode" style="display: none">
                                                    <p><span class="label label-primary" style="font-weight: bold;text-align: center;font-size: 1em;">คิวกำลังเรียก</span></p>
                                                </div>
                                            </div>
                                            <?php  
                                            echo Table::widget([
                                                'tableOptions' => ['class' => 'table table-hover table-bordered table-condensed','width' => '100%','id' => 'tb-calling'],
                                                //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
                                                'beforeHeader' => [
                                                    [
                                                        'columns' => [
                                                            ['content' => '#','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'หมายเลขคิว','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'HN','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'QN','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ประเภท','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ชื่อ-นามสกุล','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ห้องตรวจ/ช่องบริการ','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'เวลามาถึง','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'สถานะ','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'prefix','options' => ['style' => 'text-align: center;']],
                                                            //['content' => 'ผล Lab','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ดำเนินการ','options' => ['style' => 'text-align: center;']],
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
                                                <div class="col-md-12 text-center text-tablet-mode" style="display: none">
                                                    <p><span class="label label-primary" style="font-weight: bold;text-align: center;font-size: 1em;">พักคิว</span></p>
                                                </div>
                                            </div>
                                            <?php  
                                            echo Table::widget([
                                                'tableOptions' => ['class' => 'table table-hover table-bordered table-condensed','width' => '100%','id' => 'tb-hold'],
                                                //'caption' => Html::tag('span','ลงทะเบียนแล้ว',['class' => 'badge badge-success']),
                                                'beforeHeader' => [
                                                    [
                                                        'columns' => [
                                                            ['content' => '#','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'หมายเลขคิว','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'HN','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'QN','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ประเภท','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ชื่อ-นามสกุล','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ห้องตรวจ/ช่องบริการ','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'เวลามาถึง','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'สถานะ','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'prefix','options' => ['style' => 'text-align: center;']],
                                                            //['content' => 'ผล Lab','options' => ['style' => 'text-align: center;']],
                                                            ['content' => 'ดำเนินการ','options' => ['style' => 'text-align: center;']],
                                                        ],
                                                        'options' => ['style' => 'background-color:cornsilk;'],
                                                    ]
                                                ],
                                            ]);
                                            ?>
                                        </div>
                                    </div>
                                </div><!-- End panel body -->
                            </div><!-- End hpanel -->
                        </div>

                	</div>
               	</div>
            </div>
        </div>
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
                                'label' => $icon.' คิวรอเรียก ' . Html::tag('span', '0', ['id' => 'count-waiting', 'class' => 'badge badge-info count-waiting']), 'options' => ['id' => 'tab-watting'],
                                'linkOptions' => ['style' => 'font-size: 14px;'],
                                'headerOptions' => ['style' => 'width: 33.33%;bottom: 20px;','class' => 'tab-watting text-center'],
                            ],
                            [
                                'label' => $icon.' กำลังเรียก ' . Html::tag('span', '0', ['id' => 'count-calling', 'class' => 'badge badge-info count-calling']),
                                'active' => true,
                                'options' => ['id' => 'tab-calling'],
                                'linkOptions' => ['style' => 'font-size: 14px;', 'class' => 'tabx'],
                                'headerOptions' => ['style' => 'width: 33.33%;bottom: 20px;','class' => 'tab-calling text-center'],
                            ],
                            [
                                'label' => $icon.' พักคิว ' . Html::tag('span', '0', ['id' => 'count-hold', 'class' => 'badge badge-info count-hold']),
                                'options' => ['id' => 'tab-hold'],
                                'linkOptions' => ['style' => 'font-size: 14px;'],
                                'headerOptions' => ['style' => 'width: 33.33%;bottom: 20px;','class' => 'tab-hold text-center']
                            ],
                        ],
                        'options' => ['class' => 'nav nav-tabs','id' => 'tab-menu'],
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
echo $this->render('_datatables',['modelForm' => $modelForm,'modelProfile' => $modelProfile,'action' => Yii::$app->controller->action->id]);

// $this->registerJs(<<<JS

// JS
// );
$this->registerJs($this->render('examination-room.js'));
?>