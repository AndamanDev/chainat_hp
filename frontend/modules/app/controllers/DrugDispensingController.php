<?php

namespace frontend\modules\app\controllers;

use frontend\modules\app\models\Personal;
use frontend\modules\app\models\Pharmacy;
use Yii;
use frontend\modules\app\models\TbDrugDispensing;
use frontend\modules\app\models\TbDrugDispensingSearch;
use frontend\modules\app\models\TbPharmacy;
use homer\widgets\tbcolumn\ActionTable;
use homer\widgets\tbcolumn\ColumnData;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Html;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use frontend\modules\app\traits\ModelTrait;
use kartik\form\ActiveForm;


/**
 * DrugDispensingController implements the CRUD actions for TbDrugDispensing model.
 */
class DrugDispensingController extends Controller
{
    use ModelTrait;
    /**
     * @inheritdoc
     */
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
                        'allow' => true,
                        'actions' => ['create-drug-dispensing', 'drug-dispensing-list'],
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                    'delete-user-drug' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'create-drug-dispensing',
            'drug-dispensing-list'
        ])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }


    /**
     * Lists all TbDrugDispensing models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TbDrugDispensingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single TbDrugDispensing model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "TbDrugDispensing #" . $id,
                'content' => $this->renderAjax('view', [
                    'model' => $this->findModel($id),
                ]),
                'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Edit', ['update', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new TbDrugDispensing model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new TbDrugDispensing();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Create new TbDrugDispensing",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "Create new TbDrugDispensing",
                    'content' => '<span class="text-success">Create TbDrugDispensing success</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::a('Create More', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                ];
            } else {
                return [
                    'title' => "Create new TbDrugDispensing",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            }
        } else {
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->dispensing_id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Updates an existing TbDrugDispensing model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Update TbDrugDispensing #" . $id,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "TbDrugDispensing #" . $id,
                    'content' => $this->renderAjax('view', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::a('Edit', ['update', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                ];
            } else {
                return [
                    'title' => "Update TbDrugDispensing #" . $id,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->dispensing_id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Delete an existing TbDrugDispensing model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $this->findModel($id)->delete();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    /**
     * Delete multiple existing TbDrugDispensing model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionBulkDelete()
    {
        $request = Yii::$app->request;
        $pks = explode(',', $request->post('pks')); // Array or selected records primary keys
        foreach ($pks as $pk) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the TbDrugDispensing model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TbDrugDispensing the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TbDrugDispensing::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    public function actionDataDrugDispensing() //รายการรับยาใกล้บ้าน
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select([
                    'tb_drug_dispensing.dispensing_id',
                    'tb_drug_dispensing.pharmacy_drug_id',
                    'tb_drug_dispensing.rx_operator_id',
                    'tb_drug_dispensing.HN',
                    'tb_drug_dispensing.pt_name',
                    'tb_drug_dispensing.doctor_name',
                    'tb_drug_dispensing.prescription_date',
                    'tb_drug_dispensing.dispensing_date',
                    'tb_drug_dispensing.dispensing_status_id',
                    'tb_drug_dispensing.dispensing_by',
                    'tb_drug_dispensing.created_at',
                    'tb_drug_dispensing.created_by',
                    'tb_drug_dispensing.updated_at',
                    'tb_drug_dispensing.updated_by',
                    'tb_drug_dispensing.note',
                    'tb_drug_dispensing.deptname',
                    'tb_dispensing_status.dispensing_status_des',
                    'tb_drug_dispensing.pharmacy_drug_name'
                ])
                ->from('tb_drug_dispensing')
                ->leftJoin('tb_dispensing_status', 'tb_dispensing_status.dispensing_status_id = tb_drug_dispensing.dispensing_status_id')
                ->where([
                    'tb_drug_dispensing.dispensing_status_id' => 1,
                    'tb_drug_dispensing.pharmacy_drug_id' => Yii::$app->user->identity->profile->pharmacy_id
                ])
                ->orderBy('tb_drug_dispensing.dispensing_id DESC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'dispensing_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'rx_operator_id',
                    ],
                    [
                        'attribute' => 'HN',
                    ],
                    [
                        'attribute' => 'pharmacy_drug_name',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'prescription_date',
                        'value' => function ($model) {
                            return $model['prescription_date'] ? Yii::$app->formatter->asDate($model['prescription_date'], 'php:d/m/Y') : '';
                        },
                    ],
                    [
                        'attribute' => 'deptname',
                    ],
                    [
                        'attribute' => 'doctor_name',
                    ],
                    [
                        'attribute' => 'dispensing_date',
                        'value' => function ($model) {
                            return $model['dispensing_date'] ? Yii::$app->formatter->asDate($model['dispensing_date'], 'php:d/m/Y') : '';
                        },
                    ],
                    [
                        'attribute' => 'dispensing_status_des',
                    ],
                    [
                        'class' => ActionTable::class,
                        'template' => '{view} {update} {cancel}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('รายการยา', $url, ['role' => 'modal-remote', 'class' => 'btn btn-success']);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('จ่ายยา', $url, ['role' => 'modal-remote', 'class' => 'btn btn-info']);
                            },
                            'cancel' => function ($url, $model, $key) {
                                return Html::a('ยกเลิกจ่ายยา', $url, ['role' => 'modal-remote', 'class' => 'btn btn-danger']);
                            },
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'view') {
                                return Url::to(['/app/drug-dispensing/view-rx-detail', 'rx_number' => $model['rx_operator_id']]);
                            }
                            if ($action == 'update') {
                                return Url::to(['/app/drug-dispensing/update-dispensing', 'id' => $key]);
                            }
                            if ($action == 'cancel') {
                                return Url::to(['/app/drug-dispensing/cancel-dispensing', 'id' => $key]);
                            }
                        },
                    ]

                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        } else {
            throw new MethodNotAllowedHttpException('method not allowed.');
        }
    }

    public function actionViewRxDetail($rx_number) //ดูรายการยา
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "รายการยา",
                'content' => $this->renderAjax('_columns_rx_detail', [
                    'rx_number' => $rx_number
                ]),
                'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('_columns_rx_detail', ['rx_number' => $rx_number]);
        }
    }


    public function actionUpdateDispensing($id)
    {

        $request = Yii::$app->request;
        $model = TbDrugDispensing::findOne($id);
        $query = (new \yii\db\Query())
            ->select([
                'tb_drug_dispensing.dispensing_id',
                'tb_drug_dispensing.pharmacy_drug_id',
                'tb_drug_dispensing.rx_operator_id',
                'tb_drug_dispensing.HN',
                'tb_drug_dispensing.pt_name',
                'tb_drug_dispensing.doctor_name',
                'tb_drug_dispensing.prescription_date',
                'tb_drug_dispensing.dispensing_date',
                'tb_drug_dispensing.dispensing_status_id',
                'tb_drug_dispensing.dispensing_by',
                'tb_drug_dispensing.created_at',
                'tb_drug_dispensing.created_by',
                'tb_drug_dispensing.updated_at',
                'tb_drug_dispensing.updated_by',
                'tb_drug_dispensing.note',
                'tb_dispensing_status.dispensing_status_des',
                'tb_drug_dispensing.pharmacy_drug_name'
            ])
            ->from('tb_drug_dispensing')
            ->leftJoin('tb_dispensing_status', 'tb_dispensing_status.dispensing_status_id = tb_drug_dispensing.dispensing_status_id')
            ->where(['tb_drug_dispensing.dispensing_id' => $id])
            ->orderBy('tb_drug_dispensing.dispensing_id ASC')
            ->one();
        if ($request->isAjax) {
            if ($request->isPost) {
                $model->dispensing_status_id = 2;
                $model->dispensing_date = Yii::$app->formatter->asDate('now', 'php:Y-m-d H:i:s');
                $model->dispensing_by = Yii::$app->user->identity->id;
                $model->note;
            }

            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "จ่ายยา",
                    'content' => $this->renderAjax('_columns_dispensing', [
                        'model' => $model,
                        'query' => $query
                    ]),
                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "จ่ายยา",
                    'content' => $this->renderAjax('_columns_dispensing', [
                        'model' => $model,
                        'query' => $query
                    ]),
                ];
            }
        } else {
            return $this->render('_columns_dispensing', [
                'model' => $model,
                'query' => $query
            ]);
        }
    }




    public function actionCancelDispensing($id)
    {

        $request = Yii::$app->request;
        $model = TbDrugDispensing::findOne($id);
        $query = (new \yii\db\Query())
            ->select([
                'tb_drug_dispensing.dispensing_id',
                'tb_drug_dispensing.pharmacy_drug_id',
                'tb_drug_dispensing.rx_operator_id',
                'tb_drug_dispensing.HN',
                'tb_drug_dispensing.pt_name',
                'tb_drug_dispensing.doctor_name',
                'tb_drug_dispensing.prescription_date',
                'tb_drug_dispensing.dispensing_date',
                'tb_drug_dispensing.dispensing_status_id',
                'tb_drug_dispensing.dispensing_by',
                'tb_drug_dispensing.created_at',
                'tb_drug_dispensing.created_by',
                'tb_drug_dispensing.updated_at',
                'tb_drug_dispensing.updated_by',
                'tb_drug_dispensing.note',
                'tb_dispensing_status.dispensing_status_des',
                'tb_drug_dispensing.pharmacy_drug_name'
            ])
            ->from('tb_drug_dispensing')
            ->leftJoin('tb_dispensing_status', 'tb_dispensing_status.dispensing_status_id = tb_drug_dispensing.dispensing_status_id')
            ->where(['tb_drug_dispensing.dispensing_id' => $id])
            ->orderBy('tb_drug_dispensing.dispensing_id ASC')
            ->one();
        if ($request->isAjax) {

            if ($request->isPost) {
                $model->dispensing_status_id = 3;
            }
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "ยกเลิกจ่ายยา",
                    'content' => $this->renderAjax('_columns_dispensing', [
                        'model' => $model,
                        'query' => $query
                    ]),
                ];
            } else if ($model->load($request->post(), '') && $model->save()) {
                if ($request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'message' => 'ยกเลิกจ่ายสำเร็จ'
                    ];
                }
                return $this->redirect(['index']);
            }
        } else {
            return $this->render('_columns_dispensing', [
                'model' => $model,
                'query' => $query
            ]);
        }
    }



    public function actionDataRxDetail($rx_number)  // api รายการยา
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders(['content-type' => 'application/json'])
            ->setUrl('http://chainathospital.org/coreapi/api/v2/get_rx_detail')
            ->setData(['rx_number' => $rx_number])
            ->send();
        $query =  [];
        if ($response->isOk) {
            $query = Json::encode(ArrayHelper::getValue($response->data,'data',['']));
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => is_array($query) ? $query : Json::decode($query),
            'pagination' => [
                'pageSize' => false,
            ],
        ]);
        $columns = Yii::createObject([
            'class' => ColumnData::className(),
            'dataProvider' => $dataProvider,
            'formatter' => Yii::$app->getFormatter(),
            'columns' => [
                [
                    'attribute' => 'drug_code',
                ],
                [
                    'attribute' => 'drug_name',
                ],
                [
                    'attribute' => 'qty',
                ],
                [
                    'attribute' => 'drug_unit',
                ],
                [
                    'attribute' => 'drug_seq',
                ],

                [
                    'attribute' => 'drug_warning',
                ],
            ]
        ]);

        return Json::encode(['data' => $columns->renderDataColumns()]);
    }


    public function actionDataDrugHistory() //ประวัติรับยาใกล้บ้าน
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            $query = (new \yii\db\Query())
                ->select([
                    'tb_drug_dispensing.dispensing_id',
                    'tb_drug_dispensing.pharmacy_drug_id',
                    'tb_drug_dispensing.rx_operator_id',
                    'tb_drug_dispensing.HN',
                    'tb_drug_dispensing.pt_name',
                    'tb_drug_dispensing.doctor_name',
                    'tb_drug_dispensing.prescription_date',
                    'tb_drug_dispensing.dispensing_date',
                    'tb_drug_dispensing.dispensing_status_id',
                    'tb_drug_dispensing.dispensing_by',
                    'tb_drug_dispensing.created_at',
                    'tb_drug_dispensing.created_by',
                    'tb_drug_dispensing.updated_at',
                    'tb_drug_dispensing.updated_by',
                    'tb_drug_dispensing.note',
                    'tb_dispensing_status.dispensing_status_des'
                ])
                ->from('tb_drug_dispensing')
                ->leftJoin('tb_dispensing_status', 'tb_dispensing_status.dispensing_status_id = tb_drug_dispensing.dispensing_status_id')
                ->where([
                    'tb_drug_dispensing.dispensing_status_id' => [2, 3],
                    'tb_drug_dispensing.pharmacy_drug_id' => Yii::$app->user->identity->profile->pharmacy_id
                ])
                ->orderBy('tb_drug_dispensing.dispensing_id DESC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],

                'key' => 'rx_operator_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'rx_operator_id',
                    ],
                    [
                        'attribute' => 'HN',
                    ],
                    [
                        'attribute' => 'pt_name',
                    ],
                    [
                        'attribute' => 'prescription_date',
                        'value' => function ($model) {
                            return $model['prescription_date'] ? Yii::$app->formatter->asDate($model['prescription_date'], 'php:d/m/Y') : '';
                        },
                    ],
                    [
                        'attribute' => 'doctor_name',
                    ],
                    [
                        'attribute' => 'dispensing_date',
                        'value' => function ($model) {
                            return $model['dispensing_date'] ? Yii::$app->formatter->asDate($model['dispensing_date'], 'php:d/m/Y') : '';
                        },
                    ],
                    [
                        'attribute' => 'dispensing_status_des',
                    ],
                    [
                        'class' => ActionTable::class,
                        'template' => ' {view} ',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('รายการยา', $url, ['role' => 'modal-remote', 'class' => 'btn btn-success']);
                            },
                        ],
                        'urlCreator' => function ($action, $model, $key, $index) {
                            if ($action == 'view') {
                                return Url::to(['/app/drug-dispensing/view-rx-history', 'rx_number' => $key]);
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



    public function actionViewRxHistory($rx_number) //ดูประวัติรายการยา
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "รายการยา",
                'content' => $this->renderAjax('_columns_rx_history', [
                    'rx_number' => $rx_number
                ]),
                'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('_columns_rx_history', ['rx_number' => $rx_number]);
        }
    }


    /**
     * Creates a new TbDrugDispensing model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionPharmcyDrug() //จัดการร้านขายยา
    {
        return $this->render('_index_pharmacy');
    }

    /**
     * Creates a new TbDrugDispensing model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreatePharmcy()
    {
        $request = Yii::$app->request;
        $model = new TbDrugDispensing();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Create new TbDrugDispensing",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "Create new TbDrugDispensing",
                    'content' => '<span class="text-success">Create TbDrugDispensing success</span>',
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::a('Create More', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                ];
            } else {
                return [
                    'title' => "Create new TbDrugDispensing",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            }
        } else {
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->dispensing_id]);
            } else {
                return $this->render('_form_pharmcy', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionDataPharmacy() //ร้านขายยา
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl('http://chainathospital.org/coreapi/api/core_system/pharmacy_drug')
            ->send();
        $query = [];
        if ($response->isOk) {
            $query = $response->data['data'];
        }

        if(is_array($query)) {
            foreach ($query as $key => $row) {
                $model = TbPharmacy::findOne(['pharmacy_name' => $row['pharmacy_drug_name']]);
                if(empty($model)){
                    $model = new TbPharmacy();
                }
                $model->pharmacy_id = $row['pharmacy_drug_id'];
                $model->pharmacy_name = $row['pharmacy_drug_name'];
                $model->pharmacy_address = $row['pharmacy_drug_address'];
                $model->save();
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
            'pagination' => [
                'pageSize' => false,
            ],
            
            'key' => 'pharmacy_drug_id'
        ]);
        $columns = Yii::createObject([
            'class' => ColumnData::className(),
            'dataProvider' => $dataProvider,
            'formatter' => Yii::$app->getFormatter(),
            'columns' => [
                [
                    'attribute' => 'pharmacy_drug_id',
                ],
                [
                    'attribute' => 'pharmacy_drug_name',
                ],
                [
                    'attribute' => 'pharmacy_drug_address',
                    'value' => function ($model) {
                        return empty($model['pharmacy_drug_address']) ? '-' : $model['pharmacy_drug_address'];
                    },
                ],
                [
                    'attribute' => 'pharmacy_drug_date_create',
                    'value' => function ($model) {
                        return empty($model['pharmacy_drug_date_create']) ? '-' : Yii::$app->formatter->asDate($model['pharmacy_drug_date_create'], 'php:d/m/Y');
                    },
                ],
                // [
                //     'attribute' => 'pharmacy_drug_date_update',
                //     'value' => function ($model) {
                //         return $model['pharmacy_drug_date_update'] ? Yii::$app->formatter->asDate($model['pharmacy_drug_date_update'], 'php:d/m/Y') : '';
                //     },
                // ],
                [
                    'attribute' => 'is_active',
                    'value' => function ($model) {
                        return $model['is_active'] == 0 ? 'UnActive' : 'Active';
                    },
                ],
                [
                    'class' => ActionTable::className(),
                    'template' => '{update}',
                    'updateOptions' => [
                        'role' => 'modal-remote',
                        'class' => 'text-info'
                    ],
                    // 'deleteOptions' => [
                    //     'class' => 'text-danger'
                    // ],
                    'urlCreator' => function ($action, $model, $key, $index) {
                        if ($action == 'update') {
                            return Url::to(['/app/drug-dispensing/update-pharmacy', 'pharmacy_drug_id' => $key]);
                        }
                    }
                ]
            ]
        ]);

        return Json::encode(['data' => $columns->renderDataColumns()]);
    }


    public function actionDataPersonalDrug() //api รายชื่อผู้รับบริการรับยาใกล้บ้าน personal_drug
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl('http://chainathospital.org/coreapi/api/core_system/personal_drug')
            ->send();
        $query = [];
        if ($response->isOk) {
            $query = $response->data['data'];
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
            'pagination' => [
                'pageSize' => false,
            ],
            'sort' => [
                'attributes' => ['personal_drug_id'],
            ],
            'key' => 'hn'
        ]);
        $columns = Yii::createObject([
            'class' => ColumnData::className(),
            'dataProvider' => $dataProvider,
            'formatter' => Yii::$app->getFormatter(),
            'columns' => [
                // [
                //     'attribute' => 'personal_drug_id',
                // ],
                [
                    'attribute' => 'hn',
                ],
                [
                    'attribute' => 'fullname',
                ],
                [
                    'attribute' => 'personal_drug_date_create',
                    'value' => function ($model) {
                        // return $model['personal_drug_date_create'] ? Yii::$app->formatter->asDate($model['personal_drug_date_create'], 'php:d/m/Y') : '';
                        return empty($model['personal_drug_date_create']) ? '-' : Yii::$app->formatter->asDate($model['personal_drug_date_create'], 'php:d/m/Y');
                    },
                ],
                [
                    'attribute' => 'personal_drug_date_update',
                    'value' => function ($model) {
                        return empty($model['personal_drug_date_update']) ? '-' : Yii::$app->formatter->asDate($model['personal_drug_date_update'], 'php:d/m/Y');
                    },

                ],
                [
                    'attribute' => 'is_active',
                    'value' => function ($model) {
                        return $model['is_active'] == 0 ? 'UnActive' : 'Active';
                    },

                ],
                [
                    'class' => ActionTable::className(),
                    'template' => ' {update} ',
                    'updateOptions' => [
                        // 'role' => 'modal-remote',
                        'class' => 'text-success',
                    ],
                    // 'deleteOptions' => [
                    //     'class' => 'text-danger'
                    // ],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            if ($model['is_active'] == 0) {
                                return Html::a('เปิดใช้งาน', $url, [
                                    'class' => 'btn btn-success', 'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะเปิดรายการนี้?',
                                    'data-method' => 'POST'
                                ]);
                            }
                            return Html::a('ปิดใช้งาน', $url, [
                                'class' => 'btn btn-danger', 'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะปิดรายการนี้?',
                                'data-method' => 'POST'
                            ]);
                        },
                    ],
                    'urlCreator' => function ($action, $model, $key, $index) {
                        if ($action == 'update') {
                            return Url::to(['/app/drug-dispensing/update-user-drug', 'hn' => $key]);
                        }
                        // if ($action == 'delete') {
                        //     return Url::to(['drug-dispensing/delete-user-drug', 'hn' => $key]);
                        // }
                    }


                ]
            ]
        ]);

        return Json::encode(['data' => $columns->renderDataColumns()]);
    }


    public function actionGetPatientinfo($hn)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders(['content-type' => 'application/json'])
            ->setUrl('http://chainathospital.org/coreapi/api/v2/get_patient_info/' . $hn)
            ->send();
        if ($response->isOk) {
            return Json::encode(ArrayHelper::getValue($response->data, 'data.0', null));
        }
        return null;
    }


    public function actionCreateUserDrug() //สร้างรายชื่อผู้รับยาใกล้บ้าน
    {
        $request = Yii::$app->request;
        $model = new Personal();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->isGet) {
                return [
                    'title' => "เพิ่มรายการ",
                    'content' => $this->renderAjax('_form_user_drug', [
                        'model' => $model
                    ]),
                    // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                    //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                ];
            }
            if ($model->load($request->post()) && $model->validate()) {
                $client = new Client();
                $response = $client->createRequest()
                    ->setMethod('POST')
                    ->setFormat(Client::FORMAT_JSON)
                    ->addHeaders(['content-type' => 'application/json'])
                    ->setUrl('http://chainathospital.org/coreapi/api/core_system/personal_drug')
                    ->setData($request->post('Personal'))
                    ->send();
                if ($response->isOk) {
                    return [
                        'title' => "เพิ่มรายการ",
                        'content' => '<span class="text-success" style="font-size:18pt;">บันทึกสำเร็จ</span>',
                        //'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"])
                    ];
                } else {
                    return [
                        'title' => "เพิ่มรายการ",
                        'content' => $this->renderAjax('_form_user_drug', [
                            'model' => $model
                        ]),
                        // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                        //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                    ];
                }
            } else {
                return [
                    'title' => "เพิ่มรายการ",
                    'content' => $this->renderAjax('_form_user_drug', [
                        'model' => $model
                    ]),
                    // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                    //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                ];
            }
        } else {
            return $this->render('_form_user_drug', []);
        }
    }


    // public function actionUpdateUserDrug($hn) //สร้างรายชื่อผู้รับยาใกล้บ้าน
    // {

    //     $request = Yii::$app->request;
    //     \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    //     $client = new Client();
    //     $model = new Personal();

    //     if ($request->isAjax) {
    //         /*
    //         *   Process for ajax request
    //         */
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         if ($request->isGet) {
    //             $response = $client->createRequest()
    //                 ->setMethod('GET')
    //                 ->setFormat(Client::FORMAT_JSON)
    //                 ->addHeaders(['content-type' => 'application/json'])
    //                 ->setUrl('http://chainathospital.org/coreapi/api/core_system/personal_drug/' . $hn)
    //                 ->send();

    //             if ($response->isOk) {
    //                 $query = $response->data['data'];
    //                 $model->hn = ArrayHelper::getValue($query, '0.hn', null);
    //                 $model->pt_name = ArrayHelper::getValue($query, '0.fullname', null);
    //             }
    //             return [
    //                 'title' => "แก้ไขรายการ",
    //                 'content' => $this->renderAjax('_form_user_drug', [
    //                     'model' => $model
    //                 ]),
    //                 // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
    //                 //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
    //             ];
    //         }
    //         if ($model->load($request->post()) && $model->validate()) {
    //             $response = $client->createRequest()
    //                 ->setMethod('POST')
    //                 ->setFormat(Client::FORMAT_JSON)
    //                 ->addHeaders(['content-type' => 'application/json'])
    //                 ->setUrl('#' . $hn)
    //                 ->setData($request->post('Personal'))
    //                 ->send();
    //             if ($response->isOk) {
    //                 return [
    //                     'title' => "แก้ไขรายการ",
    //                     'content' => '<span class="text-success">บันทึกสำเร็จ</span>',
    //                     //'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"])
    //                 ];
    //             } else {
    //                 return [
    //                     'title' => "แก้ไขรายการ",
    //                     'content' => $this->renderAjax('_form_user_drug', [
    //                         'model' => $model
    //                     ]),
    //                     // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
    //                     //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
    //                 ];
    //             }
    //         } else {
    //             return [
    //                 'title' => "แก้ไขรายการ",
    //                 'content' => $this->renderAjax('_form_user_drug', [
    //                     'model' => $model
    //                 ]),
    //                 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
    //                     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
    //             ];
    //         }
    //     } else {
    //         return $this->render('_form_user_drug', []);
    //     }
    // }

    public function actionUpdateUserDrug($hn)
    {
        $request = Yii::$app->request;

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('PUT')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders(['content-type' => 'application/json'])
            ->setUrl('http://chainathospital.org/coreapi/api/core_system/personal_drug')
            ->setData(['hn' => $hn])
            ->send();
        if ($response->isOk) {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['message' => 'Update'];
            }
            return $this->redirect(['pharmcy-drug']);
        }
        throw new HttpException('เกิดข้อผิดพลาดในการ UPDATE!');
        return $this->redirect(['pharmcy-drug']);
    }

    public function actionDeleteUserDrug($hn) //ลบรายชื่อผู้รับยาใกล้บ้าน
    {
        $request = Yii::$app->request;

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('DELETE')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders(['content-type' => 'application/json'])
            ->setUrl('http://chainathospital.org/coreapi/api/core_system/personal_drug/' . $hn)
            ->send();
        if ($response->isOk) {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['message' => 'Delete'];
            }
            return $this->redirect(['pharmcy-drug']);
        }
        throw new HttpException('เกิดข้อผิดพลาดในการลบข้อมูล!');
        return $this->redirect(['pharmcy-drug']);
    }


    public function actionCreatePharmacy() //บันทึกร้านขายยาใกล้บ้าน
    {
        $request = Yii::$app->request;
        $model = new Pharmacy();

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "เพิ่มรายการ",
                    'content' => $this->renderAjax('_form_pharmcy', [
                        'model' => $model
                    ]),
                    // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                    //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                ];
            }
            if ($model->load($request->post()) && $model->validate()) {
                $client = new Client();
                $response = $client->createRequest()
                    ->setMethod('POST')
                    ->setFormat(Client::FORMAT_JSON)
                    ->addHeaders(['content-type' => 'application/json'])
                    ->setUrl('http://chainathospital.org/coreapi/api/core_system/pharmacy_drug')
                    ->setData($request->post('Pharmacy'))
                    ->send();
                if ($response->isOk) {
                    return [
                        'title' => "เพิ่มรายการ",
                        'content' => '<span class="text-success">บันทึกสำเร็จ</span>',
                        // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                        //     Html::a('Create More', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                } else {
                    return [
                        'title' => "เพิ่มรายการ",
                        'content' => $this->renderAjax('_form_pharmcy', [
                            'model' => $model
                        ]),
                        // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                        //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                    ];
                }
            } else {
                return [
                    'title' => "เพิ่มรายการ",
                    'content' => $this->renderAjax('_form_pharmcy', [
                        'model' => $model
                    ]),
                    // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                    //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                ];
            }
        } else {
            return $this->render('_form_pharmcy', []);
        }
    }


    public function actionUpdatePharmacy($pharmacy_drug_id) //บันทึกร้านขายยาใกล้บ้าน
    {
        $request = Yii::$app->request;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $client = new Client();
        $model = new Pharmacy();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setFormat(Client::FORMAT_JSON)
                    ->addHeaders(['content-type' => 'application/json'])
                    ->setUrl('http://chainathospital.org/coreapi/api/core_system/pharmacy_drug/' . $pharmacy_drug_id)
                    ->send();

                if ($response->isOk) {
                    $query = $response->data['data'];
                    $model->pharmacy_drug_name = ArrayHelper::getValue($query, '0.pharmacy_drug_name', null);
                    $model->pharmacy_drug_address = ArrayHelper::getValue($query, '0.pharmacy_drug_address', null);
                }
                return [
                    'title' => "แก้ไขรายการ",
                    'content' => $this->renderAjax('_form_pharmcy', [
                        'model' => $model
                    ]),
                    // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                    //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                ];
            }
            if ($model->load($request->post()) && $model->validate()) {
                $response = $client->createRequest()
                    ->setMethod('POST')
                    ->setFormat(Client::FORMAT_JSON)
                    ->addHeaders(['content-type' => 'application/json'])
                    ->setUrl('http://chainathospital.org/coreapi/api/core_system/pharmacy_drug/' . $pharmacy_drug_id)
                    ->setData($request->post('Pharmacy'))
                    ->send();
                if ($response->isOk) {
                    return [
                        'title' => "แก้ไขรายการ",
                        'content' => '<span class="text-success">บันทึกสำเร็จ</span>',
                        // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"])
                    ];
                } else {
                    return [
                        'title' => "แก้ไขรายการ",
                        'content' => $this->renderAjax('_form_pharmcy', [
                            'model' => $model
                        ]),
                        // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                        //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                    ];
                }
            } else {
                return [
                    'title' => "แก้ไขรายการ",
                    'content' => $this->renderAjax('_form_pharmcy', [
                        'model' => $model
                    ]),
                    // 'footer' => Html::button('Close', ['class' => 'btn btn-default pull-right', 'data-dismiss' => "modal"]) .
                    //     Html::button('Save', ['class' => 'btn btn-success', 'type' => "submit", 'style' => 'margin-right: .25rem;'])
                ];
            }
        } else {
            return $this->render('_form_pharmcy', []);
        }
    }

    public function  actionCreateDrugDispensing() //สร้างรายการรับยาใกล้บ้าน
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $params = Json::decode(\Yii::$app->getRequest()->getRawBody());


        if (!ArrayHelper::getValue($params, 'patient_info', null)) {
            throw new HttpException(400, 'invalid patient_info.');
        }
        if (!ArrayHelper::getValue($params, 'rx_list_by_qn', null)) {
            throw new HttpException(400, 'invalid rx_list_by_qn.');
        }
        if (!ArrayHelper::getValue($params, 'dispensing_status_id', null)) {
            throw new HttpException(400, 'invalid dispensing_status_id.');
        }

        $patient_info = $params['patient_info']; // ข้อมูลผู้ป่วย pt_name,hn,cid
        $HN = ArrayHelper::getValue($patient_info, 'hn', null);  //หมายเลข hn
        $pt_name = ArrayHelper::getValue($patient_info, 'pt_name', null); // ชื่อผู้ป่วย



        $rx_list_by_qn = $params['rx_list_by_qn']; //รายการใบสั่งยา by qn
        $rx_operator_id = ArrayHelper::getValue($rx_list_by_qn, 'rx_operator_id', null); //เลขที่ใบสั่งยา
        $pharmacy_drug_id = ArrayHelper::getValue($rx_list_by_qn, 'pharmacy_drug_id', null); //id ร้านขายยา
        $pharmacy_drug_name = ArrayHelper::getValue($rx_list_by_qn, 'pharmacy_drug_name', null);  //ชื่อ ร้านขายยา
        $doctor_name = ArrayHelper::getValue($rx_list_by_qn, 'doctor_name', null); // ชื่อแพทย์สั่งยา
        $prescription_date = ArrayHelper::getValue($rx_list_by_qn, 'prescription_date', null); // วันที่สั่งยา
        $dept_code = ArrayHelper::getValue($rx_list_by_qn, 'dept_code', null); //รหัสแผนก
        $deptname = ArrayHelper::getValue($rx_list_by_qn, 'department', null); //ชื่อแผนก



        $dispensing_status_id = ArrayHelper::getValue($params, 'dispensing_status_id', 1); //สถานะ
        //  $deptname = ArrayHelper::getValue($params, 'deptname', null); //ชื่อแผนก

        // $modelService = $this->findModelService($deptname); // กลุ่มบริการ



        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $modelDrug = TbDrugDispensing::find()
                ->where([
                    'hn' => $HN,
                    'rx_operator_id' => $rx_operator_id
                ])
                ->andWhere('DATE(created_at) = CURRENT_DATE')
                ->one();

            if (!$modelDrug) {
                $modelDrug = new TbDrugDispensing();
            }
            $modelDrug->setAttributes([
                'pharmacy_drug_id' => $pharmacy_drug_id,
                'pharmacy_drug_name' => $pharmacy_drug_name,
                'rx_operator_id' => $rx_operator_id,
                'HN' => $HN,
                'pt_name' => $pt_name,
                'doctor_name' => $doctor_name,
                'prescription_date' => $prescription_date,
                'dispensing_status_id' => $dispensing_status_id, //สถานะ
                'dept_code' => $dept_code, //รหัสแผนก
                'deptname' => $deptname, //ชื่อแผนก
            ]);

            if ($modelDrug->save()) {
                $transaction->commit();
                return [
                    'modelDrug' => $modelDrug,
                ];
            } else {
                $transaction->rollBack();
                throw new HttpException(422, Json::encode($modelDrug->errors));
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function actionDrugDispensingList($hn, $date = null) //รายการรับยาใกล้บ้านให้ Mobile
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$hn) {
            throw new HttpException(400, 'invalid hn.');
        }
        if (!$date) {
            throw new HttpException(400, 'invalid date.');
        }

        $query = (new \yii\db\Query())  //สถานะคิว
            ->select([
                'tb_drug_dispensing.rx_operator_id',
                'tb_drug_dispensing.pharmacy_drug_name',
                'tb_drug_dispensing.deptname',
                'tb_drug_dispensing.HN',
                'tb_drug_dispensing.pt_name',
                'tb_drug_dispensing.doctor_name',
                'tb_drug_dispensing.prescription_date',
                'tb_dispensing_status.dispensing_status_des'
            ])
            ->from('tb_drug_dispensing')
            ->innerJoin('tb_dispensing_status', ' tb_drug_dispensing.dispensing_status_id = tb_dispensing_status.dispensing_status_id')
            ->where([
                'tb_drug_dispensing.HN' => $hn
            ]);
        if ($date) {
            $query->andWhere(['DATE( tb_drug_dispensing.prescription_date )' => $date]);
        } else {
            $query->andWhere('DATE( tb_drug_dispensing.prescription_date ) = CURRENT_DATE');
        }

        $drug = $query->all();
        if (!$drug) {
            return [
                'status' => false,
                'message' => 'ไม่พบข้อมูล HN กรุณาตรวจสอบข้อมูล!',
                'data' => $drug
            ];
        } else {
            return [
                'status' => true,
                'message' => 'success',
                'data' => $drug
            ];
        }
    }
}
