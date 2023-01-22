<?php

namespace frontend\modules\settings\controllers;

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
use yii\icons\Icon;
use kartik\form\ActiveForm;
use common\models\MultipleModel;
use yii\base\Model;
use yii\db\Expression;
use Intervention\Image\ImageManagerStatic;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;
#models
use frontend\modules\kiosk\models\TbPtVisitType;
use frontend\modules\kiosk\models\TbSection;
use frontend\modules\kiosk\models\TbTicket;
use frontend\modules\kiosk\models\TbServiceStatus;
use frontend\modules\kiosk\models\TbCounterserviceType;
use frontend\modules\kiosk\models\TbCounterservice;
use frontend\modules\kiosk\models\TbServiceMdName;
use frontend\modules\kiosk\models\TbDisplayConfig;
/**
 * Default controller for the `settings` module
 */
class DefaultController extends Controller
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
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'delete-visit-type' => ['post'],
                    'delete-section' => ['post'],
                    'delete-ticket' => ['post'],
                    'delete-service-status' => ['post'],
                    'delete-counter-type' => ['post'],
                    'delete-display' => ['post'],
                ],
            ],
        ];
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
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionDataVisitType(){
        $request = Yii::$app->request;

        if($request->isAjax){
            $query = (new \yii\db\Query())
            ->select(['tb_pt_visit_type.*'])
            ->from('tb_pt_visit_type');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'pt_visit_type_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'pt_visit_type_id',
                    ],
                    [
                        'attribute' => 'pt_visit_type',
                    ],
                    [
                        'attribute' => 'pt_visit_type_prefix',
                    ],
                    [
                        'attribute' => 'pt_visit_type_digit',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'urlCreator' => function ( $action, $model, $key, $index) {
                            if($action == 'update'){
                                return Url::to(['/settings/default/create-visit-type']);
                            }
                            if($action == 'delete'){
                                return Url::to(['/settings/default/delete-visit-type','id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionCreateVisitType(){
        $request = Yii::$app->request;
        $models = TbPtVisitType::find()->all();
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'     => "จัดการประเภท",
                    'content'   => $this->renderAjax('_form_visit_type', [
                        'models' => (empty($models)) ? [new TbPtVisitType] : $models,
                    ]),
                    'footer'=>  ''
                ];         
            }elseif(Model::loadMultiple($models, Yii::$app->request->post()) && Model::validateMultiple($models)){
                $oldIDs = ArrayHelper::map($models, 'pt_visit_type_id', 'pt_visit_type_id');
                $models = MultipleModel::createMultiple(TbPtVisitType::classname(), $models,'pt_visit_type_id');
                MultipleModel::loadMultiple($models, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($models, 'pt_visit_type_id', 'pt_visit_type_id')));

                // validate all models
                $valid = MultipleModel::validateMultiple($models);
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = true) {
                            if (! empty($deletedIDs)) {
                                TbPtVisitType::deleteAll(['pt_visit_type_id' => $deletedIDs]);
                            }
                            foreach ($models as $model) {
                                if (! ($flag = $model->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title'=> "จัดการประเภท",
                                'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                }else{           
                    return [
                        'title'=> "จัดการประเภท",
                        'content'=>$this->renderAjax('_form_visit_type', [
                            'models' => (empty($models)) ? [new TbPtVisitType] : $models,
                        ]),
                        'footer' => '',
                        'validate' => ActiveForm::validateMultiple($models),
                        'status' => 'error'
                    ];         
                }
            }else{           
                return [
                    'title'=> "จัดการประเภท",
                    'content'=>$this->renderAjax('_form_visit_type', [
                        'models' => (empty($models)) ? [new TbPtVisitType] : $models,
                    ]),
                    'footer' => '',
                    'validate' => ActiveForm::validateMultiple($models),
                    'status' => 'error'
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDeleteVisitType($id)
    {
        $request = Yii::$app->request;
        TbPtVisitType::findOne($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionCreateSection(){
        $request = Yii::$app->request;
        $models = TbSection::find()->all();
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'     => "จัดการ แผนก/คลีนิค",
                    'content'   => $this->renderAjax('_form_section', [
                        'models' => (empty($models)) ? [new TbSection] : $models,
                    ]),
                    'footer'=>  ''
                ];         
            }elseif(Model::loadMultiple($models, Yii::$app->request->post()) && Model::validateMultiple($models)){
                $oldIDs = ArrayHelper::map($models, 'sec_id', 'sec_id');
                $models = MultipleModel::createMultiple(TbSection::classname(), $models,'sec_id');
                MultipleModel::loadMultiple($models, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($models, 'sec_id', 'sec_id')));

                // validate all models
                $valid = MultipleModel::validateMultiple($models);
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = true) {
                            if (! empty($deletedIDs)) {
                                TbSection::deleteAll(['sec_id' => $deletedIDs]);
                            }
                            foreach ($models as $model) {
                                if (! ($flag = $model->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title'=> "จัดการ แผนก/คลีนิค",
                                'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                }else{           
                    return [
                        'title'=> "จัดการ แผนก/คลีนิค",
                        'content'=>$this->renderAjax('_form_section', [
                            'models' => (empty($models)) ? [new TbSection] : $models,
                        ]),
                        'footer' => '',
                        'validate' => ActiveForm::validateMultiple($models),
                        'status' => 'error'
                    ];         
                }
            }else{           
                return [
                    'title'=> "จัดการ แผนก/คลีนิค",
                    'content'=>$this->renderAjax('_form_section', [
                        'models' => (empty($models)) ? [new TbSection] : $models,
                    ]),
                    'footer' => '',
                    'validate' => ActiveForm::validateMultiple($models),
                    'status' => 'error'
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDataSection(){
        $request = Yii::$app->request;

        if($request->isAjax){
            $query = (new \yii\db\Query())
            ->select(['tb_section.*'])
            ->from('tb_section');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'sec_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'sec_id',
                    ],
                    [
                        'attribute' => 'sec_name',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'urlCreator' => function ( $action, $model, $key, $index) {
                            if($action == 'update'){
                                return Url::to(['/settings/default/create-section']);
                            }
                            if($action == 'delete'){
                                return Url::to(['/settings/default/delete-section','id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDeleteSection($id)
    {
        $request = Yii::$app->request;
        TbSection::findOne($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDataTicket(){
        $request = Yii::$app->request;

        if($request->isAjax){
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
                        'value' => function($model, $key, $index, $column){
                            return ($model['status'] == 1) ? 'ใช้งาน' : 'ปิดใช้งาน';
                        }
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'urlCreator' => function ( $action, $model, $key, $index) {
                            if($action == 'update'){
                                return Url::to(['/settings/default/update-ticket','id' => $key]);
                            }
                            if($action == 'delete'){
                                return Url::to(['/settings/default/delete-ticket','id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionCreateTicket()
    {
        $request = Yii::$app->request;
        $model = new TbTicket();

        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "จัดการข้อมูลบัตรคิว",
                    'content'=>$this->renderAjax('_form_ticket', [
                        'model' => $model,
                    ]),
                    'footer'=> '',
        
                ];         
            }else if($model->load($request->post()) && $model->save()){
                return [
                    'title'=> "จัดการข้อมูลบัตรคิว",
                    'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->ids]),
                ];         
            }else{           
                return [
                    'title'=> "จัดการข้อมูลบัตรคิว",
                    'content'=>$this->renderAjax('_form_ticket', [
                        'model' => $model,
                    ]),
                    'footer'=> '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionUpdateTicket($id)
    {
        $request = Yii::$app->request;
        $model = TbTicket::findOne($id);

        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "จัดการข้อมูลบัตรคิว",
                    'content'=>$this->renderAjax('_form_ticket', [
                        'model' => $model,
                    ]),
                    'footer'=> '',
        
                ];         
            }else if($model->load($request->post()) && $model->save()){
                return [
                    'title'=> "จัดการข้อมูลบัตรคิว",
                    'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->ids]),
                ];         
            }else{           
                return [
                    'title'=> "จัดการข้อมูลบัตรคิว",
                    'content'=>$this->renderAjax('_form_ticket', [
                        'model' => $model,
                    ]),
                    'footer'=> '',
                    'status' => 'error',
                    'validate' => ActiveForm::validate($model),
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDeleteTicket($id)
    {
        $request = Yii::$app->request;
        TbTicket::findOne($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDataServiceStatus(){
        $request = Yii::$app->request;

        if($request->isAjax){
            $query = (new \yii\db\Query())
            ->select(['tb_service_status.*'])
            ->from('tb_service_status');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'service_status_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'service_status_id',
                    ],
                    [
                        'attribute' => 'service_status_name',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'urlCreator' => function ( $action, $model, $key, $index) {
                            if($action == 'update'){
                                return Url::to(['/settings/default/create-service-status']);
                            }
                            if($action == 'delete'){
                                return Url::to(['/settings/default/delete-service-status','id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionCreateServiceStatus(){
        $request = Yii::$app->request;
        $models = TbServiceStatus::find()->all();
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'     => "จัดการสถานะคิว",
                    'content'   => $this->renderAjax('_form_service_status', [
                        'models' => (empty($models)) ? [new TbServiceStatus] : $models,
                    ]),
                    'footer'=>  ''
                ];         
            }elseif(Model::loadMultiple($models, Yii::$app->request->post()) && Model::validateMultiple($models)){
                $oldIDs = ArrayHelper::map($models, 'service_status_id', 'service_status_id');
                $models = MultipleModel::createMultiple(TbServiceStatus::classname(), $models,'service_status_id');
                MultipleModel::loadMultiple($models, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($models, 'service_status_id', 'service_status_id')));

                // validate all models
                $valid = MultipleModel::validateMultiple($models);
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = true) {
                            if (! empty($deletedIDs)) {
                                TbServiceStatus::deleteAll(['service_status_id' => $deletedIDs]);
                            }
                            foreach ($models as $model) {
                                if (! ($flag = $model->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title'=> "จัดการสถานะคิว",
                                'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                }else{           
                    return [
                        'title'=> "จัดการสถานะคิว",
                        'content'=>$this->renderAjax('_form_service_status', [
                            'models' => (empty($models)) ? [new TbServiceStatus] : $models,
                        ]),
                        'footer' => '',
                        'validate' => ActiveForm::validateMultiple($models),
                        'status' => 'error'
                    ];         
                }
            }else{           
                return [
                    'title'=> "จัดการสถานะคิว",
                    'content'=>$this->renderAjax('_form_service_status', [
                        'models' => (empty($models)) ? [new TbServiceStatus] : $models,
                    ]),
                    'footer' => '',
                    'validate' => ActiveForm::validateMultiple($models),
                    'status' => 'error'
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDeleteServiceStatus($id)
    {
        $request = Yii::$app->request;
        TbServiceStatus::findOne($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDataCounterType(){
        $request = Yii::$app->request;

        if($request->isAjax){
            $query = (new \yii\db\Query())
            ->select(['tb_counterservice_type.*'])
            ->from('tb_counterservice_type');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'tb_counterservice_typeid'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'tb_counterservice_typeid',
                    ],
                    [
                        'attribute' => 'tb_counterservice_type',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'urlCreator' => function ( $action, $model, $key, $index) {
                            if($action == 'update'){
                                return Url::to(['/settings/default/create-counter-type']);
                            }
                            if($action == 'delete'){
                                return Url::to(['/settings/default/delete-counter-type','id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDeleteCounterType($id)
    {
        $request = Yii::$app->request;
        TbCounterserviceType::findOne($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionCreateCounterType(){
        $request = Yii::$app->request;
        $models = TbCounterserviceType::find()->all();
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'     => "จัดการประเภทเคาน์เตอร์",
                    'content'   => $this->renderAjax('_form_counter_type', [
                        'models' => (empty($models)) ? [new TbCounterserviceType] : $models,
                    ]),
                    'footer'=>  ''
                ];         
            }elseif(Model::loadMultiple($models, Yii::$app->request->post()) && Model::validateMultiple($models)){
                $oldIDs = ArrayHelper::map($models, 'tb_counterservice_typeid', 'tb_counterservice_typeid');
                $models = MultipleModel::createMultiple(TbCounterserviceType::classname(), $models,'tb_counterservice_typeid');
                MultipleModel::loadMultiple($models, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($models, 'tb_counterservice_typeid', 'tb_counterservice_typeid')));

                // validate all models
                $valid = MultipleModel::validateMultiple($models);
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = true) {
                            if (! empty($deletedIDs)) {
                                TbCounterserviceType::deleteAll(['tb_counterservice_typeid' => $deletedIDs]);
                            }
                            foreach ($models as $model) {
                                if (! ($flag = $model->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title'=> "จัดการประเภทเคาน์เตอร์",
                                'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                }else{           
                    return [
                        'title'=> "จัดการประเภทเคาน์เตอร์",
                        'content'=>$this->renderAjax('_form_counter_type', [
                            'models' => (empty($models)) ? [new TbCounterserviceType] : $models,
                        ]),
                        'footer' => '',
                        'validate' => ActiveForm::validateMultiple($models),
                        'status' => 'error'
                    ];         
                }
            }else{           
                return [
                    'title'=> "จัดการประเภทเคาน์เตอร์",
                    'content'=>$this->renderAjax('_form_counter_type', [
                        'models' => (empty($models)) ? [new TbCounterserviceType] : $models,
                    ]),
                    'footer' => '',
                    'validate' => ActiveForm::validateMultiple($models),
                    'status' => 'error'
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDataCounter(){
        $request = Yii::$app->request;

        if($request->isAjax){
            $query = (new \yii\db\Query())
            ->select(['tb_counterservice.*','tb_counterservice_type.*','tb_section.*'])
            ->from('tb_counterservice')
            ->innerJoin('tb_counterservice_type','tb_counterservice_type.tb_counterservice_typeid = tb_counterservice.counterservice_type')
            ->innerJoin('tb_section','tb_section.sec_id = tb_counterservice.sec_id');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'counterserviceid'
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
                        'attribute' => 'sec_name',
                    ],
                    [
                        'attribute' => 'sound_stationid',
                    ],
                    [
                        'attribute' => 'sound_typeid',
                    ],
                    [
                        'attribute' => 'counterservice_status',
                    ],
                    [
                        'attribute' => 'counterservice_name',
                    ],
                    [
                        'attribute' => 'counterservice_type',
                    ],
                    [
                        'attribute' => 'tb_counterservice_type',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'urlCreator' => function ( $action, $model, $key, $index) {
                            if($action == 'update'){
                                return Url::to(['/settings/default/create-counter']);
                            }
                            if($action == 'delete'){
                                return Url::to(['/settings/default/delete-counter','id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionCreateCounter(){
        $request = Yii::$app->request;
        $models = TbCounterservice::find()->all();
        if(!$models){
            $models = [new TbCounterservice];
        }
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'     => "จัดการเคาน์เตอร์",
                    'content'   => $this->renderAjax('_form_counter', [
                        'models' => (empty($models)) ? [new TbCounterservice] : $models,
                    ]),
                    'footer'=>  ''
                ];         
            }elseif(Model::loadMultiple($models, Yii::$app->request->post()) && Model::validateMultiple($models)){
                $oldIDs = ArrayHelper::map($models, 'counterserviceid', 'counterserviceid');
                $models = MultipleModel::createMultiple(TbCounterservice::classname(), $models,'counterserviceid');
                MultipleModel::loadMultiple($models, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($models, 'counterserviceid', 'counterserviceid')));

                // validate all models
                $valid = MultipleModel::validateMultiple($models);
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = true) {
                            if (! empty($deletedIDs)) {
                                TbCounterservice::deleteAll(['counterserviceid' => $deletedIDs]);
                            }
                            foreach ($models as $model) {
                                if (! ($flag = $model->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title'=> "จัดการเคาน์เตอร์",
                                'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                }else{           
                    return [
                        'title'=> "จัดการเคาน์เตอร์",
                        'content'=>$this->renderAjax('_form_counter', [
                            'models' => (empty($models)) ? [new TbCounterservice] : $models,
                        ]),
                        'footer' => '',
                        'validate' => ActiveForm::validateMultiple($models),
                        'status' => 'error'
                    ];         
                }
            }else{           
                return [
                    'title'=> "จัดการเคาน์เตอร์",
                    'content'=>$this->renderAjax('_form_counter', [
                        'models' => (empty($models)) ? [new TbCounterservice] : $models,
                    ]),
                    'footer' => '',
                    'validate' => ActiveForm::validateMultiple($models),
                    'status' => 'error'
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDeleteCounter($id)
    {
        $request = Yii::$app->request;
        TbCounterservice::findOne($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionCreateDoctor(){
        $request = Yii::$app->request;
        $models = TbServiceMdName::find()->all();
        if(!$models){
            $models = [new TbServiceMdName];
        }
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'     => "บันทึกชื่อแพทย์",
                    'content'   => $this->renderAjax('_form_doctor', [
                        'models' => (empty($models)) ? [new TbServiceMdName] : $models,
                    ]),
                    'footer'=>  ''
                ];         
            }elseif(Model::loadMultiple($models, $request->post()) && Model::validateMultiple($models)){
                $oldIDs = ArrayHelper::map($models, 'service_md_name_id', 'service_md_name_id');
                $models = MultipleModel::createMultiple(TbServiceMdName::classname(), $models,'service_md_name_id');
                MultipleModel::loadMultiple($models, $request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($models, 'service_md_name_id', 'service_md_name_id')));

                // validate all models
                $valid = MultipleModel::validateMultiple($models);
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = true) {
                            if (! empty($deletedIDs)) {
                                TbServiceMdName::deleteAll(['service_md_name_id' => $deletedIDs]);
                            }
                            foreach ($models as $model) {
                                if (! ($flag = $model->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return [
                                'title'=> "บันทึกชื่อแพทย์",
                                'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                                'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                                'status' => '200'
                            ];
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                }else{
                    return [
                        'title'=> "บันทึกชื่อแพทย์",
                        'content'=>$this->renderAjax('_form_doctor', [
                            'models' => (empty($models)) ? [new TbServiceMdName] : $models,
                        ]),
                        'footer' => '',
                        'validate' => ActiveForm::validateMultiple($models),
                        'status' => 'error'
                    ];         
                }
            }else{
                return [
                    'title'=> "บันทึกชื่อแพทย์",
                    'content'=>$this->renderAjax('_form_doctor', [
                        'models' => (empty($models)) ? [new TbServiceMdName] : $models,
                    ]),
                    'footer' => '',
                    'validate' => ActiveForm::validateMultiple($models),
                    'status' => 'error'
                ];
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDataDoctor(){
        $request = Yii::$app->request;

        if($request->isAjax){
            $query = (new \yii\db\Query())
            ->select(['tb_service_md_name.*'])
            ->from('tb_service_md_name');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'service_md_name_id'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::className(),
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'service_md_name_id',
                    ],
                    [
                        'attribute' => 'service_md_name',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{update} {delete}',
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'urlCreator' => function ( $action, $model, $key, $index) {
                            if($action == 'update'){
                                return Url::to(['/settings/default/create-doctor']);
                            }
                            if($action == 'delete'){
                                return Url::to(['/settings/default/delete-doctor','id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDeleteDoctor($id)
    {
        $request = Yii::$app->request;
        TbServiceMdName::findOne($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionDataDisplay(){
        $request = Yii::$app->request;

        if($request->isAjax){
            $query = (new \yii\db\Query())
            ->select(['tb_display_config.*','tb_counterservice_type.*'])
            ->from('tb_display_config')
            ->innerJoin('tb_counterservice_type','tb_counterservice_type.tb_counterservice_typeid = tb_display_config.counterservice_type');

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
                        'attribute' => 'tb_counterservice_type',
                    ],
                    [
                        'attribute' => 'title_left',
                    ],
                    [
                        'attribute' => 'title_right',
                    ],
                    [
                        'attribute' => 'title_color',
                        'value' => function($model, $key, $index, $column){
                            return Html::tag('span',$model['title_color'],['class' => 'badge','style' => 'background-color: '. $model['title_color']]);
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
                        'value' => function($model, $key, $index, $column){
                            return Html::tag('span',$model['header_color'],['class' => 'badge','style' => 'background-color: '. $model['header_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'column_color',
                        'value' => function($model, $key, $index, $column){
                            return Html::tag('span',$model['column_color'],['class' => 'badge','style' => 'background-color: '. $model['column_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'background_color',
                        'value' => function($model, $key, $index, $column){
                            return Html::tag('span',$model['background_color'],['class' => 'badge','style' => 'background-color: '. $model['background_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'font_color',
                        'value' => function($model, $key, $index, $column){
                            return Html::tag('span',$model['font_color'],['class' => 'badge','style' => 'background-color: '. $model['font_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'border_color',
                        'value' => function($model, $key, $index, $column){
                            return Html::tag('span',$model['border_color'],['class' => 'badge','style' => 'background-color: '. $model['border_color']]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'class' => ActionTable::className(),
                        'template' => '{view} {update} {delete}',
                        'viewOptions' => [
                            'target' => '_blank'
                        ],
                        'updateOptions' => [
                            'role' => 'modal-remote'
                        ],
                        'urlCreator' => function ( $action, $model, $key, $index) {
                            if($action == 'view'){
                                return Url::to(['/kiosk/display/index','id' => $key]);
                            }
                            if($action == 'update'){
                                return Url::to(['/settings/default/update-display','id' => $key]);
                            }
                            if($action == 'delete'){
                                return Url::to(['/settings/default/delete-display','id' => $key]);
                            }
                        }
                    ]
                ]
            ]);

            return Json::encode(['data' => $columns->renderDataColumns()]);
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionDeleteDisplay($id)
    {
        $request = Yii::$app->request;
        TbDisplayConfig::findOne($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    public function actionCreateDisplay()
    {
        $request = Yii::$app->request;
        $model = new TbDisplayConfig();

        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "จัดการข้อมูลจอแสดงผล",
                    'content'=>$this->renderAjax('_form_display', [
                        'model' => $model,
                    ]),
                    'footer'=> '',
        
                ];         
            }else if($model->load($request->post()) && $model->save()){
                return [
                    'title'=> "จัดการข้อมูลจอแสดงผล",
                    'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                    'status' => '200',
                    'url' => Url::to(['update', 'id' => $model->display_ids]),
                ];         
            }else{           
                return [
                    'title'=> "จัดการข้อมูลจอแสดงผล",
                    'content'=>$this->renderAjax('_form_display', [
                        'model' => $model,
                    ]),
                    'footer'=> '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }

    public function actionUpdateDisplay($id)
    {
        $request = Yii::$app->request;
        $model = TbDisplayConfig::findOne($id);

        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "จัดการข้อมูลจอแสดงผล",
                    'content'=>$this->renderAjax('_form_display', [
                        'model' => $model,
                    ]),
                    'footer'=> '',
        
                ];         
            }else if($model->load($request->post()) && $model->save()){
                return [
                    'title'=> "จัดการข้อมูลจอแสดงผล",
                    'content'=>'<span class="text-success">บันทึกสำเร็จ!</span>',
                    'footer'=> Html::button('Close',['class'=>'btn btn-default','data-dismiss'=>"modal"]),
                    'status' => '200',
                    'url' => Url::to(['update-display', 'id' => $model->display_ids]),
                ];         
            }else{           
                return [
                    'title'=> "จัดการข้อมูลจอแสดงผล",
                    'content'=>$this->renderAjax('_form_display', [
                        'model' => $model,
                    ]),
                    'footer'=> '',
                    'status' => 'validate',
                    'validate' => ActiveForm::validate($model),
                ];         
            }
        }else{
            throw new MethodNotAllowedHttpException('method not allowed.'); 
        }
    }
}
