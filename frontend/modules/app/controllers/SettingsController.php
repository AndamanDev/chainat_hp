<?php

namespace frontend\modules\app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Html;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use homer\widgets\tbcolumn\ActionTable;
use homer\widgets\tbcolumn\ColumnTable;
use homer\widgets\tbcolumn\ColumnData;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\icons\Icon;
use kartik\form\ActiveForm;
use common\models\MultipleModel;
use yii\base\Model;
use yii\db\Expression;
use Intervention\Image\ImageManagerStatic;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;
use kartik\helpers\Html as kartik;
#models
use frontend\modules\app\models\TbServicegroup;
use frontend\modules\app\models\TbService;
use frontend\modules\app\models\TbSound;
use frontend\modules\app\models\TbDisplayConfig;
use frontend\modules\app\models\TbCounterserviceType;
use frontend\modules\app\models\TbCounterservice;
use frontend\modules\app\models\TbTicket;
use frontend\modules\app\models\TbServiceProfile;
use frontend\modules\app\models\TbSoundStation;
use frontend\modules\app\traits\ModelTrait;
use frontend\modules\app\models\TbQuequData;
use frontend\modules\app\models\TbCaller;
use frontend\modules\app\models\TbCallerData;
use frontend\modules\app\models\TbQtransData;
use frontend\modules\app\models\TbQtrans;
use frontend\modules\app\models\TbCidStation;
use frontend\modules\app\models\LabItems;
use frontend\modules\app\models\mobile\TbCallingConfig;
use frontend\modules\app\models\TbKiosk;
use frontend\modules\app\models\TbServiceTslot;
use frontend\modules\app\models\TbTokenNhso;
use kartik\switchinput\SwitchInput;
use yii\web\HttpException;
use frontend\modules\app\models\mobile\TbQuequ;

class SettingsController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['save-nhso-token'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-service-group' => ['POST'],
                    'delete-sound' => ['POST'],
                    'delete-display' => ['POST'],
                    'delete-counter' => ['POST'],
                    'delete-ticket' => ['POST'],
                    'delete-service-profile' => ['POST'],
                    'delete-sound-station' => ['POST'],
                    'delete-cid-station' => ['POST'],
                    'delete-kiosk' => ['POST'],
                    'delete-calling-config' => ['POST']

                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action->id == 'save-nhso-token') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actions()
    {
        return [
            'file-upload' => [
                'class' => UploadAction::className(),
                'deleteRoute' => 'file-delete',
            ],
            'file-delete' => [
                'class' => DeleteAction::className()
            ]
        ];
    }

    public function actionDeleteServiceGroup($id, $serviceid = null)
    {
        $request = Yii::$app->request;
        if ($serviceid != null) {
            TbService::findOne($serviceid)->delete();
        }
        if (TbService::find()->where(['service_groupid' => $id])->count() == 0) {
            TbServicegroup::findOne($id)->delete();
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDeleteSound($id)
    {
        $request = Yii::$app->request;
        TbSound::findOne($id)->delete();
        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDeleteDisplay($id)
    {
        $request = Yii::$app->request;
        TbDisplayConfig::findOne($id)->delete();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDeleteCounter($id)
    {
        $request = Yii::$app->request;
        TbCounterserviceType::findOne($id)->delete();
        TbCounterservice::deleteAll(['counterservice_type' => $id]);

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDeleteTicket($id)
    {
        $request = Yii::$app->request;
        TbTicket::findOne($id)->delete();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDeleteServiceProfile($id)
    {
        $request = Yii::$app->request;
        TbServiceProfile::findOne($id)->delete();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDeleteSoundStation($id)
    {
        $request = Yii::$app->request;
        TbSoundStation::findOne($id)->delete();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDeleteCidStation($id)
    {
        $request = Yii::$app->request;
        TbCidStation::findOne($id)->delete();
        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDeleteKiosk($id)
    {
        $request = Yii::$app->request;
        TbKiosk::findOne($id)->delete();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    protected function getBadgeStatus($status)
    {
        if ($status == 0) {
            return kartik::badge(Icon::show('close') . ' ปิดใช้งาน', ['class' => 'badge badge-danger']);
        } elseif ($status == 1) {
            return kartik::badge(Icon::show('check') . ' เปิดใช้งาน', ['class' => 'badge badge-success']);
        }
    }

    public function actionDataServiceGroup()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select([
                    'tb_servicegroup.servicegroupid',
                    'tb_servicegroup.servicegroup_name',
                    'tb_servicegroup.servicegroup_order',
                    'tb_service.serviceid',
                    'tb_service.service_name',
                    'tb_service.service_groupid',
                    'tb_service.service_route',
                    'tb_service.prn_profileid',
                    'tb_service.prn_copyqty',
                    'tb_service.service_prefix',
                    'tb_service.service_numdigit',
                    'tb_service.service_status',
                    'tb_service.show_on_kiosk',
                    'tb_service.show_on_mobile',
                    'tb_service.service_type_id',
                    'tb_service_type.service_type_name'
                ])
                ->from('tb_servicegroup')
                ->leftJoin('tb_service', 'tb_service.service_groupid = tb_servicegroup.servicegroupid')
                ->leftJoin('tb_service_type', 'tb_service.service_type_id = tb_service_type.service_type_id')
                ->orderBy('tb_servicegroup.servicegroupid ASC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'servicegroupid'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'servicegroupid',
                    ],
                    [
                        'attribute' => 'servicegroup_name',
                    ],
                    [
                        'attribute' => 'service_type_name',
                    ],
                    [
                        'attribute' => 'servicegroup_order',
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'service_groupid',
                    ],
                    [
                        'attribute' => 'service_route',
                    ],
                    [
                        'attribute' => 'prn_profileid',
                    ],
                    [
                        'attribute' => 'prn_copyqty',
                    ],
                    [
                        'attribute' => 'service_prefix',
                    ],
                    [
                        'attribute' => 'service_numdigit',
                    ],
                    [
                        'attribute' => 'show_on_kiosk',
                    ],
                    [
                        'attribute' => 'show_on_mobile',
                    ],
                    [
                        'attribute' => 'service_status',
                        'value' => function ($model, $key, $index) {
                            return $this->getBadgeStatus($model['service_status']);
                        },
                        'format' => 'raw'
                    ],
                    // [
                    //     'attribute' => 'service_status_id',
                    //     'value' => function ($model, $key, $index) {
                    //         return $model['service_status'];
                    //     },
                    // ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{slot} {update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote',
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {

                            if ($action == 'slot') {
                                return Url::to(['/app/settings/create-service-tslot', 'id' => $model['serviceid']]);
                            }
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-service-group', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-service-group', 'id' => $key, 'serviceid' => $model['serviceid']]);
                            }
                        },
                        'buttons' => [
                            'slot' => function ($url, $model, $key) {
                                return Html::a('<i class="fa fa-calendar"></i>', $url, ['role' => 'modal-remote', 'class' => 'text-info', 'title' => 'บันทึก slot']);
                            }
                        ]
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataSound()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select([
                    'tb_sound.sound_id',
                    'tb_sound.sound_name',
                    'tb_sound.sound_path_name',
                    'tb_sound.sound_th',
                    'tb_sound.sound_type'
                ])
                ->from('tb_sound');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'sound_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'sound_id',
                    ],
                    [
                        'attribute' => 'sound_name',
                    ],
                    [
                        'attribute' => 'sound_path_name',
                    ],
                    [
                        'attribute' => 'sound_th',
                    ],
                    [
                        'attribute' => 'sound_type',
                        'value' => function ($model) {
                            return $model['sound_type'] == 1 ? 'เสียงผู้หญิง' : 'เสียงผู้ชาย';
                        }
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-sound', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-sound', 'id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataDisplay()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = TbDisplayConfig::find();

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'display_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'display_ids',
                    ],
                    [
                        'attribute' => 'display_name',
                    ],
                    [
                        'attribute' => 'counterservice_id',
                        'value' => function ($model) {
                            return @$model->counterList;
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'service_id',
                        'value' => function ($model, $key, $index, $column) {
                            return $model->serviceList;
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'title_left',
                    ],
                    [
                        'attribute' => 'title_right',
                    ],
                    [
                        'attribute' => 'title_color',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::tag('span', $model['title_color'], ['class' => 'badge', 'style' => 'background-color: ' . $model['title_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'table_title_left',
                    ],
                    [
                        'attribute' => 'table_title_right',
                    ],
                    [
                        'attribute' => 'display_limit',
                    ],
                    [
                        'attribute' => 'hold_label',
                    ],
                    [
                        'attribute' => 'header_color',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::tag('span', $model['header_color'], ['class' => 'badge', 'style' => 'background-color: ' . $model['header_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'column_color',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::tag('span', $model['column_color'], ['class' => 'badge', 'style' => 'background-color: ' . $model['column_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'background_color',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::tag('span', $model['background_color'], ['class' => 'badge', 'style' => 'background-color: ' . $model['background_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'font_color',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::tag('span', $model['font_color'], ['class' => 'badge', 'style' => 'background-color: ' . $model['font_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'border_color',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::tag('span', $model['border_color'], ['class' => 'badge', 'style' => 'background-color: ' . $model['border_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{view} {display} {update} {delete}',
                        'viewOptions' => [
                            'target' => '_blank'
                        ],
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'view') {
                                return Url::to(['/app/display/index', 'id' => $key]);
                            }
                            if ($action == 'display') {
                                return Url::to(['/app/settings/update-display', 'id' => $key]);
                            }
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-display', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-display', 'id' => $key]);
                            }
                        },
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a(Icon::show('eye'), $url, []);
                            },
                            'display' => function ($url, $model, $key) {
                                return Html::a(Icon::show('television'), $url, []);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a(Icon::show('pencil'), $url, ['role' => 'modal-remote']);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a(Icon::show('trash-o'), $url, [
                                    'class' => 'text-danger',
                                    'data-pjax' => 0,
                                    'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                                    'data-method' => 'post'
                                ]);
                            },
                        ]
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function fn_return_soundname($tb_counterservice)
    {
        !empty($tb_counterservice) ?
            $query = (new \yii\db\Query())
            ->select(['tb_sound.sound_th'])
            ->from('tb_sound')
            ->where(['tb_sound.sound_id' => $tb_counterservice])
            ->all(\Yii::$app->db) :
            $query = '';
        return $query;
    }

    public function actionDataCounter()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $sql = 'SELECT
                        tb_counterservice_type.counterservice_typeid,
                        tb_counterservice_type.counterservice_type,
                        tb_counterservice_type.sound_id,
                        tb_counterservice.counterserviceid,
                        tb_counterservice.counterservice_name,
                        tb_counterservice.counterservice_callnumber,
                        tb_counterservice.servicegroupid,
                        tb_counterservice.counterservice_type as counterservice_type_id,
                        tb_counterservice.userid,
                        tb_counterservice.serviceid,
                        tb_counterservice.sound_stationid,
                        tb_counterservice.sound_id,
                        tb_counterservice.counterservice_status,
                        tb_counterservice.service_order,
                        tb_servicegroup.servicegroup_name,
                        tb_service.service_name,
                        tb_sound.sound_th as sound_name,
                        (select tb_sound.sound_th FROM tb_sound WHERE tb_sound.sound_id = tb_counterservice.sound_service_id) as sound_service_name
                        FROM
                        tb_counterservice_type
                        LEFT JOIN tb_counterservice ON tb_counterservice.counterservice_type = tb_counterservice_type.counterservice_typeid
                        LEFT JOIN tb_servicegroup ON tb_servicegroup.servicegroupid = tb_counterservice.servicegroupid
                        LEFT JOIN tb_service ON tb_service.service_groupid = tb_servicegroup.servicegroupid
                        LEFT JOIN tb_sound ON tb_sound.sound_id = tb_counterservice.sound_id
                        GROUP BY
                        tb_counterservice.counterserviceid
                        ORDER BY
                        tb_counterservice.counterservice_type ASC,
                        tb_counterservice.service_order ASC';
            $command = Yii::$app->db->createCommand($sql);
            $dataProvider = new ArrayDataProvider([
                'allModels' => $command->queryAll(),
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'counterservice_typeid'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'counterserviceid',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'counterservice_callnumber',
                    ],
                    [
                        'attribute' => 'servicegroupid',
                    ],
                    [
                        'attribute' => 'userid',
                    ],
                    [
                        'attribute' => 'serviceid',
                    ],
                    [
                        'attribute' => 'sound_stationid',
                    ],
                    [
                        'attribute' => 'sound_id',
                    ],
                    [
                        'attribute' => 'counterservice_status',
                        'value' => function ($model, $key, $index) {
                            return $this->getBadgeStatus($model['counterservice_status']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'counterservice_status_id',
                        'value' => function ($model, $key, $index) {
                            return $model['counterservice_status'];
                        },
                    ],
                    [
                        'attribute' => 'counterservice_type',
                    ],
                    [
                        'attribute' => 'counterservice_typeid',
                    ],
                    [
                        'attribute' => 'sound_name',
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'servicegroup_name',
                    ],
                    [
                        'attribute' => 'sound_service_name',
                    ],
                    [
                        'attribute' => 'counterservice_type_id',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        // 'deleteOptions' => [
                        //     'class' => 'text-danger'
                        // ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-counter', 'id' => $key]);
                            }
                            // if ($action == 'delete') {
                            //     return Url::to(['/app/settings/delete-counter', 'id' => $key]);
                            // }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataTicket()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select(['tb_ticket.*'])
                ->from('tb_ticket');

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
                        'attribute' => 'hos_name_th',
                    ],
                    [
                        'attribute' => 'hos_name_en',
                    ],
                    [
                        'attribute' => 'template',
                    ],
                    [
                        'attribute' => 'default_template',
                    ],
                    [
                        'attribute' => 'logo_path',
                    ],
                    [
                        'attribute' => 'logo_base_url',
                    ],
                    [
                        'attribute' => 'barcode_type',
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model, $key, $index) {
                            return $this->getBadgeStatus($model['status']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-ticket', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-ticket', 'id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataServiceProfile()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = TbServiceProfile::find();

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'service_profile_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'service_profile_id',
                    ],
                    [
                        'attribute' => 'service_name',
                    ],
                    [
                        'attribute' => 'counterservice_typeid',
                    ],
                    [
                        'attribute' => 'service_id',
                    ],
                    [
                        'attribute' => 'servicelist',
                        'value' => function ($model, $key, $index, $column) {
                            return $model->servieceList;
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'counterservice_type',
                        'value' => function ($model, $key, $index, $column) {
                            return @$model->tbCounterserviceType->counterservice_type;
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_status_id',
                        'value' => function ($model, $key, $index, $column) {
                            return @$model->tbServiceStatus->service_status_name;
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'service_profile_status',
                        'value' => function ($model, $key, $index) {
                            return $this->getBadgeStatus($model['service_profile_status']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-service-profile', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-service-profile', 'id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataSoundStation()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = TbSoundStation::find();

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'sound_station_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'sound_station_id',
                    ],
                    [
                        'attribute' => 'sound_station_name',
                    ],
                    [
                        'attribute' => 'counterserviceid',
                    ],
                    [
                        'attribute' => 'counterlist',
                        'value' => function ($model, $key, $index, $column) {
                            return $model->counterList;
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'sound_station_status',
                        'value' => function ($model, $key, $index) {
                            return $this->getBadgeStatus($model['sound_station_status']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-sound-station', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-sound-station', 'id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataCidStation()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select(['tb_cid_station.*'])
                ->from('tb_cid_station');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'id',
                    ],
                    [
                        'attribute' => 'name',
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model, $key, $index) {
                            return $this->getBadgeStatus($model['status']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-cid-station', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-cid-station', 'id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataLab()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select(['lab_items.*'])
                ->from('lab_items');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'lab_items_code'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'lab_items_code',
                    ],
                    [
                        'attribute' => 'lab_items_name',
                    ],
                    [
                        'attribute' => 'confirm',
                        'value' => function ($model, $key, $index, $column) {
                            if ($model['confirm'] == 'Y') {
                                $checked = true;
                            } else {
                                $checked = false;
                            }
                            $checkbox = '<div class="checkbox"><label style="font-size: 1em">';
                            $checkbox .= Html::checkbox('confirm', $checked, ['value' => $model['confirm'], 'class' => 'activity-lab-confirm', 'data-key' => $key]);
                            $checkbox .= '<span class="cr"><i class="cr-icon cr-icon glyphicon glyphicon-ok"></i></span>';
                            $checkbox .= '</label></div>';
                            return $checkbox;
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-lab', 'id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataKiosk()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select([
                    'tb_kiosk.*',
                ])
                ->from('tb_kiosk');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'kiosk_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'kiosk_id',
                    ],
                    [
                        'attribute' => 'kiosk_name',
                    ],
                    [
                        'attribute' => 'service_names',
                        'value' => function ($model) {
                            return TbKiosk::getServiceList($model['service_ids']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'font_size',
                        'value' => function ($model) {
                            return !empty($model['font_size']) ? $model['font_size'] . 'px' : '';
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{sortinput} {update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote',
                            'class' => 'btn btn-sm btn-default'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger',
                            'class' => 'btn btn-sm btn-danger'
                        ],
                        'buttons' => [
                            'sortinput' => function ($url, $model, $key) {
                                return Html::a('<i class="fa fa-sort-numeric-asc"></i>', $url, ['class' => 'btn btn-sm btn-success', 'role' => 'modal-remote', 'title' => 'จัดเรียงปุ่ม']);
                            }
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'sortinput') {
                                return Url::to(['/app/settings/sortinput-kiosk', 'id' => $key]);
                            }
                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-kiosk', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-kiosk', 'id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionCreateServiceGroup()
    {
        $request = Yii::$app->request;
        $model = new TbServicegroup();
        $modelServices = [new TbService()];

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                $model->servicestatus_default = 1;
                return [
                    'title'     => "จัดการกลุ่มบริการ",
                    'content'   => $this->renderAjax('_form_service_group', [
                        'model' => $model,
                        'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                    ]),
                    'footer' =>  ''
                ];
            } elseif ($model->load($request->post())) {
                $oldIDs = ArrayHelper::map($modelServices, 'serviceid', 'serviceid');
                $modelServices = MultipleModel::createMultiple(TbService::classname(), $modelServices, 'serviceid');
                MultipleModel::loadMultiple($modelServices, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelServices, 'serviceid', 'serviceid')));

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelServices) && $valid;
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $model->subservice_status = 1;
                        if ($flag = $model->save(false)) {
                            if (!empty($deletedIDs)) {
                                TbService::deleteAll(['serviceid' => $deletedIDs]);
                            }
                            foreach ($modelServices as $modelService) {
                                $modelService->service_groupid = $model['servicegroupid'];
                                if (!($flag = $modelService->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title' => "จัดการกลุ่มบริการ",
                                'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                } else {
                    return [
                        'title' => "จัดการกลุ่มบริการ",
                        'content'   => $this->renderAjax('_form_service_group', [
                            'model' => $model,
                            'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                        ]),
                        'footer' => '',
                        'validate' => ArrayHelper::merge(ActiveForm::validateMultiple($modelServices), ActiveForm::validate($model)),
                        'status' => 'error'
                    ];
                }
            } else {
                return [
                    'title' => "จัดการกลุ่มบริการ",
                    'content'   => $this->renderAjax('_form_service_group', [
                        'model' => $model,
                        'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                    ]),
                    'footer' => '',
                    'validate' => ArrayHelper::merge(ActiveForm::validateMultiple($modelServices), ActiveForm::validate($model)),
                    'status' => 'error'
                ];
            }
        } else {
            if ($model->load($request->post())) {
                $oldIDs = ArrayHelper::map($modelServices, 'serviceid', 'serviceid');
                $modelServices = MultipleModel::createMultiple(TbService::classname(), $modelServices, 'serviceid');
                MultipleModel::loadMultiple($modelServices, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelServices, 'serviceid', 'serviceid')));

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelServices) && $valid;
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $model->subservice_status = 1;
                        if ($flag = $model->save(false)) {
                            if (!empty($deletedIDs)) {
                                TbService::deleteAll(['serviceid' => $deletedIDs]);
                            }
                            foreach ($modelServices as $modelService) {
                                $modelService->service_groupid = $model['servicegroupid'];
                                if (!($flag = $modelService->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return $this->redirect(['/app/settings/index']);
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                } else {
                    return  $this->render('_form_service_group', [
                        'model' => $model,
                        'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                    ]);
                }
            } else {
                return  $this->render('_form_service_group', [
                    'model' => $model,
                    'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                ]);
            }
        }
    }

    public function actionUpdateServiceGroup($id)
    {
        $request = Yii::$app->request;
        $model = TbServicegroup::findOne($id);
        $modelServices = TbService::find()->where(['service_groupid' => $id])->all();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title'     => "จัดการกลุ่มบริการ",
                    'content'   => $this->renderAjax('_form_service_group', [
                        'model' => $model,
                        'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                    ]),
                    'footer' =>  ''
                ];
            } elseif ($model->load($request->post())) {
                $oldIDs = ArrayHelper::map($modelServices, 'serviceid', 'serviceid');
                $modelServices = MultipleModel::createMultiple(TbService::classname(), $modelServices, 'serviceid');
                MultipleModel::loadMultiple($modelServices, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelServices, 'serviceid', 'serviceid')));

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelServices) && $valid;
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {
                            if (!empty($deletedIDs)) {
                                TbService::deleteAll(['serviceid' => $deletedIDs]);
                            }
                            foreach ($modelServices as $modelService) {
                                $modelService->service_groupid = $model['servicegroupid'];
                                if (!($flag = $modelService->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title' => "จัดการกลุ่มบริการ",
                                'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                } else {
                    return [
                        'title' => "จัดการกลุ่มบริการ",
                        'content'   => $this->renderAjax('_form_service_group', [
                            'model' => $model,
                            'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                        ]),
                        'footer' => '',
                        'validate' => ArrayHelper::merge(ActiveForm::validateMultiple($modelServices), ActiveForm::validate($model)),
                        'status' => 'error'
                    ];
                }
            } else {
                return [
                    'title' => "จัดการกลุ่มบริการ",
                    'content'   => $this->renderAjax('_form_service_group', [
                        'model' => $model,
                        'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                    ]),
                    'footer' => '',
                    'validate' => ArrayHelper::merge(ActiveForm::validateMultiple($modelServices), ActiveForm::validate($model)),
                    'status' => 'error'
                ];
            }
        } else {
            if ($model->load($request->post())) {
                $oldIDs = ArrayHelper::map($modelServices, 'serviceid', 'serviceid');
                $modelServices = MultipleModel::createMultiple(TbService::classname(), $modelServices, 'serviceid');
                MultipleModel::loadMultiple($modelServices, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelServices, 'serviceid', 'serviceid')));

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelServices) && $valid;
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {
                            if (!empty($deletedIDs)) {
                                TbService::deleteAll(['serviceid' => $deletedIDs]);
                            }
                            foreach ($modelServices as $modelService) {
                                $modelService->service_groupid = $model['servicegroupid'];
                                if (!($flag = $modelService->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return $this->refresh();
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                } else {
                    return $this->render('_form_service_group', [
                        'model' => $model,
                        'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                    ]);
                }
            } else {
                return $this->render('_form_service_group', [
                    'model' => $model,
                    'modelServices' => (empty($modelServices)) ? [new TbService()] : $modelServices,
                ]);
            }
        }
    }

    public function actionCreateSound()
    {
        $request = Yii::$app->request;
        $model = new TbSound();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "จัดการข้อมูลไฟล์เสียง",
                    'content' => $this->renderAjax('_form_sound', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "จัดการข้อมูลไฟล์เสียง",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                ];
            } else {
                return [
                    'title' => "จัดการข้อมูลไฟล์เสียง",
                    'content' => $this->renderAjax('_form_sound', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionUpdateSound($id)
    {
        $request = Yii::$app->request;
        $model = TbSound::findOne($id);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "จัดการข้อมูลไฟล์เสียง",
                    'content' => $this->renderAjax('_form_sound', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "จัดการข้อมูลไฟล์เสียง",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                ];
            } else {
                return [
                    'title' => "จัดการข้อมูลไฟล์เสียง",
                    'content' => $this->renderAjax('_form_sound', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionCreateDisplay()
    {
        $request = Yii::$app->request;
        $model = new TbDisplayConfig();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isPost) {
                $data = $request->post('TbDisplayConfig', []);
                $model->counterservice_id = isset($data['counterservice_id']) ? $data['counterservice_id'] : null;
                $model->service_id = isset($data['service_id']) ? $data['service_id'] : null;
            }
            if ($request->isGet) {
                return [
                    'title' => "จัดการข้อมูลจอแสดงผล",
                    'content' => $this->renderAjax('_form_display', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "จัดการข้อมูลจอแสดงผล",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->display_ids]),
                ];
            } else {
                return [
                    'title' => "จัดการข้อมูลจอแสดงผล",
                    'content' => $this->renderAjax('_form_display', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            return $this->render('_form_display', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdateDisplay($id)
    {
        $request = Yii::$app->request;
        $model = TbDisplayConfig::findOne($id);
        $model->service_id = !empty($model['service_id']) ? explode(",", $model['service_id']) : null;
        $model->counterservice_id = !empty($model['counterservice_id']) ? explode(",", $model['counterservice_id']) : null;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "จัดการข้อมูลจอแสดงผล",
                    'content' => $this->renderAjax('_form_display', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post())) {
                $data = $request->post('TbDisplayConfig', []);
                if (isset($data['counterservice_id'])) {
                    $model->counterservice_id = $data['counterservice_id'];
                }
                if (isset($data['service_id'])) {
                    $model->service_id = $data['service_id'];
                }


                $model->save();
                return [
                    'title' => "จัดการข้อมูลจอแสดงผล",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update-display', 'id' => $model->display_ids]),
                    'data' => $data
                ];
            } else {
                return [
                    'title' => "จัดการข้อมูลจอแสดงผล",
                    'content' => $this->renderAjax('_form_display', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            if ($model->load($request->post())) {
                $model->attributes = \Yii::$app->request->post('TbDisplayConfig');
            }
            $this->layout = 'display';
            return $this->render('_form_display_design', [
                'model' => $model,
            ]);
        }
    }

    public function actionCreateCounter()
    {
        $request = Yii::$app->request;
        $model = new TbCounterserviceType();
        $modelCounterservices = [new TbCounterservice()];

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title'     => "จัดการช่องบริการ",
                    'content'   => $this->renderAjax('_form_counter', [
                        'model' => $model,
                        'modelCounterservices' => (empty($modelCounterservices)) ? [new TbCounterservice()] : $modelCounterservices,
                    ]),
                    'footer' =>  ''
                ];
            } elseif ($model->load($request->post())) {
                $oldIDs = ArrayHelper::map($modelCounterservices, 'counterserviceid', 'counterserviceid');
                $modelCounterservices = MultipleModel::createMultiple(TbCounterservice::classname(), $modelCounterservices, 'counterserviceid');
                MultipleModel::loadMultiple($modelCounterservices, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelCounterservices, 'counterserviceid', 'counterserviceid')));

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelCounterservices) && $valid;
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {
                            if (!empty($deletedIDs)) {
                                TbCounterservice::deleteAll(['counterserviceid' => $deletedIDs]);
                            }
                            foreach ($modelCounterservices as $modelCounterservice) {
                                $modelCounterservice->counterservice_type = $model['counterservice_typeid'];
                                if (!($flag = $modelCounterservice->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title' => "จัดการช่องบริการ",
                                'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                } else {
                    return [
                        'title' => "จัดการช่องบริการ",
                        'content'   => $this->renderAjax('_form_counter', [
                            'model' => $model,
                            'modelCounterservices' => (empty($modelCounterservices)) ? [new TbCounterservice()] : $modelCounterservices,
                        ]),
                        'footer' => '',
                        'validate' => ArrayHelper::merge(ActiveForm::validateMultiple($modelCounterservices), ActiveForm::validate($model)),
                        'status' => 'error'
                    ];
                }
            } else {
                return [
                    'title' => "จัดการช่องบริการ",
                    'content'   => $this->renderAjax('_form_counter', [
                        'model' => $model,
                        'modelCounterservices' => (empty($modelCounterservices)) ? [new TbCounterservice()] : $modelCounterservices,
                    ]),
                    'footer' => '',
                    'validate' => ArrayHelper::merge(ActiveForm::validateMultiple($modelCounterservices), ActiveForm::validate($model)),
                    'status' => 'error'
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionChildServiceGroup()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            $list = TbService::find()->andWhere(['service_groupid' => $id])->asArray()->all();
            $selected  = null;
            if ($id != null && count($list) > 0) {
                $selected = '';
                foreach ($list as $i => $service) {
                    $out[] = ['id' => $service['serviceid'], 'name' => $service['service_name']];
                    if ($i == 0) {
                        $selected = $service['serviceid'];
                    }
                }
                // Shows how you can preselect a value
                echo Json::encode(['output' => $out, 'selected' => $selected]);
                return;
            }
        }
        echo Json::encode(['output' => '', 'selected' => '']);
    }

    public function actionUpdateCounter($id)
    {
        $request = Yii::$app->request;
        $model = TbCounterserviceType::findOne($id);
        $modelCounterservices = TbCounterservice::find()->where(['counterservice_type' => $id])->all();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title'     => "จัดการช่องบริการ",
                    'content'   => $this->renderAjax('_form_counter', [
                        'model' => $model,
                        'modelCounterservices' => (empty($modelCounterservices)) ? [new TbCounterservice()] : $modelCounterservices,
                    ]),
                    'footer' =>  ''
                ];
            } elseif ($model->load($request->post())) {
                $oldIDs = ArrayHelper::map($modelCounterservices, 'counterserviceid', 'counterserviceid');
                $modelCounterservices = MultipleModel::createMultiple(TbCounterservice::classname(), $modelCounterservices, 'counterserviceid');
                MultipleModel::loadMultiple($modelCounterservices, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelCounterservices, 'counterserviceid', 'counterserviceid')));

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelCounterservices) && $valid;
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {
                            if (!empty($deletedIDs)) {
                                TbCounterservice::deleteAll(['counterserviceid' => $deletedIDs]);
                            }
                            foreach ($modelCounterservices as $modelCounterservice) {
                                $modelCounterservice->counterservice_type = $model['counterservice_typeid'];
                                if (!($flag = $modelCounterservice->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title' => "จัดการช่องบริการ",
                                'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                } else {
                    return [
                        'title' => "จัดการช่องบริการ",
                        'content'   => $this->renderAjax('_form_counter', [
                            'model' => $model,
                            'modelCounterservices' => (empty($modelCounterservices)) ? [new TbCounterservice()] : $modelCounterservices,
                        ]),
                        'footer' => '',
                        'validate' => ArrayHelper::merge(ActiveForm::validateMultiple($modelCounterservices), ActiveForm::validate($model)),
                        'status' => 'error'
                    ];
                }
            } else {
                return [
                    'title' => "จัดการช่องบริการ",
                    'content'   => $this->renderAjax('_form_counter', [
                        'model' => $model,
                        'modelCounterservices' => (empty($modelCounterservices)) ? [new TbCounterservice()] : $modelCounterservices,
                    ]),
                    'footer' => '',
                    'validate' => ArrayHelper::merge(ActiveForm::validateMultiple($modelCounterservices), ActiveForm::validate($model)),
                    'status' => 'error'
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionCreateTicket()
    {
        $request = Yii::$app->request;
        $model = new TbTicket();
        $modelQueue = new TbQuequ();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $description = [];
            $qattr = $modelQueue->attributeLabels();
            $keys = array_keys($qattr);
            foreach ($keys as $value) {
                $description[] = '{' . $value . '} : ' . ArrayHelper::getValue($qattr, $value);
            }

            if ($request->isGet) {
                return [
                    'title' => "จัดการข้อมูลบัตรคิว",
                    'content' => $this->renderAjax('_form_ticket', [
                        'model' => $model,
                        'description' => $description,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "จัดการข้อมูลบัตรคิว",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->ids]),
                ];
            } else {
                return [
                    'title' => "จัดการข้อมูลบัตรคิว",
                    'content' => $this->renderAjax('_form_ticket', [
                        'model' => $model,
                        'description' => $description,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionUpdateTicket($id)
    {
        $request = Yii::$app->request;
        $model = TbTicket::findOne($id);
        $modelQueue = new TbQuequ();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $description = [];
            $qattr = $modelQueue->attributeLabels();
            $keys = array_keys($qattr);
            foreach ($keys as $value) {
                $description[] = '{' . $value . '} : ' . ArrayHelper::getValue($qattr, $value);
            }
            if ($request->isGet) {
                return [
                    'title' => "จัดการข้อมูลบัตรคิว",
                    'content' => $this->renderAjax('_form_ticket', [
                        'model' => $model,
                        'description' => $description,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "จัดการข้อมูลบัตรคิว",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->ids]),
                ];
            } else {
                return [
                    'title' => "จัดการข้อมูลบัตรคิว",
                    'content' => $this->renderAjax('_form_ticket', [
                        'model' => $model,
                        'description' => $description,
                    ]),
                    'footer' => '',
                    'status' => 'error',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionCreateServiceProfile()
    {
        $request = Yii::$app->request;
        $model = new TbServiceProfile();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Service Profile",
                    'content' => $this->renderAjax('_form_service_profile', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "Service Profile",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->service_profile_id]),
                ];
            } else {
                return [
                    'title' => "Service Profile",
                    'content' => $this->renderAjax('_form_service_profile', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionUpdateServiceProfile($id)
    {
        $request = Yii::$app->request;
        $model = TbServiceProfile::findOne($id);
        $model->service_id = !empty($model['service_id']) ? explode(",", $model['service_id']) : null;
        $model->counter_service_ids = !empty($model['counter_service_ids']) ? explode(",", $model['counter_service_ids']) : null;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Service Profile",
                    'content' => $this->renderAjax('_form_service_profile', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "Service Profile",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->service_profile_id]),
                ];
            } else {
                return [
                    'title' => "Service Profile",
                    'content' => $this->renderAjax('_form_service_profile', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionCreateSoundStation()
    {
        $request = Yii::$app->request;
        $model = new TbSoundStation();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "โปรแกรมเสียง",
                    'content' => $this->renderAjax('_form_sound_station', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "โปรแกรมเสียง",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->sound_station_id]),
                ];
            } else {
                return [
                    'title' => "โปรแกรมเสียง",
                    'content' => $this->renderAjax('_form_sound_station', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionUpdateSoundStation($id)
    {
        $request = Yii::$app->request;
        $model = TbSoundStation::findOne($id);
        $model->counterserviceid = !empty($model['counterserviceid']) ? explode(",", $model['counterserviceid']) : null;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "โปรแกรมเสียง",
                    'content' => $this->renderAjax('_form_sound_station', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "โปรแกรมเสียง",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->sound_station_id]),
                ];
            } else {
                return [
                    'title' => "โปรแกรมเสียง",
                    'content' => $this->renderAjax('_form_sound_station', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDataQreset()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select(['tb_quequ.*'])
                ->from('tb_quequ');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'q_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'q_ids',
                    ],
                    [
                        'attribute' => 'q_num',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'q_timestp',
                    ],
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionResetQdata()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $modelQueue = TbQuequ::find()->all();
            foreach ($modelQueue as $key => $data) {
                $model = new TbQuequData();
                $model->q_ids = $data['q_ids'];
                $model->q_num = $data['q_num'];
                $model->q_timestp = $data['q_timestp'];
                $model->pt_id = $data['pt_id'];
                $model->q_vn = $data['q_vn'];
                $model->q_hn = $data['q_hn'];
                $model->pt_name = $data['pt_name'];
                $model->pt_visit_type_id = $data['pt_visit_type_id'];
                $model->pt_appoint_sec_id = $data['pt_appoint_sec_id'];
                $model->serviceid = $data['serviceid'];
                $model->servicegroupid = $data['servicegroupid'];
                $model->q_status_id = $data['q_status_id'];
                $model->doctor_id = $data['doctor_id'];
                $model->created_at = $data['created_at'];
                $model->updated_at = $data['updated_at'];
                $model->save(false);
            }
            $modelCaller = TbCaller::find()->all();
            foreach ($modelCaller as $key => $data) {
                $model = new TbCallerData();
                $model->caller_ids = $data['caller_ids'];
                $model->q_ids = $data['q_ids'];
                $model->qtran_ids = $data['qtran_ids'];
                $model->servicegroupid = $data['servicegroupid'];
                $model->counter_service_id = $data['counter_service_id'];
                $model->call_timestp = $data['call_timestp'];
                $model->created_by = $data['created_by'];
                $model->created_at = $data['created_at'];
                $model->updated_by = $data['updated_by'];
                $model->updated_at = $data['updated_at'];
                $model->call_status = $data['call_status'];
                $model->save(false);
            }
            $modelTrans = TbQtrans::find()->all();
            foreach ($modelTrans as $key => $data) {
                $model = new TbQtransData();
                $model->ids = $data['ids'];
                $model->q_ids = $data['q_ids'];
                $model->servicegroupid = $data['servicegroupid'];
                $model->counter_service_id = $data['counter_service_id'];
                $model->doctor_id = $data['doctor_id'];
                $model->checkin_date = $data['checkin_date'];
                $model->checkout_date = $data['checkout_date'];
                $model->service_status_id = $data['service_status_id'];
                $model->created_at = $data['created_at'];
                $model->updated_at = $data['updated_at'];
                $model->created_by = $data['created_by'];
                $model->updated_by = $data['updated_by'];
                $model->save(false);
            }
            TbCaller::deleteAll();
            TbQtrans::deleteAll();
            TbQuequ::deleteAll();
            return Json::encode(['status' => '200', 'mesage' => 'success']);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionSaveStatusServicegroup()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = $request->post();
            $model = TbService::findOne($post['key']);
            $model->service_status = $post['status'];
            $model->save(false);
            return $model;
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionSaveStatusCounterservicestatus()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = $request->post();
            $model = TbCounterservice::findOne($post['key']);
            $model->counterservice_status = $post['status'];
            $model->save(false);
            return $model;
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionSaveStatusTicket()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = $request->post();
            $model = TbTicket::findOne($post['key']);
            $model->status = $post['status'];
            $model->save(false);
            return $model;
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionSaveStatusProfile()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = $request->post();
            $model = TbServiceProfile::findOne($post['key']);
            $model->service_id = !empty($model['service_id']) ? explode(",", $model['service_id']) : null;
            $model->service_profile_status = $post['status'];
            $model->save(false);
            return $model;
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionSaveStatusSoundstation()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = $request->post();
            $model = TbSoundStation::findOne($post['key']);
            $model->counterserviceid = !empty($model['counterserviceid']) ? explode(",", $model['counterserviceid']) : null;
            $model->sound_station_status = $post['status'];
            $model->save(false);
            return $model;
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionSaveStatusCid()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = $request->post();
            $model = TbCidStation::findOne($post['key']);
            $model->status = $post['status'];
            $model->save(false);
            return $model;
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionCreateCidStation()
    {
        $request = Yii::$app->request;
        $model = new TbCidStation();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "จัดการข้อมูล",
                    'content' => $this->renderAjax('_form_cid_station', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "จัดการข้อมูล",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                ];
            } else {
                return [
                    'title' => "จัดการข้อมูล",
                    'content' => $this->renderAjax('_form_cid_station', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionUpdateCidStation($id)
    {
        $request = Yii::$app->request;
        $model = TbCidStation::findOne($id);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "จัดการข้อมูล",
                    'content' => $this->renderAjax('_form_cid_station', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "จัดการข้อมูล",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                ];
            } else {
                return [
                    'title' => "จัดการข้อมูล",
                    'content' => $this->renderAjax('_form_cid_station', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionServiceOrder($id)
    {
        $request = Yii::$app->request;
        $model = new TbCounterservice();
        $data = TbCounterservice::find()->where(['counterservice_type' => $id])->orderBy(['service_order' => SORT_ASC])->all();
        $items = [];
        foreach ($data as $item) {
            $items[$item['counterserviceid']] = ['content' => $item['counterservice_name']];
        }
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {

                return [
                    'title' => "จัดเรียงข้อมูลช่องบริการ",
                    'content' => $this->renderAjax('_form_service_order', [
                        'model' => $model,
                        'items' => $items,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post())) {
                $post = $request->post('TbCounterservice', []);
                $arr = explode(",", $post['items_sort']);
                $i = 1;
                foreach ($arr as $key) {
                    $modelSave = TbCounterservice::findOne($key);
                    $modelSave->service_order = $i++;
                    $modelSave->save(false);
                }
                return [
                    'title' => "จัดเรียงข้อมูลช่องบริการ",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                ];
            } else {
                return [
                    'title' => "จัดเรียงข้อมูลช่องบริการ",
                    'content' => $this->renderAjax('_form_service_order', [
                        'model' => $model,
                        'items' => $items,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionSaveLab()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = $request->post();
            $model = LabItems::findOne($post['code']);
            $model->confirm = $post['confirm'];
            $model->save(false);
            return $model;
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionImportLab()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $labItems = LabItems::find()->all();
            $oldLabs = ArrayHelper::getColumn($labItems, 'lab_items_code');
            $labs = Yii::$app->db_his->createCommand('SELECT * FROM lab_items')->queryAll();
            $count = 0;
            foreach ($labs as $lab) {
                if (!ArrayHelper::isIn($lab['lab_items_code'], $oldLabs)) {
                    Yii::$app->db->createCommand()->insert('lab_items', [
                        'lab_items_code' => $lab['lab_items_code'],
                        'lab_items_name' => $lab['lab_items_name'],
                        'confirm' => 'Y'
                    ])->execute();
                    $count++;
                }
            }
            return $count;
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }


    public function actionCreateKiosk()
    {
        $request = Yii::$app->request;
        $model = new TbKiosk();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isPost) {
                $data = $request->post('TbKiosk', []);
                $model->service_ids = isset($data['service_ids']) ? $data['service_ids'] : null;
            }
            if ($request->isGet) {
                return [
                    'title' => "บันทึกรายการ",
                    'content' => $this->renderAjax('_form_kiosk', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "บันทึกรายการ",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->kiosk_id]),
                ];
            } else {
                return [
                    'title' => "บันทึกรายการ",
                    'content' => $this->renderAjax('_form_kiosk', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            return $this->render('_form_kiosk', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdateKiosk($id)
    {
        $request = Yii::$app->request;
        $model = TbKiosk::findOne($id);
        $model->service_ids = !empty($model['service_ids']) ? explode(",", $model['service_ids']) : null;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isPost) {
                $data = $request->post('TbKiosk', []);
                $model->service_ids = isset($data['service_ids']) ? $data['service_ids'] : null;
            }
            if ($request->isGet) {
                return [
                    'title' => "บันทึกรายการ",
                    'content' => $this->renderAjax('_form_kiosk', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "บันทึกรายการ",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->kiosk_id]),
                ];
            } else {
                return [
                    'title' => "บันทึกรายการ",
                    'content' => $this->renderAjax('_form_kiosk', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            return $this->render('_form_kiosk', [
                'model' => $model,
            ]);
        }
    }

    public function actionSortinputKiosk($id)
    {
        $request = Yii::$app->request;
        $model = TbKiosk::findOne($id);
        $service_ids = !empty($model['service_ids']) ? explode(",", $model['service_ids']) : [];
        $items = [];


        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                foreach ($service_ids as $service_id) {
                    $modelService = TbService::findOne($service_id);
                    $items[$service_id] = [
                        'content' => $modelService['btn_kiosk_name'] . ' (' . $modelService['service_name'] . ')'
                    ];
                }
                return [
                    'title' => "จัดเรียงปุ่ม Kiosk",
                    'content' => $this->renderAjax('_form_sortinput_kiosk', [
                        'model' => $model,
                        'items' => $items,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                $data = $request->post('TbKiosk', []);
                Yii::$app->db->createCommand()->update('tb_kiosk', ['service_ids' => (isset($data['service_ids']) ? $data['service_ids'] : null)], 'kiosk_id = ' . $id . ' ')->execute();
                return [
                    'title' => "จัดเรียงปุ่ม Kiosk",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->kiosk_id]),
                ];
            } else {
                return [
                    'title' => "จัดเรียงปุ่ม Kiosk",
                    'content' => $this->renderAjax('_form_sortinput_kiosk', [
                        'model' => $model,
                        'items' => $items,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            return $this->render('_form_sortinput_kiosk', [
                'model' => $model,
                'items' => $items,
            ]);
        }
    }

    public function actionSaveNhsoToken()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $params = Yii::$app->getRequest()->getRawBody();
        $body = Json::decode($params);
        $model = new TbTokenNhso();
        $model->user_person_id = $body['id_card'];
        $model->smctoken = $body['token'];
        $model->createdby = 1;
        $model->crearedat = Yii::$app->formatter->asDate('now', 'php:Y-m-d H:i:s');
        if ($model->save()) {
            return $model;
        } else {
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    public function actionCreateServiceTslot($id)
    {
        $request = Yii::$app->request;
        $model = TbService::findOne($id);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                $schedules = TbServiceTslot::find()->where(['serviceid' => $id])->asArray()->all();
                $model->schedules = $schedules;
                return [
                    'title' => "บันทึกรายการ",
                    'content' => $this->renderAjax('_form_slot', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                $body = $request->post('TbService');
                $schedules = $body['schedules'];
                $oldIDs = ArrayHelper::map(TbServiceTslot::find()->where(['serviceid' => $id])->asArray()->all(), 'tslotid', 'tslotid');
                foreach ($schedules as $schedule) {
                    $slot = new TbServiceTslot();
                    if (!empty($schedule['tslotid'])) {
                        $slot =  TbServiceTslot::findOne($schedule['tslotid']);
                    }

                    $slot->serviceid = $id;
                    $slot->t_slot_begin = $schedule['t_slot_begin'];
                    $slot->t_slot_end = $schedule['t_slot_end'];
                    $slot->q_limit = $schedule['q_limit'];
                    $slot->q_limitqty = $schedule['q_limitqty'];
                    if (!$slot->save()) {
                        throw new HttpException(422, $slot->errors);
                    }
                }
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($schedules, 'tslotid', 'tslotid')));
                if (!empty($deletedIDs)) {
                    TbServiceTslot::deleteAll(['tslotid' => $deletedIDs]);
                }
                return [
                    'title' => "บันทึกรายการ",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->serviceid]),
                ];
            } else {
                return [
                    'title' => "บันทึกรายการ",
                    'content' => $this->renderAjax('_form_slot', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            return $this->render('_form_slot', [
                'model' => $model,
            ]);
        }
    }


    public function actionDataCallingConfig()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select(['tb_calling_config.*'])
                ->from('tb_calling_config');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'calling_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'calling_id',
                    ],
                    [
                        'attribute' => 'notice_queue',
                    ],
                    [
                        'attribute' => 'notice_queue_status',
                        'value' => function ($model, $key, $index) {
                            return $this->getBadgeStatus($model['notice_queue_status']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'notice_queue_status1',
                        'value' => function ($model, $key, $index) {
                            $checked = $model['notice_queue_status'] == 1 ? 'checked' : '';
                            return '<label class="switch ">' .
                                '<span>เปิด</span>' .
                                '<input type="checkbox" class="success" data-key="' . $model['calling_id'] . '" value="' . $model['notice_queue_status'] . '" ' . $checked . '>' .
                                '<span class="slider round"></span>' .
                                '</label>';
                        },
                        'format' => 'raw'
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => ' {update} {delete} ',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'deleteOptions' => [
                            'class' => 'text-danger'
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {

                            if ($action == 'update') {
                                return Url::to(['/app/settings/update-calling-config', 'id' => $key]);
                            }
                            if ($action == 'delete') {
                                return Url::to(['/app/settings/delete-calling-config', 'id' => $key]);
                            }
                        }

                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }


    public function actionCreateCallingConfig()
    {
        $request = Yii::$app->request;
        $model = new TbCallingConfig();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "เพิ่มจำนวนคิวที่แจ้งเตือน",
                    'content' => $this->renderAjax('_form_calling_config', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "เพิ่มจำนวนคิวที่แจ้งเตือน",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                ];
            } else {
                return [
                    'title' => "เพิ่มจำนวนคิวที่แจ้งเตือน",
                    'content' => $this->renderAjax('_form_calling_config', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionUpdateCallingConfig($id)
    {
        $request = Yii::$app->request;
        $model = TbCallingConfig::findOne($id);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "แก้ไขจำนวนคิว",
                    'content' => $this->renderAjax('_form_calling_config', [
                        'model' => $model,
                    ]),
                    'footer' => '',

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'title' => "แก้ไขจำนวนคิว",
                    'content' => '<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default', 'data-dismiss' => "modal"]),
                    'status' => '200',
                ];
            } else {
                return [
                    'title' => "แก้ไขจำนวนคิว",
                    'content' => $this->renderAjax('_form_calling_config', [
                        'model' => $model,
                    ]),
                    'footer' => '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];
            }
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionDeleteCallingConfig($id)
    {
        $request = Yii::$app->request;
        TbCallingConfig::findOne($id)->delete();
        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionSaveStatusNoticeQueue()
    {
        $request = Yii::$app->request;
        $model = TbCallingConfig::findOne($request->post('id'));
        $model->notice_queue_status = $request->post('value');
        $model->save();

        TbCallingConfig::updateAll(['notice_queue_status' => 0], ['<>', 'calling_id', $request->post('id')]);  //update ข้อมูใน table ทั้งหมด
        return Json::encode($model);
    }
}
