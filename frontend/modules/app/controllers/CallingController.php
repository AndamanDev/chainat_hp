<?php

namespace frontend\modules\app\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;
use yii\helpers\Json;
use homer\widgets\tbcolumn\ActionTable;
use homer\widgets\tbcolumn\ColumnTable;
use homer\widgets\tbcolumn\ColumnData;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\db\Expression;
use yii\icons\Icon;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
#models
use frontend\modules\app\models\CallingForm;
use frontend\modules\app\models\TbServiceProfile;
use frontend\modules\app\traits\ModelTrait;
use frontend\modules\app\models\TbCaller;
use frontend\modules\app\models\TbSoundStation;
use frontend\modules\app\models\TbServicegroup;
use frontend\modules\app\models\TbCounterservice;
use frontend\modules\app\models\TbQuequ;
use frontend\modules\app\models\LabItems;
use frontend\modules\app\models\TbQtrans;

class CallingController extends \yii\web\Controller
{
    use ModelTrait;

    const KEY_COUNTER_SESSION = 'calling-counter';
    const KEY_MEDICINE_SESSION = 'calling-medicine';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['play-sound', 'autoload-media', 'update-status'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['play-sound', 'autoload-media', 'update-status'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($profileid = null, $counterid = null)
    {
        $modelForm = new CallingForm();
        $modelProfile = new TbServiceProfile();
        $modelForm->service_profile = $profileid;
        $modelForm->counter_service = $counterid;
        if ($profileid != null) {
            $modelProfile = $this->findModelServiceProfile($profileid);
        }
        return $this->render('index', [
            'modelForm' => $modelForm,
            'modelProfile' => $modelProfile,
        ]);
    }

    public function actionMedical($profileid = null, $counterid = null)
    {
        $services = (new \yii\db\Query())
            ->select(['tb_servicegroup.*', 'tb_service.*'])
            ->from('tb_servicegroup')
            ->innerJoin('tb_service', 'tb_service.service_groupid = tb_servicegroup.servicegroupid')
            ->where(['tb_servicegroup.servicegroupid' => 2, 'tb_service.service_status' => 1])
            ->orderBy(['tb_servicegroup.servicegroup_order' => SORT_ASC])
            ->all();
        $modelForm = new CallingForm();
        $modelProfile = new TbServiceProfile();
        $modelForm->service_profile = $profileid;
        $modelForm->counter_service = $counterid;
        if ($profileid != null) {
            $modelProfile = $this->findModelServiceProfile($profileid);
        }
        return $this->render('medical', [
            'modelForm' => $modelForm,
            'modelProfile' => $modelProfile,
            'services' => $services,
        ]);
    }

    public function actionExaminationRoom($profileid = null)
    {
        $session = Yii::$app->session;
        $key = ($profileid != null) ? self::KEY_COUNTER_SESSION . $profileid : self::KEY_COUNTER_SESSION;
        $modelForm = new CallingForm();
        $modelProfile = new TbServiceProfile();
        $modelForm->service_profile = $profileid;
        if ($session->get($key) !== null) {
            $modelForm->counter_service = $session->get($key);
        }
        if ($profileid != null) {
            $modelProfile = $this->findModelServiceProfile($profileid);
            $counter = TbCounterservice::find()->where(['counterservice_type' => $modelProfile['counterservice_typeid'], 'counterservice_status' => 1])->all();
            //$modelForm->counter_service = $session->get($key) ? $session->get($key) : ArrayHelper::getColumn($counter,'counterserviceid');
        }
        if ($profileid === null) {
            $session->remove($key);
        }
        return $this->render('examination-room', [
            'modelForm' => $modelForm,
            'modelProfile' => $modelProfile,
        ]);
    }

    // Medicine room
    public function actionMedicineRoom($profileid = null)
    {
        $session = Yii::$app->session;
        $key = ($profileid != null) ? self::KEY_MEDICINE_SESSION . $profileid : self::KEY_MEDICINE_SESSION;
        $modelForm = new CallingForm();
        $modelProfile = new TbServiceProfile();
        $modelForm->service_profile = $profileid;
        if ($session->get($key) !== null) {
            $modelForm->counter_service = $session->get($key);
        }
        if ($profileid != null) {
            $modelProfile = $this->findModelServiceProfile($profileid);
            $counter = TbCounterservice::find()->where(['counterservice_type' => $modelProfile['counterservice_typeid'], 'counterservice_status' => 1])->all();
            //$modelForm->counter_service = $session->get($key) ? $session->get($key) : ArrayHelper::getColumn($counter,'counterserviceid');
        }
        if ($profileid === null) {
            $session->remove($key);
        }
        return $this->render('medicine-room', [
            'modelForm' => $modelForm,
            'modelProfile' => $modelProfile,
        ]);
    }

    public function actionPlaySound($stationid = null)
    {
        $model = ($stationid != null) ? TbSoundStation::findOne($stationid) : new TbSoundStation();
        return $this->render('play-sound', [
            'model' => $model,
        ]);
    }

    public function actionDataTbwaitingSr()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();
            $query = (new \yii\db\Query())
                ->select([
                    'tb_qtrans.ids',
                    'tb_qtrans.q_ids',
                    'tb_qtrans.counter_service_id',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_qtrans.service_status_id',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service_status.service_status_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix',
                    'tb_quequ.quickly'
                ])
                ->from('tb_qtrans')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    'tb_quequ.q_status_id' => 1,
                    'tb_qtrans.service_status_id' => 1
                ])
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->leftJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_qtrans.counter_service_id')
                ->leftJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->orderBy(['tb_quequ.quickly' => SORT_DESC, 'checkin_date' => SORT_ASC]);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'service_status_name',
                    ],
                    [
                        'attribute' => 'service_name',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['quickly'] == 1 ? 'คิวด่วน' : $model['service_name'];
                        },
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'quickly',
                    ],
                    [
                        'attribute' => 'checkbox',
                        'value' => function ($model, $key, $index) {
                            return Html::beginTag('div', ['class' => 'checkbox']) .
                                Html::beginTag('label', ['style' => 'font-size: 1.5em']) .
                                Html::checkbox('selection[]', false, ['value' => $key, 'id' => 'checkbox-' . $key]) .
                                Html::tag('span', '<i class="cr-icon fa fa-check"></i>', ['class' => 'cr']) .
                                Html::endTag('label') .
                                Html::endTag('div');
                        },
                        'format' => 'raw',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{call} {end}',
                        'buttons' => [
                            'call' => function ($url, $model, $key) {
                                return Html::a('CALL', $url, ['class' => 'btn btn-success btn-calling', 'data-url' => '/app/calling/call-screening-room']);
                            },
                            'end' => function ($url, $model, $key) {
                                return Html::a('END', $url, ['class' => 'btn btn btn-danger btn-end', 'data-url' => '/app/calling/end-wait-screening-room']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataTbcallingSr()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();

            $query = (new \yii\db\Query())
                ->select([
                    'tb_caller.caller_ids',
                    'tb_caller.q_ids',
                    'tb_caller.qtran_ids',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_caller.servicegroupid',
                    'tb_caller.counter_service_id',
                    'tb_caller.call_timestp',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_service_status.service_status_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix',
                    'tb_quequ.quickly'
                ])
                ->from('tb_caller')
                ->innerJoin('tb_qtrans', 'tb_qtrans.ids = tb_caller.qtran_ids')
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->innerJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    'tb_caller.counter_service_id' => $formData['counter_service'],
                    'tb_caller.call_status' => ['calling', 'callend']
                ])
                ->orderBy(['tb_quequ.quickly' => SORT_DESC, 'tb_caller.call_timestp' => SORT_ASC]);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'caller_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'caller_ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'service_status_name',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge(Icon::show('hourglass-3') . ' ' . $model['service_status_name'], ['class' => 'badge']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_name',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['quickly'] == 1 ? 'คิวด่วน' : $model['service_name'];
                        },
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'quickly',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{recall} {hold} {end}',
                        'buttons' => [
                            'recall' => function ($url, $model, $key) {
                                return Html::a('RECALL', $url, ['class' => 'btn btn-success btn-recall', 'title' => 'RECALL', 'data-url' => '/app/calling/recall-screening-room']);
                            },
                            'hold' => function ($url, $model, $key) {
                                return Html::a('HOLD', $url, ['class' => 'btn btn-warning btn-hold', 'title' => 'HOLD', 'data-url' => '/app/calling/hold-screening-room']);
                            },
                            'end' => function ($url, $model, $key) {
                                return Html::a('END', $url, ['class' => 'btn btn-danger btn-end', 'title' => 'END', 'data-url' => '/app/calling/end-medical']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataTbholdSr()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();

            $query = (new \yii\db\Query())
                ->select([
                    'tb_caller.caller_ids',
                    'tb_caller.q_ids',
                    'tb_caller.qtran_ids',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_caller.servicegroupid',
                    'tb_caller.counter_service_id',
                    'tb_caller.call_timestp',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_service_status.service_status_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix',
                    'tb_quequ.quickly'
                ])
                ->from('tb_caller')
                ->innerJoin('tb_qtrans', 'tb_qtrans.ids = tb_caller.qtran_ids')
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->innerJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    'tb_caller.counter_service_id' => $formData['counter_service'],
                    'tb_caller.call_status' => 'hold'
                ])
                ->orderBy(['tb_quequ.quickly' => SORT_DESC, 'tb_caller.call_timestp' => SORT_ASC]);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'caller_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'caller_ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'service_status_name',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge(Icon::show('hourglass-3') . ' ' . $model['service_status_name'], ['class' => 'badge']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_name',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['quickly'] == 1 ? 'คิวด่วน' : $model['service_name'];
                        },
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'quickly',
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{call} {end}',
                        'buttons' => [
                            'call' => function ($url, $model, $key) {
                                return Html::a('CALL', $url, ['class' => 'btn btn-success btn-calling', 'data-url' => '/app/calling/callhold-screening-room']);
                            },
                            'end' => function ($url, $model, $key) {
                                return Html::a('END', $url, ['class' => 'btn btn-danger btn-end', 'data-url' => '/app/calling/endhold-screening-room']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataTbwaitingEx()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();
            $query = (new \yii\db\Query())
                ->select([
                    'tb_qtrans.ids',
                    'tb_qtrans.q_ids',
                    'tb_qtrans.counter_service_id',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_qtrans.service_status_id',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service_status.service_status_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix'
                ])
                ->from('tb_qtrans')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    'tb_qtrans.counter_service_id' => $formData['counter_service'],
                    'tb_qtrans.service_status_id' => 4
                ])
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->leftJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_qtrans.counter_service_id')
                ->leftJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->orderBy('checkin_date ASC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'service_status_name',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{call} {end}',
                        'buttons' => [
                            'call' => function ($url, $model, $key) {
                                return Html::a('CALL', $url, ['class' => 'btn btn-success btn-calling']);
                            },
                            'end' => function ($url, $model, $key) {
                                return Html::a('ส่งไปรับยา', $url, ['class' => 'btn btn-danger btn-end']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataTbcallingEx()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();

            $query = (new \yii\db\Query())
                ->select([
                    'tb_caller.caller_ids',
                    'tb_caller.q_ids',
                    'tb_caller.qtran_ids',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_caller.servicegroupid',
                    'tb_caller.counter_service_id',
                    'tb_caller.call_timestp',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_service_status.service_status_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix'
                ])
                ->from('tb_caller')
                ->innerJoin('tb_qtrans', 'tb_qtrans.ids = tb_caller.qtran_ids')
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->innerJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    'tb_caller.counter_service_id' => $formData['counter_service'],
                    'tb_caller.call_status' => ['calling', 'callend']
                ])
                ->andWhere(['not', ['tb_qtrans.counter_service_id' => null]])
                ->orderBy('tb_caller.call_timestp ASC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'caller_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'caller_ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'service_status_name',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge(Icon::show('hourglass-3') . ' ' . $model['service_status_name'], ['class' => 'badge']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{recall} {hold} {end} {transfer}',
                        'buttons' => [
                            'recall' => function ($url, $model, $key) {
                                return Html::a('RECALL', $url, ['class' => 'btn btn-success btn-recall']);
                            },
                            'hold' => function ($url, $model, $key) {
                                return Html::a('HOLD', $url, ['class' => 'btn btn-warning btn-hold']);
                            },
                            'end' => function ($url, $model, $key) {
                                return Html::a('END', $url, ['class' => 'btn btn-danger btn-end']);
                            },
                            'transfer' => function ($url, $model, $key) {
                                return Html::a('Transfer', $url, ['class' => 'btn btn-primary btn-transfer']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataTbholdEx()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();

            $query = (new \yii\db\Query())
                ->select([
                    'tb_caller.caller_ids',
                    'tb_caller.q_ids',
                    'tb_caller.qtran_ids',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_caller.servicegroupid',
                    'tb_caller.counter_service_id',
                    'tb_caller.call_timestp',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_service_status.service_status_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix'
                ])
                ->from('tb_caller')
                ->innerJoin('tb_qtrans', 'tb_qtrans.ids = tb_caller.qtran_ids')
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->innerJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    'tb_caller.counter_service_id' => $formData['counter_service'],
                    'tb_caller.call_status' => 'hold'
                ])
                ->orderBy('tb_caller.call_timestp ASC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'caller_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'caller_ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'service_status_name',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge(Icon::show('hourglass-3') . ' ' . $model['service_status_name'], ['class' => 'badge']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{call} {end} {transfer}',
                        'buttons' => [
                            'call' => function ($url, $model, $key) {
                                return Html::a('CALL', $url, ['class' => 'btn btn-success btn-calling']);
                            },
                            'end' => function ($url, $model, $key) {
                                return Html::a('END', $url, ['class' => 'btn btn-danger btn-end']);
                            },
                            'transfer' => function ($url, $model, $key) {
                                return Html::a('Transfer', $url, ['class' => 'btn btn-primary btn-transfer']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataTbwaitingMedicine()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();
            $query = (new \yii\db\Query())
                ->select([
                    'tb_qtrans.ids',
                    'tb_qtrans.q_ids',
                    'tb_qtrans.counter_service_id',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_qtrans.service_status_id',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service_status.service_status_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix',
                    'tb_caller.caller_ids',
                ])
                ->from('tb_qtrans')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    // 'tb_qtrans.counter_service_id' => $formData['counter_service'],
                    'tb_qtrans.service_status_id' => 10
                ])
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->leftJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_qtrans.counter_service_id')
                ->leftJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->leftJoin('tb_caller', 'tb_caller.qtran_ids = tb_qtrans.ids')
                ->groupBy('tb_qtrans.ids')
                ->orderBy('checkin_date ASC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'caller_ids',
                    ],
                    [
                        'attribute' => 'ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'service_status_name',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{call} {transfer}',
                        'buttons' => [
                            'call' => function ($url, $model, $key) {
                                return Html::a('CALL', $url, ['class' => 'btn btn-success btn-calling']);
                            },
                            'transfer' => function ($url, $model, $key) {
                                return Html::a('ส่งกลับห้องตรวจ', $url, ['class' => 'btn btn-primary btn-transfer']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataTbcallingMedicine()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();

            $query = (new \yii\db\Query())
                ->select([
                    'tb_caller.caller_ids',
                    'tb_caller.q_ids',
                    'tb_caller.qtran_ids',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_caller.servicegroupid',
                    'tb_caller.counter_service_id',
                    'tb_caller.call_timestp',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_service_status.service_status_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix'
                ])
                ->from('tb_caller')
                ->innerJoin('tb_qtrans', 'tb_qtrans.ids = tb_caller.qtran_ids')
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->innerJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    'tb_caller.counter_service_id' => $formData['counter_service'],
                    'tb_caller.call_status' => ['calling', 'callend']
                ])
                ->andWhere(['not', ['tb_qtrans.counter_service_id' => null]])
                ->orderBy('tb_caller.call_timestp ASC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'caller_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'caller_ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'service_status_name',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge(Icon::show('hourglass-3') . ' ' . $model['service_status_name'], ['class' => 'badge']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{recall} {hold} {end}',
                        'buttons' => [
                            'recall' => function ($url, $model, $key) {
                                return Html::a('RECALL', $url, ['class' => 'btn btn-success btn-recall']);
                            },
                            'hold' => function ($url, $model, $key) {
                                return Html::a('HOLD', $url, ['class' => 'btn btn-warning btn-hold']);
                            },
                            'end' => function ($url, $model, $key) {
                                return Html::a('END', $url, ['class' => 'btn btn-danger btn-end']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataTbholdMedicine()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $formData = $request->post('modelForm', []);
            $profileData = $request->post('modelProfile', []);
            $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;
            $labItems = $this->findLabs();

            $query = (new \yii\db\Query())
                ->select([
                    'tb_caller.caller_ids',
                    'tb_caller.q_ids',
                    'tb_caller.qtran_ids',
                    'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                    'tb_caller.servicegroupid',
                    'tb_caller.counter_service_id',
                    'tb_caller.call_timestp',
                    'tb_quequ.q_num',
                    'tb_quequ.q_hn',
                    'tb_quequ.pt_name',
                    'tb_service_status.service_status_name',
                    'tb_counterservice.counterservice_name',
                    'tb_service.service_name',
                    'tb_service.serviceid',
                    'tb_service.service_prefix'
                ])
                ->from('tb_caller')
                ->innerJoin('tb_qtrans', 'tb_qtrans.ids = tb_caller.qtran_ids')
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->innerJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
                ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->where([
                    'tb_quequ.serviceid' => $services,
                    'tb_caller.counter_service_id' => $formData['counter_service'],
                    'tb_caller.call_status' => 'hold'
                ])
                ->orderBy('tb_caller.call_timestp ASC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'caller_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'caller_ids',
                    ],
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'q_hn',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'counter_service_id',
                    ],
                    [
                        'attribute' => 'checkin_date',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'qnumber',
                        'value' => function ($model, $key, $index, $column) {
                            return $model['q_num'];
                        },
                    ],
                    [
                        'attribute' => 'service_status_name',
                        'value' => function ($model, $key, $index, $column) {
                            return \kartik\helpers\Html::badge(Icon::show('hourglass-3') . ' ' . $model['service_status_name'], ['class' => 'badge']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'lab_confirm',
                        'value' => function ($model, $key, $index, $column) use ($labItems) {
                            $confirm = $this->checkLab($model['q_hn'], $labItems);
                            if ($confirm == 'N') {
                                return Html::tag('span', 'ยังไม่เสร็จ', ['class' => 'text-warning']);
                            } elseif ($confirm == 'Y') {
                                return Html::tag('span', 'เสร็จแล้ว', ['class' => 'text-success']);
                            } else {
                                return Html::tag('span', 'ไม่มี Lab', ['class' => 'text-danger']);
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{call} {end}',
                        'buttons' => [
                            'call' => function ($url, $model, $key) {
                                return Html::a('CALL', $url, ['class' => 'btn btn-success btn-calling']);
                            },
                            'end' => function ($url, $model, $key) {
                                return Html::a('END', $url, ['class' => 'btn btn-danger btn-end']);
                            },
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionCallScreeningRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                $data = $request->post('data', []);
                $dataForm = $request->post('modelForm', []);
                $dataProfile = $request->post('modelProfile', []);
                $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
                $counter = $this->findModelCounterservice($dataForm['counter_service']);
                $modelQ = $this->findModelQuequ($data['q_ids']);

                $model = new TbCaller();
                $model->q_ids = $data['q_ids'];
                $model->qtran_ids = $data['ids'];
                //$model->servicegroupid = $modelProfile['service_groupid'];
                $model->counter_service_id = $dataForm['counter_service'];
                $model->call_timestp = new Expression('NOW()');
                $model->call_status = TbCaller::STATUS_CALLING;

                $modelTrans = $this->findModelQTrans($data['ids']);
                //$modelTrans->counter_service_id = $dataForm['counter_service'];
                //$modelTrans->servicegroupid = null;
                $modelTrans->service_status_id = 2;

                if ($model->save() && $modelTrans->save()) {
                    $data['counter_service_id'] = $counter['counterserviceid'];
                    $transaction->commit();
                    return [
                        'status' => '200',
                        'message' => 'success',
                        'sound' => $this->getMediaSound($modelQ['q_num'], $model['counter_service_id']),
                        'data' => $data,
                        'model' => $model,
                        'modelQ' => $modelQ,
                        'modelProfile' => $modelProfile,
                        'counter' => $counter,
                        'eventOn' => 'tb-waiting',
                        'state' => 'call'
                    ];
                } else {
                    $transaction->rollBack();
                    return [
                        'status' => '500',
                        'message' => 'error',
                        'validate' => ActiveForm::validate($model)
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndWaitScreeningRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                $data = $request->post('data', []);
                $dataForm = $request->post('modelForm', []);
                $dataProfile = $request->post('modelProfile', []);
                $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
                $counter = $this->findModelCounterservice($dataForm['counter_service']);
                $modelQ = $this->findModelQuequ($data['q_ids']);

                $model = new TbCaller();
                $model->q_ids = $data['q_ids'];
                $model->qtran_ids = $data['ids'];
                //$model->servicegroupid = $modelProfile['service_groupid'];
                $model->counter_service_id = $dataForm['counter_service'];
                $model->call_timestp = new Expression('NOW()');
                $model->call_status = TbCaller::STATUS_END;

                $modelTrans = $this->findModelQTrans($data['ids']);
                //$modelTrans->counter_service_id = $dataForm['counter_service'];
                //$modelTrans->servicegroupid = null;
                $modelTrans->service_status_id = 4;
                $modelTrans->counter_service_id = $request->post('value');

                if ($model->save() && $modelTrans->save()) {
                    $data['counter_service_id'] = $counter['counterserviceid'];
                    $transaction->commit();
                    return [
                        'status' => '200',
                        'message' => 'success',
                        'data' => $data,
                        'model' => $model,
                        'modelQ' => $modelQ,
                        'modelProfile' => $modelProfile,
                        'counter' => $counter,
                        'eventOn' => 'tb-waiting',
                        'state' => 'call'
                    ];
                } else {
                    $transaction->rollBack();
                    return [
                        'status' => '500',
                        'message' => 'error',
                        'validate' => ActiveForm::validate($model)
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionRecallScreeningRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);
            $modelQ = $this->findModelQuequ($data['q_ids']);

            $model = $this->findModelCaller($data['caller_ids']);
            $model->call_timestp = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_CALLING;
            if ($model->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'sound' => $this->getMediaSound($modelQ['q_num'], $model['counter_service_id']),
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-calling',
                    'state' => 'recall'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionHoldScreeningRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQtran->service_status_id = 3;
            $model->call_status = TbCaller::STATUS_HOLD;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-calling',
                    'state' => 'hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionCallholdScreeningRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);
            $modelQ = $this->findModelQuequ($data['q_ids']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQtran->service_status_id = 2;
            $model->call_timestp = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_CALLING;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'sound' => $this->getMediaSound($modelQ['q_num'], $model['counter_service_id']),
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'call-hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndholdScreeningRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQ = $this->findModelQuequ($modelQtran['q_ids']);
            $modelQtran->service_status_id = 4;
            $model->call_status = TbCaller::STATUS_END;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'end-hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($modelQtran)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndScreeningRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQ = $this->findModelQuequ($modelQtran['q_ids']);
            $modelQtran->service_status_id = 4;
            $modelQtran->counter_service_id = $request->post('value');
            $model->call_status = TbCaller::STATUS_FINISHED;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-calling',
                    'state' => 'end'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndMedical()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQ = $this->findModelQuequ($modelQtran['q_ids']);
            $modelQtran->service_status_id = 10;
            $modelQtran->checkout_date = new Expression('NOW()');
            $modelQtran->counter_service_id = $model['counter_service_id'];
            $model->call_status = TbCaller::STATUS_END;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'end-calling'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($modelQtran)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function getMediaSound($qnum, $id)
    {
        $qnum = str_split($qnum);
        $counter = $this->findModelCounterservice($id);
        $modelSound = $counter->tbSound;
        //$counterType = $counter->counterserviceType;
        //$counterSound = $counterType->tbSound;
        $servicesound = $counter->soundService;
        $basePath = "/media/" . $modelSound['sound_path_name'];
        $begin = [$basePath . "/please.wav"];//เชิญหมายเลข
        $end = [
            "/media/" . $servicesound['sound_path_name'] . '/' . $servicesound['sound_name'],
            $basePath . '/' . $modelSound['sound_name'],
            $basePath . '/' . $modelSound['sound_path_name'] . '_Sir.wav',
        ];

        $sound = array_map(function ($num) use ($basePath, $modelSound) {
            return $basePath . '/' . $modelSound['sound_path_name'] . '_' . $num . '.wav';
        }, $qnum);
        $sound = ArrayHelper::merge($begin, $sound);
        $sound = ArrayHelper::merge($sound, $end);
        return $sound;
    }

    public function actionUpdateStatus($ids)
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model = $this->findModelCaller($ids);
            if ($model->call_status == TbCaller::STATUS_CALLING) {
                $model->call_status = TbCaller::STATUS_CALLEND;
            }
            if ($model->save(false)) {
                return [
                    'status' => '200',
                    'message' => 'success',
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionSetCounterSession($page = null)
    {
        $request = Yii::$app->request;
        $session = Yii::$app->session;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $data = $request->post('CallingForm', []);
            if($page == 'medicine-room') {
                $key = self::KEY_MEDICINE_SESSION . $data['service_profile'];
            } else {
                $key = self::KEY_COUNTER_SESSION . $data['service_profile'];
            }
            $session->remove($key);
            $session->set($key, $data['counter_service']);
            return ['key' => $key, 'value' => $session->get($key)];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionCallExaminationRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                $data = $request->post('data', []);
                $dataForm = $request->post('modelForm', []);
                $dataProfile = $request->post('modelProfile', []);
                $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
                $counter = $this->findModelCounterservice($request->post('value'));
                $modelQ = $this->findModelQuequ($data['q_ids']);

                $model = new TbCaller();
                $model->q_ids = $data['q_ids'];
                $model->qtran_ids = $data['ids'];
                //$model->servicegroupid = $modelProfile['service_groupid'];
                $model->counter_service_id = $request->post('value');
                $model->call_timestp = new Expression('NOW()');
                $model->call_status = TbCaller::STATUS_CALLING;

                $modelTrans = $this->findModelQTrans($data['ids']);
                //$modelTrans->counter_service_id = $dataForm['counter_service'];
                //$modelTrans->servicegroupid = $modelProfile['service_groupid'];
                $modelTrans->service_status_id = 2;

                if ($model->save() && $modelTrans->save()) {
                    $transaction->commit();
                    return [
                        'status' => '200',
                        'message' => 'success',
                        'sound' => $this->getMediaSound($modelQ['q_num'], $request->post('value')),
                        'data' => $data,
                        'model' => $model,
                        'modelQ' => $modelQ,
                        'modelProfile' => $modelProfile,
                        'counter' => $counter,
                        'eventOn' => 'tb-waiting',
                        'state' => 'call'
                    ];
                } else {
                    $transaction->rollBack();
                    return [
                        'status' => '500',
                        'message' => 'error',
                        'validate' => ActiveForm::validate($model)
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndWaitExaminationRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                $data = $request->post('data', []);
                $dataForm = $request->post('modelForm', []);
                $dataProfile = $request->post('modelProfile', []);
                $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
                $counter = $this->findModelCounterservice($request->post('value'));
                $modelQ = $this->findModelQuequ($data['q_ids']);

                $model = new TbCaller();
                $model->q_ids = $data['q_ids'];
                $model->qtran_ids = $data['ids'];
                //$model->servicegroupid = $modelProfile['service_groupid'];
                $model->counter_service_id = $request->post('value');
                $model->call_timestp = new Expression('NOW()');
                $model->call_status = TbCaller::STATUS_CALLEND;

                $modelTrans = $this->findModelQTrans($data['ids']);
                //$modelTrans->counter_service_id = $dataForm['counter_service'];
                //$modelTrans->servicegroupid = $modelProfile['service_groupid'];
                $modelTrans->service_status_id = 10;

                if ($model->save() && $modelTrans->save()) {
                    $transaction->commit();
                    return [
                        'status' => '200',
                        'message' => 'success',
                        'sound' => $this->getMediaSound($modelQ['q_num'], $request->post('value')),
                        'data' => $data,
                        'model' => $model,
                        'modelQ' => $modelQ,
                        'modelProfile' => $modelProfile,
                        'counter' => $counter,
                        'eventOn' => 'tb-waiting',
                        'state' => 'end'
                    ];
                } else {
                    $transaction->rollBack();
                    return [
                        'status' => '500',
                        'message' => 'error',
                        'validate' => ActiveForm::validate($model)
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionRecallExaminationRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);
            $modelQ = $this->findModelQuequ($data['q_ids']);

            $model = $this->findModelCaller($data['caller_ids']);
            $model->call_timestp = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_CALLING;
            if ($model->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'sound' => $this->getMediaSound($modelQ['q_num'], $model['counter_service_id']),
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-calling',
                    'state' => 'recall'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionHoldExaminationRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQtran->service_status_id = 3;
            $model->call_status = TbCaller::STATUS_HOLD;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-calling',
                    'state' => 'hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionCallholdExaminationRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);
            $modelQ = $this->findModelQuequ($data['q_ids']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQtran->service_status_id = 2;
            $model->call_timestp = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_CALLING;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'sound' => $this->getMediaSound($modelQ['q_num'], $model['counter_service_id']),
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'call-hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndholdExaminationRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQ = $this->findModelQuequ($modelQtran['q_ids']);
            $modelQtran->service_status_id = 10;
            $modelQtran->checkout_date = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_END;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'end-hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($modelQtran)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndExaminationRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQ = $this->findModelQuequ($modelQtran['q_ids']);
            $modelQtran->service_status_id = 10;
            $modelQtran->checkout_date = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_END;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'end-hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($modelQtran)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionTransferExaminationRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);
            $modelQ = $this->findModelQuequ($data['q_ids']);

            $model = $this->findModelCaller($data['caller_ids']);
            $model->counter_service_id = $request->post('value');
            // $model->call_timestp = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_CALLEND;
            if ($model->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'sound' => $this->getMediaSound($modelQ['q_num'], $model['counter_service_id']),
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-calling',
                    'state' => 'transfer'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionTransferMedicineRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);
            $modelQ = $this->findModelQuequ($data['q_ids']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQtran->service_status_id = 4;
            $model->call_status = TbCaller::STATUS_CALLEND;
            if ($model->save() && $modelQtran->save())  {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-waiting',
                    'state' => 'transfer'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionCallMedicineRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                $data = $request->post('data', []);
                $dataForm = $request->post('modelForm', []);
                $dataProfile = $request->post('modelProfile', []);
                $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
                $counter = $this->findModelCounterservice($request->post('value'));
                $modelQ = $this->findModelQuequ($data['q_ids']);

                $model = new TbCaller();
                $model->q_ids = $data['q_ids'];
                $model->qtran_ids = $data['ids'];
                //$model->servicegroupid = $modelProfile['service_groupid'];
                $model->counter_service_id = $request->post('value');
                $model->call_timestp = new Expression('NOW()');
                $model->call_status = TbCaller::STATUS_CALLING;

                $modelTrans = $this->findModelQTrans($data['ids']);
                //$modelTrans->counter_service_id = $dataForm['counter_service'];
                //$modelTrans->servicegroupid = $modelProfile['service_groupid'];
                $modelTrans->service_status_id = 2;

                if ($model->save() && $modelTrans->save()) {
                    $transaction->commit();
                    return [
                        'status' => '200',
                        'message' => 'success',
                        'sound' => $this->getMediaSound($modelQ['q_num'], $request->post('value')),
                        'data' => $data,
                        'model' => $model,
                        'modelQ' => $modelQ,
                        'modelProfile' => $modelProfile,
                        'counter' => $counter,
                        'eventOn' => 'tb-waiting',
                        'state' => 'call'
                    ];
                } else {
                    $transaction->rollBack();
                    return [
                        'status' => '500',
                        'message' => 'error',
                        'validate' => ActiveForm::validate($model)
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionRecallMedicineRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);
            $modelQ = $this->findModelQuequ($data['q_ids']);

            $model = $this->findModelCaller($data['caller_ids']);
            $model->call_timestp = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_CALLING;
            if ($model->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'sound' => $this->getMediaSound($modelQ['q_num'], $model['counter_service_id']),
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-calling',
                    'state' => 'recall'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionHoldMedicineRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQtran->service_status_id = 3;
            $model->call_status = TbCaller::STATUS_HOLD;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-calling',
                    'state' => 'hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndMedicineRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQ = $this->findModelQuequ($modelQtran['q_ids']);
            $modelQtran->service_status_id = 11;
            $modelQtran->checkout_date = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_END;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'end-hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($modelQtran)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionCallholdMedicineRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);
            $modelQ = $this->findModelQuequ($data['q_ids']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQtran->service_status_id = 2;
            $model->call_timestp = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_CALLING;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'sound' => $this->getMediaSound($modelQ['q_num'], $model['counter_service_id']),
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'call-hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($model)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionEndholdMedicineRoom()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $data = $request->post('data', []);
            $dataForm = $request->post('modelForm', []);
            $dataProfile = $request->post('modelProfile', []);
            $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
            $counter = $this->findModelCounterservice($dataForm['counter_service']);

            $model = $this->findModelCaller($data['caller_ids']);
            $modelQtran = $this->findModelQTrans($model['qtran_ids']);
            $modelQ = $this->findModelQuequ($modelQtran['q_ids']);
            $modelQtran->service_status_id = 11;
            $modelQtran->checkout_date = new Expression('NOW()');
            $model->call_status = TbCaller::STATUS_END;
            if ($model->save() && $modelQtran->save()) {
                return [
                    'status' => '200',
                    'message' => 'success',
                    'data' => $data,
                    'model' => $model,
                    'modelQ' => $modelQ,
                    'modelProfile' => $modelProfile,
                    'counter' => $counter,
                    'eventOn' => 'tb-hold',
                    'state' => 'end-hold'
                ];
            } else {
                return [
                    'status' => '500',
                    'message' => 'error',
                    'validate' => ActiveForm::validate($modelQtran)
                ];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionAutoloadMedia()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $counters = !empty($request->post('counterserviceid')) ? explode(",", $request->post('counterserviceid')) : null;
            $rows = (new \yii\db\Query())
                ->select([
                    'tb_caller.*',
                    'tb_qtrans.ids',
                    'tb_quequ.q_num',
                    'tb_counterservice.*'
                ])
                ->from('tb_caller')
                ->where(['tb_caller.call_status' => TbCaller::STATUS_CALLING, 'tb_caller.counter_service_id' => $counters])
                ->innerJoin('tb_qtrans', 'tb_qtrans.ids = tb_caller.qtran_ids')
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                ->innerJoin('tb_counterservice_type', 'tb_counterservice_type.counterservice_typeid = tb_counterservice.counterservice_type')
                ->orderBy('tb_caller.call_timestp ASC')
                ->all();
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'sound' => $this->getMediaSound($row['q_num'], $row['counter_service_id']),
                    'model' => $row,
                    'counter' => ['counterservice_type' => $row['counterservice_type']],
                    'data' => $row,
                ];
            }
            return [
                'status' => '200',
                'message' => 'success',
                'rows' => $data,
            ];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionSetCidStation()
    {
        $request = Yii::$app->request;
        $session = Yii::$app->session;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $data = $request->post('TbQuequ', []);
            $session->set('cid-station', $data['cid_station']);
            return $data;
        }
    }

    public function actionDataPatientsList($vstdate = null)
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $sql = 'SELECT
                        `patient`.`hn` AS `hn`,
                        `patient`.`pname` AS `pname`,
                        `patient`.`fname` AS `fname`,
                        `patient`.`lname` AS `lname`,
                        `patient`.`cid` AS `cid`,
                        `vn_stat`.`vn` AS `vn`,
                        `vn_stat`.`dx_doctor` AS `dx_doctor`,
                        `vn_stat`.`vstdate` AS `vstdate`,
                        `doctor`.`name` AS `doctor_name`,
                        concat( `patient`.`pname`, `patient`.`fname`, \' \', `patient`.`lname` ) AS `fullname` 
                    FROM
                        (
                        ( `patient` JOIN `vn_stat` ON ( ( `vn_stat`.`hn` = `patient`.`hn` ) ) )
                        LEFT JOIN `doctor` ON ( ( `doctor`.`code` = `vn_stat`.`dx_doctor` ) ) 
                        ) 
                    WHERE
                        ( `vn_stat`.`vstdate` = :vstdate )';
            $params = [':vstdate' => $vstdate != null ? static::convertDate($vstdate, 'php:Y-m-d') : date('Y-m-d')];
            $query = Yii::$app->db_his->createCommand($sql)
                ->bindValues($params)
                ->queryAll();
            $qarray = TbQuequ::find()->where(['not', ['pt_name' => null]])->all();
            $queue = ArrayHelper::getColumn($qarray, 'q_hn');

            $services = (new \yii\db\Query())
                ->select(['tb_servicegroup.*', 'tb_service.*'])
                ->from('tb_servicegroup')
                ->innerJoin('tb_service', 'tb_service.service_groupid = tb_servicegroup.servicegroupid')
                ->where(['tb_servicegroup.servicegroupid' => 2, 'tb_service.service_status' => 1])
                ->orderBy(['tb_servicegroup.servicegroup_order' => SORT_ASC])
                ->all();
            $items = [];
            foreach ($services as $service) {
                $items[] = ['label' => $service['service_name'], 'url' => '#', 'linkOptions' => ['data-key' => $service['serviceid'], 'data-group' => $service['servicegroupid']]];
            }

            $dataProvider = new ArrayDataProvider([
                'allModels' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'hn'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'hn',
                    ],
                    [
                        'attribute' => 'vn',
                    ],
                    [
                        'attribute' => 'cid',
                        'value' => function ($model, $key, $index, $column) {
                            return substr($model['cid'], 0, -3) . '***';
                        },
                    ],
                    [
                        'attribute' => 'vstdate',
                        'format' => ['date', 'php:d/m/Y']
                    ],
                    [
                        'attribute' => 'fullname',
                    ],
                    [
                        'attribute' => 'dx_doctor',
                    ],
                    [
                        'attribute' => 'doctor_name',
                    ],
                    [
                        'attribute' => 'pname',
                    ],
                    [
                        'attribute' => 'fname',
                    ],
                    [
                        'attribute' => 'lname',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{action}',
                        'buttons' => [
                            'action' => function ($url, $model, $key) use ($queue, $items) {
                                if (ArrayHelper::isIn($model['hn'], $queue)) {
                                    return \kartik\helpers\Html::badge('ลงทะเบียนแล้ว', ['class' => 'badge badge-success']);
                                } else {
                                    return \yii\bootstrap\ButtonDropdown::widget([
                                        'label' => 'ลงทะเบียน',
                                        'dropdown' => [
                                            'items' => $items
                                        ],
                                        'options' => ['class' => 'btn btn-xs btn-primary']
                                    ]);;
                                }
                            }
                        ],
                    ]
                ]
            ]);

            return ['data' => $columns->renderDataColumns()];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public static function convertDate($date = null)
    {
        $result = '';
        if (!empty($date)) {
            $arr = explode("/", $date);
            $y = $arr[2];
            $m = $arr[1];
            $d = $arr[0];
            $result = "$y-$m-$d";
        }
        return $result;
    }

    protected function findLabByHN($hn)
    {
        $sql = 'SELECT
                    `vn_stat`.`vn` AS `vn`,
                    `vn_stat`.`hn` AS `hn`,
                    `vn_stat`.`vstdate` AS `vstdate`,
                    concat( `patient`.`pname`, \' \', `patient`.`fname`, \' \', `patient`.`lname` ) AS `pt_name`,
                    `lab_order`.`lab_order_number` AS `lab_order_number`,
                    `lab_order`.`lab_items_code` AS `lab_items_code`,
                    `lab_order`.`lab_order_result` AS `lab_order_result`,
                    `lab_order`.`confirm` AS `confirm`,
                    `lab_items`.`lab_items_name` AS `lab_items_name` 
                FROM
                    (
                    (
                    (
                    ( `lab_order` JOIN `lab_order_service` ON ( ( `lab_order_service`.`lab_order_number` = `lab_order`.`lab_order_number` ) ) )
                    JOIN `vn_stat` ON ( ( `vn_stat`.`vn` = `lab_order_service`.`vn` ) ) 
                    )
                    JOIN `patient` ON ( ( `patient`.`hn` = `vn_stat`.`hn` ) ) 
                    )
                    JOIN `lab_items` ON ( ( `lab_items`.`lab_items_code` = `lab_order`.`lab_items_code` ) ) 
                    ) 
                WHERE
                    `vn_stat`.`vstdate` = :vstdate AND `vn_stat`.`hn` = :hn
                GROUP BY
                    `lab_order`.`lab_order_number` 
                ORDER BY
                    `lab_order`.`lab_order_number`';
        $params = [':vstdate' => date('Y-m-d'), ':hn' => $hn];
        return Yii::$app->db_his->createCommand($sql)->bindValues($params)->queryAll();
    }

    protected function checkLab($hn, $labItems)
    {
        //\Yii::$app->response->format = Response::FORMAT_JSON;
        $confirm = 'N';
        $lab_items_code = ArrayHelper::getColumn($labItems, 'lab_items_code');
        if (!empty($hn)) {
            $labData = $this->findLabByHN($hn);
        } else {
            $labData = false;
        }
        if ($labData) {
            $labItemCodeHIS = ArrayHelper::getColumn($labData, 'lab_items_code');
            $labItemConfirmHIS = ArrayHelper::getColumn($labData, 'confirm');
            foreach ($labItemCodeHIS as $itemCode) {
                if (ArrayHelper::isIn($itemCode, $lab_items_code)) {
                    if (!ArrayHelper::isIn('N', $labItemConfirmHIS)) {
                        $confirm = 'Y';
                    }
                }
            }
        } else {
            $confirm = 'no lab';
        }
        return $confirm;
    }

    public function actionSearchByQnum()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $qnum = $request->post('qnum');
            $modelQ = TbQuequ::findOne(['q_num' => $qnum]);
            if (!$modelQ) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบข้อมูล'
                ];
            }
            $modelQTran = TbQtrans::findOne(['q_ids' => $modelQ['q_ids']]);
            if (!$modelQTran) {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
            if ($modelQTran['service_status_id'] == 1) {//รอเรียก
                if ($modelQ['servicegroupid'] == 1) {//ลงทะเบียน
                    return [
                        'success' => true,
                        'message' => 'รอเรียกคิว (จุดลงทะเบียน)'
                    ];
                } else {//ซักประวัติ
                    return [
                        'success' => true,
                        'message' => 'รอเรียกคิว (ซักประวัติ)'
                    ];
                }
            } elseif ($modelQTran['service_status_id'] == 2) {//เรียกคิว
                $modelCaller = TbCaller::findOne(['qtran_ids' => $modelQTran['ids'], 'q_ids' => $modelQ['q_ids']]);
                if (!$modelCaller) {
                    throw new NotFoundHttpException('The requested page does not exist.');
                }
                if ($modelQ['servicegroupid'] == 1) {//ลงทะเบียน
                    if ($modelCaller['call_status'] == 'calling' || $modelCaller['call_status'] == 'callend') {
                        return [
                            'success' => true,
                            'message' => 'กำลังเรียก (จุดลงทะเบียน) ' . $modelCaller->tbCounterservice->counterservice_name,
                        ];
                    }
                }
                if ($modelQ['servicegroupid'] == 2 && empty($modelQTran['counter_service_id'])) {//
                    if ($modelCaller['call_status'] == 'calling' || $modelCaller['call_status'] == 'callend') {
                        return [
                            'success' => true,
                            'message' => 'กำลังเรียก (ซักประวัติ) ' . $modelCaller->tbCounterservice->counterservice_name,
                        ];
                    }
                }
                if ($modelQ['servicegroupid'] == 2 && !empty($modelQTran['counter_service_id'])) {//
                    $modelCaller = TbCaller::find()->where(['qtran_ids' => $modelQTran['ids'], 'q_ids' => $modelQ['q_ids']])->orderBy('caller_ids DESC')->one();
                    if (!$modelCaller) {
                        throw new NotFoundHttpException('The requested page does not exist.');
                    }
                    if ($modelCaller['call_status'] == 'calling' || $modelCaller['call_status'] == 'callend') {
                        return [
                            'success' => true,
                            'message' => 'กำลังเรียก (ห้องตรวจ) ' . $modelCaller->tbCounterservice->counterservice_name,
                        ];
                    }
                }
            } elseif ($modelQTran['service_status_id'] == 3) {//พักคิว
                $modelCaller = TbCaller::findOne(['qtran_ids' => $modelQTran['ids'], 'q_ids' => $modelQ['q_ids']]);
                if (!$modelCaller) {
                    throw new NotFoundHttpException('The requested page does not exist.');
                }
                if ($modelQ['servicegroupid'] == 1) {//ลงทะเบียน
                    if ($modelCaller['call_status'] == 'hold') {
                        return [
                            'success' => true,
                            'message' => 'พักคิว (จุดลงทะเบียน) ' . $modelCaller->tbCounterservice->counterservice_name,
                        ];
                    }
                }
                if ($modelQ['servicegroupid'] == 2 && empty($modelQTran['counter_service_id'])) {//
                    if ($modelCaller['call_status'] == 'hold') {
                        return [
                            'success' => true,
                            'message' => 'พักคิว (ซักประวัติ) ' . $modelCaller->tbCounterservice->counterservice_name,
                        ];
                    }
                }
                if ($modelQ['servicegroupid'] == 2 && !empty($modelQTran['counter_service_id'])) {//
                    $modelCaller = TbCaller::find()->where(['qtran_ids' => $modelQTran['ids'], 'q_ids' => $modelQ['q_ids']])->orderBy('caller_ids DESC')->one();
                    if (!$modelCaller) {
                        throw new NotFoundHttpException('The requested page does not exist.');
                    }
                    if ($modelCaller['call_status'] == 'hold') {
                        return [
                            'success' => true,
                            'message' => 'พักคิว (ห้องตรวจ) ' . $modelCaller->tbCounterservice->counterservice_name,
                        ];
                    }
                }
            } elseif ($modelQTran['service_status_id'] == 4) {//
                if ($modelQ['servicegroupid'] == 1) {//ลงทะเบียน
                    return [
                        'success' => true,
                        'message' => 'เสร็จสิ้น (จุดลงทะเบียน)'
                    ];
                }
                if ($modelQ['servicegroupid'] == 2 && empty($modelQTran['counter_service_id'])) {//
                    return [
                        'success' => true,
                        'message' => 'เสร็จสิ้น (ซักประวัติ)'
                    ];
                }
                if ($modelQ['servicegroupid'] == 2 && !empty($modelQTran['counter_service_id'])) {//
                    return [
                        'success' => true,
                        'message' => 'รอเรียก (ห้องตรวจ)'
                    ];
                }
            } elseif ($modelQTran['service_status_id'] == 10) {
                if ($modelQ['servicegroupid'] == 1) {//ลงทะเบียน
                    return [
                        'success' => true,
                        'message' => 'เสร็จสิ้น (จุดลงทะเบียน)'
                    ];
                }
                if ($modelQ['servicegroupid'] == 2) {
                    return [
                        'success' => true,
                        'message' => 'เสร็จสิ้น (ห้องตรวจ)'
                    ];
                }
            }
        }
    }

    public function actionCallSrSelected()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $db = Yii::$app->db;
            $selectedData = $request->post('selectedData');
            $autoend = $request->post('autoend');// 0 = auto
            $call_result = [];
            $end_result = [];
            foreach ($selectedData as $data) {
                $transaction = $db->beginTransaction();
                try {
                    $dataForm = $request->post('modelForm', []);
                    $dataProfile = $request->post('modelProfile', []);
                    $modelProfile = $this->findModelServiceProfile($dataProfile['service_profile_id']);
                    $counter = $this->findModelCounterservice($dataForm['counter_service']);
                    $modelQ = $this->findModelQuequ($data['q_ids']);

                    $modelCaller = new TbCaller();
                    $modelCaller->q_ids = $data['q_ids'];
                    $modelCaller->qtran_ids = $data['ids'];
                    $modelCaller->counter_service_id = $dataForm['counter_service'];
                    $modelCaller->call_timestp = new Expression('NOW()');
                    $modelCaller->call_status = TbCaller::STATUS_CALLING;

                    $modelTrans = $this->findModelQTrans($data['ids']);
                    if ($autoend == 1) {
                        $modelTrans->service_status_id = 2;
                    } else {//auto end
                        $modelTrans->service_status_id = 4;
                        $modelTrans->counter_service_id = $counter['counterserviceid'];
                        $modelCaller->call_status = TbCaller::STATUS_FINISHED;
                    }
                    if ($modelCaller->save() && $modelTrans->save()) {
                        $data['counter_service_id'] = $counter['counterserviceid'];

                        $call_result[] = [
                            'status' => '200',
                            'message' => 'success',
                            'sound' => $this->getMediaSound($modelQ['q_num'], $modelCaller['counter_service_id']),
                            'data' => $data,
                            'model' => $modelCaller,
                            'modelQ' => $modelQ,
                            'modelProfile' => $modelProfile,
                            'counter' => $counter,
                            'eventOn' => 'tb-waiting',
                            'state' => 'call',
                        ];
                        if ($autoend == 0) {
                            $data = $this->getDataCaller($request, $modelCaller);

                            $end_result[] = [
                                'status' => '200',
                                'message' => 'success',
                                'data' => (is_array($data) & count($data) > 0) ? $data[0] : [],
                                'model' => $modelCaller,
                                'modelQ' => $modelQ,
                                'modelProfile' => $modelProfile,
                                'counter' => $counter,
                                'eventOn' => 'tb-calling',
                                'state' => 'end',
                            ];
                            $transaction->commit();
                        } else {
                            $transaction->commit();
                        }
                    } else {
                        $transaction->rollBack();
                        throw new HttpException(422, json_encode($modelCaller->errors));
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
            return [
                'call_result' => $call_result,
                'end_result' => $end_result,
            ];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    private function getDataCaller($request, $modelCaller)
    {
        $formData = $request->post('modelForm', []);
        $profileData = $request->post('modelProfile', []);
        $services = isset($profileData['service_id']) ? explode(",", $profileData['service_id']) : null;

        $query = (new \yii\db\Query())
            ->select([
                'tb_caller.caller_ids',
                'tb_caller.q_ids',
                'tb_caller.qtran_ids',
                'DATE_FORMAT(DATE_ADD(tb_qtrans.checkin_date, INTERVAL 543 YEAR),\'%H:%i:%s\') as checkin_date',
                'tb_caller.servicegroupid',
                'tb_caller.counter_service_id',
                'tb_caller.call_timestp',
                'tb_quequ.q_num',
                'tb_quequ.q_hn',
                'tb_quequ.pt_name',
                'tb_service_status.service_status_name',
                'tb_counterservice.counterservice_name',
                'tb_service.service_name',
                'tb_service.serviceid',
                'tb_service.service_prefix',
                'tb_quequ.quickly'
            ])
            ->from('tb_caller')
            ->innerJoin('tb_qtrans', 'tb_qtrans.ids = tb_caller.qtran_ids')
            ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
            ->innerJoin('tb_service_status', 'tb_service_status.service_status_id = tb_qtrans.service_status_id')
            ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
            ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
            ->where([
                'tb_quequ.serviceid' => $services,
                'tb_caller.counter_service_id' => $formData['counter_service'],
                'tb_caller.caller_ids' => $modelCaller['caller_ids'],
            ])
            ->orderBy(['tb_quequ.quickly' => SORT_DESC, 'tb_caller.call_timestp' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => false,
            ],
            'key' => 'caller_ids',
        ]);
        $columns = Yii::createObject([
            'class' => ColumnData::className(),
            'dataProvider' => $dataProvider,
            'formatter' => Yii::$app->getFormatter(),
            'columns' => [
                [
                    'attribute' => 'caller_ids',
                ],
                [
                    'attribute' => 'q_ids',
                ],
                [
                    'attribute' => 'q_num',
                    'value' => function ($model, $key, $index, $column) {
                        return \kartik\helpers\Html::badge($model['q_num'], ['class' => 'badge', 'style' => 'font-size: 16px;']);
                    },
                    'format' => 'raw'
                ],
                [
                    'attribute' => 'q_hn',
                ],
                [
                    'attribute' => 'pt_name',
                ],
                [
                    'attribute' => 'counter_service_id',
                ],
                [
                    'attribute' => 'checkin_date',
                ],
                [
                    'attribute' => 'counterservice_name',
                ],
                [
                    'attribute' => 'qnumber',
                    'value' => function ($model, $key, $index, $column) {
                        return $model['q_num'];
                    },
                ],
                [
                    'attribute' => 'service_status_name',
                    'value' => function ($model, $key, $index, $column) {
                        return \kartik\helpers\Html::badge(Icon::show('hourglass-3') . ' ' . $model['service_status_name'], ['class' => 'badge']);
                    },
                    'format' => 'raw'
                ],
                [
                    'attribute' => 'service_name',
                    'value' => function ($model, $key, $index, $column) {
                        return $model['quickly'] == 1 ? 'คิวด่วน' : $model['service_name'];
                    },
                ],
                [
                    'attribute' => 'serviceid',
                ],
                [
                    'attribute' => 'service_prefix',
                ],
                [
                    'attribute' => 'quickly',
                ],
            ]
        ]);

        return $columns->renderDataColumns();
    }
}
