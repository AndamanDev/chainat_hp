<?php

namespace frontend\modules\app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\Html;
use homer\widgets\tbcolumn\ActionTable;
use homer\widgets\tbcolumn\ColumnTable;
use homer\widgets\tbcolumn\ColumnData;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
#models
use frontend\modules\app\models\TbDisplayConfig;
use frontend\modules\app\models\TbCaller;
use frontend\modules\app\traits\ModelTrait;
use frontend\modules\app\models\LabItems;
use frontend\modules\app\models\TbService;
use frontend\modules\app\models\TbSoundStation;
use yii\helpers\Url;

class DisplayController extends \yii\web\Controller
{
    use ModelTrait;

    public $layout = 'display';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionView($id)
    {
        $this->layout = 'display2';
        $config = $this->findModelDisplayConfig($id);
        $counter = $this->findModelCounterserviceType($config['counterservice_id']);
        $config->service_id = !empty($config['service_id']) ? explode(",", $config['service_id']) : [];
        $config->counterservice_id = !empty($config['counterservice_id']) ? explode(",", $config['counterservice_id']) : [];
        return $this->render('view', [
            'config' => $config,
            'counter' => $counter
        ]);
    }

    public function actionIndex($id)
    {
        $ids = \Yii::$app->keyStorage->get('display-lastq', '');
        $display_ids = explode(",", $ids);
        if (is_array($display_ids) && ArrayHelper::isIn($id, $display_ids)) {
            return $this->redirect(['view', 'id' => $id]);
        }
        $config = $this->findModelDisplayConfig($id);
        $counter = $this->findModelCounterserviceType($config['counterservice_id']);
        $config->service_id = !empty($config['service_id']) ? explode(",", $config['service_id']) : [];
        $config->counterservice_id = !empty($config['counterservice_id']) ? explode(",", $config['counterservice_id']) : [];

        $station = $config['sound_station_id'] ? TbSoundStation::findOne($config['sound_station_id']) : new TbSoundStation();
        return $this->render('index', [
            'config' => $config,
            'counter' => $counter,
            'station' => $station
        ]);
    }

    public function actionDisplayList()
    {
        $this->layout = '@homer/views/layouts/main';
        $display = TbDisplayConfig::find()->where(['display_status' => 1])->all();
        return $this->render('display-list', [
            'displays' => $display,
        ]);
    }

    public function actionDataDisplay($id)
    {
        $request = Yii::$app->request;
        $current_qid = $request->get('q_ids');
        $config = $this->findModelDisplayConfig($id);
        $counter = $this->findModelCounterserviceType($config['counterservice_id']);
        $config->service_id = !empty($config['service_id']) ? explode(",", $config['service_id']) : [];
        $config->counterservice_id = !empty($config['counterservice_id']) ? explode(",", $config['counterservice_id']) : [];

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            //$config = $request->post('config', []);
            $query = $this->findDisplayData($config,  $current_qid);

            $map = ArrayHelper::map($query, 'serviceid', 'service_prefix');
            $caller_ids = ArrayHelper::getColumn($query, 'caller_ids');
            $mapArr = [];
            foreach ($map as $m) {
                $rows = (new \yii\db\Query())
                    ->select([
                        'tb_quequ.q_num',
                        'tb_doctor.doctor_name',
                        'tb_quequ.pt_name',
                        'tb_quequ.pt_pic'
                    ])
                    ->from('tb_caller')
                    ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_caller.q_ids')
                    ->leftJoin('tb_doctor', 'tb_doctor.doc_id = tb_quequ.doctor_id')
                    ->where(['tb_caller.caller_ids' => $caller_ids, 'tb_caller.call_status' => 'calling'])
                    ->andWhere('tb_quequ.q_num LIKE :q_num')
                    ->andWhere('DATE( tb_quequ.q_timestp ) = CURRENT_DATE')
                    ->addParams([':q_num' => $m . '%'])
                    ->orderBy(['tb_caller.call_timestp' => SORT_ASC])
                    ->one();
                if ($rows) {
                    $mapArr[$m] = $rows['q_num'];
                } else {
                    $rows = (new \yii\db\Query())
                        ->select([
                            'tb_quequ.q_num',
                            'tb_doctor.doctor_name',
                            'tb_quequ.pt_name',
                            'tb_quequ.pt_pic'
                        ])
                        ->from('tb_caller')
                        ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_caller.q_ids')
                        ->leftJoin('tb_doctor', 'tb_doctor.doc_id = tb_quequ.doctor_id')
                        ->where(['tb_caller.caller_ids' => $caller_ids, 'tb_caller.call_status' => 'callend'])
                        ->andWhere('tb_quequ.q_num LIKE :q_num')
                        ->addParams([':q_num' => $m . '%'])
                        ->andWhere('DATE( tb_quequ.q_timestp ) = CURRENT_DATE')
                        ->orderBy(['tb_caller.call_timestp' => SORT_DESC])
                        ->one();
                    if ($rows) {
                        $mapArr[$m] = $rows['q_num'];
                    }
                }
            }

            $count = count($query);
            if ($count < $config['display_limit']) {
                $array = [];
                for ($i = $count; $i < $config['display_limit']; $i++) {
                    $random = Yii::$app->getSecurity()->generateRandomString(32);
                    $arr = [
                        'caller_ids' => $random,
                        'q_num' => '-',
                        'doctor_name' => '-',
                        'call_timestp' => date('Y-m-d H:i:s'),
                        'counterservice_callnumber' => '-',
                        'serviceid' => '',
                        'service_name' => '',
                        'service_prefix' => '',
                        'pt_name' => '-',
                        'pt_pic' => '-'
                    ];
                    $array[] = $arr;
                }
                $query = ArrayHelper::merge($query, $array);
            }

            $dataProvider = new ArrayDataProvider([
                'allModels' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'caller_ids'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::class,
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'q_num',
                        'value' => function ($model, $key, $index, $column) use ($config) {
                            if ($config['pt_name'] == 1 && $config['pt_pic'] == 1) {
                                return '
                                <table class="table" style="background-color: inherit;margin-bottom: 0px;">
                                    <tr style="border:0px;">
                                        <td rowspan="2" style="border-top:0px;vertical-align: middle; width:20%">
                                        ' . Html::beginTag('div', ['style' => 'margin: auto;']) .
                                    Html::img(empty($model['pt_pic']) ? Url::base(true) . '/img/admin.png' : $model['pt_pic'], ['width' => '140px']) .
                                    Html::endTag('div') . '
                                        </td>
    
                                        <td  style="border-top:0px; width: 60%">' . Html::tag('text', $model['q_num'], ['class' => trim($model['q_num'])]) . '</td>
                                        
                                        <td rowspan="2" style="border-top:0px; width: 20%;vertical-align: middle;">
                                        ' . Html::tag('text', $model['counterservice_callnumber'], ['class' => trim($model['q_num'])]) . '
                                        </td>
                                    </tr>
                                    <tr  style="border:0px;">
                                        <td style="border-top:0px; text-align:center;width:60%">
                                        ' . Html::tag('text', $model['pt_name'], ['class' => trim($model['q_num'])]) . '
                                        </td>
                                    </tr>
                                </table>
                                ';
                            } else if ($config['pt_name'] == 1 && $config['pt_pic'] == 0) {
                                return '
                                <table class="table" style="background-color: inherit;margin-bottom: 0px;">
                                    <tr style="border:0px;">
    
                                        <td  style="border-top:0px; width: 80%">' . Html::tag('text', $model['q_num'], ['class' => trim($model['q_num'])]) . '</td>
                                        
                                        <td rowspan="2" style="border-top:0px; width: 20%;vertical-align: middle;">
                                        ' . Html::tag('text', $model['counterservice_callnumber'], ['class' => trim($model['q_num'])]) . '
                                        </td>
                                    </tr>
                                    <tr  style="border:0px;">
                                        <td style="border-top:0px; text-align:center;width:80%">
                                        ' . Html::tag('text', $model['pt_name'], ['class' => trim($model['q_num'])]) . '
                                        </td>
                                    </tr>
                                </table>
                                ';
                            } else if ($config['pt_name'] == 0 && $config['pt_pic'] == 1) {

                                return '
                                <table class="table" style="background-color: inherit;margin-bottom: 0px;">
                                    <tr style="border:0px;">
                                        <td style="border-top:0px;vertical-align: middle; width:20%">
                                        ' . Html::beginTag('div', ['style' => 'margin: auto;']) .
                                    Html::img(empty($model['pt_pic']) ? Url::base(true) . '/img/admin.png' : $model['pt_pic'], ['width' => '140px']) .
                                    Html::endTag('div') . '
                                        </td>
    
                                        <td  style="border-top:0px; width: 40%;text-align:left;vertical-align: middle;">' . Html::tag('text', $model['q_num'], ['class' => trim($model['q_num'])]) . '</td>
                                        
                                        <td style="border-top:0px; width: 40%;vertical-align: middle;">
                                        ' . Html::tag('text', $model['counterservice_callnumber'], ['class' => trim($model['q_num'])]) . '
                                        </td>
                                    </tr>
                                    
                                </table>
                                ';
                            } else if ($config['pt_name'] == 0 && $config['pt_pic'] == 0) {
                                return '
                                <table class="table" style="background-color: inherit;margin-bottom: 0px;">
                                    <tr style="border:0px;">
                                        <td  style="border-top:0px; width: 50%;vertical-align: middle;">' . Html::tag('text', $model['q_num'], ['class' => trim($model['q_num'])]) . '</td>
                                        
                                        <td style="border-top:0px; width: 50%;vertical-align: middle;">
                                        ' . Html::tag('text', $model['counterservice_callnumber'], ['class' => trim($model['q_num'])]) . '
                                        </td>
                                    </tr>
                                    
                                </table>
                                ';
                            }else{
                                return '
                                <table class="table" style="background-color: inherit;margin-bottom: 0px;">
                                    <tr style="border:0px;">
                                        <td  style="border-top:0px; width: 50%;vertical-align: middle;">' . Html::tag('text', $model['q_num'], ['class' => trim($model['q_num'])]) . '</td>
                                        
                                        <td style="border-top:0px; width: 50%;vertical-align: middle;">
                                        ' . Html::tag('text', $model['counterservice_callnumber'], ['class' => trim($model['counterservice_callnumber'])]) . '
                                        </td>
                                    </tr>
                                    
                                </table>
                                ';
                            }

                            //     return '<table class="table" style="background-color: inherit;margin-bottom: 0px;">
                            //     <tr style="border:0px;">
                            //         <td rowspan="2" style="border-top:0px;vertical-align: middle;">
                            //             '.Html::beginTag('div' ,['style' => 'margin: auto;']) .
                            //         Html::img(empty($model['pt_pic']) ? Url::base(true).'/img/admin.png' : $model['pt_pic'],['width' => '140px']) .
                            //         Html::endTag('div').'
                            //         </td>
                            //         <td style="border-top:0px; width: 40%">
                            //             '.Html::tag('text', $model['q_num'], ['class' => trim($model['q_num'])]).'
                            //         </td>
                            //         <td style="border-top:0px; width: 40%">
                            //            '.Html::tag('text', $model['counterservice_callnumber'], ['class' => trim($model['counterservice_callnumber'])]).'
                            //         </td>
                            //     </tr>
                            //     <tr style="border:0px;">
                            //         <td colspan="2" style="border-top:0px; text-align:center">
                            //             '.Html::tag('text', $model['pt_name'], []) .'
                            //         </td>
                            //     </tr>
                            // </table>';

                            // Html::beginTag('div', ['class' => '', 'style' => 'display: flex;justify-content: space-between;']) .
                            //     Html::beginTag('div') .
                            //      .
                            //     .
                            //     Html::endTag('div') .
                            //     Html::endTag('div');
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'service_number',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::tag('text', $model['counterservice_callnumber'], ['class' => trim($model['q_num'])]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'call_timestp',
                    ],
                    [
                        'attribute' => 'doctor_name',
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
                        'attribute' => 'pt_name',
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'pt_pic',
                    ],
                ]
            ]);

            return ['data' => $columns->renderDataColumns(), 'mapArr' => $mapArr, 'query' => $query];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataDisplayHold()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $config = $request->post('config', []);
            $query = (new \yii\db\Query())
                ->select([
                    'tb_caller.caller_ids',
                    'GROUP_CONCAT(tb_quequ.q_num) AS q_num'
                ])
                ->from('tb_caller')
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_caller.q_ids')
                ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                ->innerJoin('tb_counterservice_type', 'tb_counterservice_type.counterservice_typeid = tb_counterservice.counterservice_type')
                ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
                ->where([
                    'tb_caller.call_status' => 'hold',
                    'tb_counterservice_type.counterservice_typeid' => $config['counterservice_id'],
                    'tb_service.serviceid' => $config['service_id']
                ])
                ->andWhere('DATE( tb_quequ.q_timestp ) = CURRENT_DATE')
                ->orderBy('tb_caller.call_timestp DESC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'caller_ids'
            ]);

            if ($dataProvider->getTotalCount() <= 0) {
                return ['data' => [['q_num' => '-']]];
            } else {
                $columns = Yii::createObject([
                    'class' => ColumnData::class,
                    'dataProvider' => $dataProvider,
                    'formatter' => Yii::$app->getFormatter(),
                    'columns' => [
                        [
                            'attribute' => 'q_num',
                            'value' => function ($model, $key, $index, $column) {
                                return Html::tag('marquee', $model['q_num'], ['direction' => 'left']);
                            },
                            'format' => 'raw'
                        ],
                    ]
                ]);

                return ['data' => $columns->renderDataColumns()];
            }
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionLab($id)
    {
        $model = $this->findModelDisplayConfig($id);
        return $this->render('display-lab', ['config' => $model]);
    }

    public function actionDataDisplaylab($id)
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $session = Yii::$app->session;
            $display = $this->findModelDisplayConfig($id);
            $services = !empty($display['service_id']) ? explode(",", $display['service_id']) : [];
            $modelLab = LabItems::find()->where(['confirm' => 'Y'])->all();
            $labs = ArrayHelper::getColumn($modelLab, 'lab_items_code');
            $params = [':vstdate' => date('Y-m-d'), ':confirm' => 'N'];
            $sql = 'SELECT
                    `lab_order`.`lab_order_number` AS lab_order_number,
                    `vn_stat`.`vn` AS `vn`,
                    `vn_stat`.`hn` AS `hn`,
                    `vn_stat`.`vstdate` AS `vstdate`,
                    concat( `patient`.`pname`, \' \', `patient`.`fname`, \' \', `patient`.`lname` ) AS `pt_name`,
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
                    ( `vn_stat`.`vstdate` = :vstdate AND `lab_order`.`confirm` = :confirm AND `lab_items`.`lab_items_code` IN (' . implode(",", $labs) . '))
                GROUP BY
                    `lab_order`.`lab_order_number`
                ORDER BY
                    `lab_order`.`lab_order_number`, `patient`.`fname`';
            $allModels = Yii::$app->db_his->createCommand($sql)->bindValues($params)->queryAll();
            $rows = (new \yii\db\Query())
                ->select(['q_ids', 'q_num', 'q_hn'])
                ->from('tb_quequ')
                ->where(['serviceid' => $services])
                ->andWhere(['not', ['q_hn' => null]])
                ->all();
            $items = [];
            $i = 0;
            if (count($allModels) > 0) {
                $qdata = ArrayHelper::map($rows, 'q_hn', 'q_num');
                foreach ($allModels as $key => $value) {
                    $qnum = ArrayHelper::getValue($qdata, $value['hn'], '');
                    if ($qnum == '') {
                        continue;
                    } else {
                        $i++;
                    }
                    $items[] = ArrayHelper::merge($value, [
                        'qnum' => $qnum,
                        'map' => $qdata
                    ]);
                    if ($i == 5) {
                        $session->set('lab_order_number', $value['lab_order_number']);
                        break;
                    }
                }
            }
            $dataProvider = new ArrayDataProvider([
                'allModels' => $items,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'lab_order_number'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::class,
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'vn',
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'pt_name',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) {
                            $str = $model['qnum'] . ' - ' . $model['pt_name'];
                            return mb_substr($str, 0, 30) . '...';
                        }
                    ],
                    [
                        'attribute' => 'lab_order_number',
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'qnum',
                        'format' => 'raw',
                    ],
                ]
            ]);

            return ['data' => $columns->renderDataColumns(), 'lab_order_number' => $session->get('lab_order_number')];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataDisplaylab2($id)
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $session = Yii::$app->session;
            $lab_order_number = isset($_SESSION['lab_order_number']) ? $_SESSION['lab_order_number'] : null;
            $params = [':vstdate' => date('Y-m-d'), ':confirm' => 'N', ':lab_order_number' => $lab_order_number];
            $modelLab = LabItems::find()->where(['confirm' => 'Y'])->all();
            $labs = ArrayHelper::getColumn($modelLab, 'lab_items_code');
            $display = $this->findModelDisplayConfig($id);
            $services = !empty($display['service_id']) ? explode(",", $display['service_id']) : [];
            $sql = 'SELECT
                    `lab_order`.`lab_order_number` AS lab_order_number,
                    `vn_stat`.`vn` AS `vn`,
                    `vn_stat`.`hn` AS `hn`,
                    `vn_stat`.`vstdate` AS `vstdate`,
                    concat( `patient`.`pname`, \' \', `patient`.`fname`, \' \', `patient`.`lname` ) AS `pt_name`,
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
                    ( `vn_stat`.`vstdate` = :vstdate AND `lab_order`.`confirm` = :confirm AND `lab_order`.`lab_order_number` > :lab_order_number AND `lab_items`.`lab_items_code` IN (' . implode(",", $labs) . '))
                GROUP BY
                    `lab_order`.`lab_order_number`
                ORDER BY
                    `lab_order`.`lab_order_number`,`patient`.`fname`';
            $allModels = Yii::$app->db_his->createCommand($sql)->bindValues($params)->queryAll();
            $rows = (new \yii\db\Query())
                ->select(['q_ids', 'q_num', 'q_hn'])
                ->from('tb_quequ')
                ->where(['serviceid' => $services])
                ->andWhere(['not', ['q_hn' => null]])
                ->all();
            $items = [];
            $i = 0;
            if (count($allModels) > 0) {
                $qdata = ArrayHelper::map($rows, 'q_hn', 'q_num');
                foreach ($allModels as $key => $value) {
                    $qnum = ArrayHelper::getValue($qdata, $value['hn'], '');
                    if ($qnum == '') {
                        continue;
                    } else {
                        $i++;
                    }
                    $items[] = ArrayHelper::merge($value, [
                        'qnum' => $qnum,
                        'map' => $qdata
                    ]);
                    if ($i == 5) {
                        break;
                    }
                }
            }

            $dataProvider = new ArrayDataProvider([
                'allModels' => $items,
                'pagination' => [
                    'pageSize' => false,
                ],
                'key' => 'lab_order_number'
            ]);
            $columns = Yii::createObject([
                'class' => ColumnData::class,
                'dataProvider' => $dataProvider,
                'formatter' => Yii::$app->getFormatter(),
                'columns' => [
                    [
                        'attribute' => 'vn',
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'pt_name',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) {
                            $str = $model['qnum'] . ' - ' . $model['pt_name'];
                            return mb_substr($str, 0, 30) . '...';
                        }
                    ],
                    [
                        'attribute' => 'lab_order_number',
                        'format' => 'raw',
                    ],
                ]
            ]);

            return ['data' => $columns->renderDataColumns(), 'lab_order_number' => $session->get('lab_order_number')];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDataDisplay2()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $config = $request->post('config', []);
            $query = $this->findDisplayData($config);
            $model = TbDisplayConfig::findOne($config['display_ids']);
            $services = !empty($model['service_id']) ? explode(",", $model['service_id']) : [];
            $counters = !empty($model['counterservice_id']) ? explode(",", $model['counterservice_id']) : [];
            $servicePrefixs = ArrayHelper::map(TbService::find()->where(['serviceid' => $services])->orderBy(['service_prefix' => SORT_ASC])->all(), 'serviceid', 'service_prefix');

            $map = ArrayHelper::map($query, 'serviceid', 'service_prefix');
            $caller_ids = ArrayHelper::getColumn($query, 'caller_ids');
            $mapArr = [];
            foreach ($servicePrefixs as $prefix) {
                $rows = (new \yii\db\Query())
                    ->select(['tb_quequ.q_num', 'tb_quequ.quickly'])
                    ->from('tb_caller')
                    ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_caller.q_ids')
                    ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                    ->where([
                        'tb_quequ.serviceid' => $services,
                        //'tb_caller.call_status' => ['calling','callend','hold'],
                        'tb_counterservice.counterservice_type' => $counters
                    ])
                    //->where(['tb_caller.caller_ids' => $caller_ids,'tb_caller.call_status' => 'calling'])
                    ->andWhere('tb_quequ.q_num LIKE :q_num')
                    ->andWhere('DATE( tb_quequ.q_timestp ) = CURRENT_DATE')
                    ->addParams([':q_num' => $prefix . '%'])
                    ->orderBy(['tb_caller.call_timestp' => SORT_DESC])
                    ->groupBy('tb_quequ.q_ids')
                    ->one();
                if ($rows) {
                    $mapArr[] = [
                        'prefix' => $prefix,
                        'qnum' => $rows['q_num'],
                        'DT_RowAttr' => ['data-key' => $prefix],
                        'DT_RowId' => $prefix,
                    ];
                    if ($rows['quickly'] == 1) {
                        $mapArr[] = [
                            'prefix' => '<small>คิวด่วน</small>',
                            'qnum' => $rows['q_num'],
                            'DT_RowAttr' => ['data-key' => $prefix],
                            'DT_RowId' => $prefix,
                        ];
                    }
                } else {
                    $mapArr[] = [
                        'prefix' => $prefix,
                        'qnum' => '-',
                        'DT_RowAttr' => ['data-key' => $prefix],
                        'DT_RowId' => $prefix,
                    ];
                }
            }
            return ['data' => $mapArr];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }

    protected function findDisplayData($config,  $current_qid = null)
    {
        $lastcalling = $this->lastCalling($config);
        $query = (new \yii\db\Query())
            ->select([
                'tb_caller.caller_ids',
                'tb_quequ.q_num',
                'tb_doctor.doctor_name',
                'tb_caller.call_timestp',
                'tb_counterservice.counterservice_callnumber',
                'tb_service.serviceid',
                'tb_service.service_name',
                'tb_service.service_prefix',
                'tb_quequ.pt_name',
                'tb_quequ.pt_pic'
            ])
            ->from('tb_caller')
            ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_caller.q_ids')
            ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
            ->innerJoin('tb_counterservice_type', 'tb_counterservice_type.counterservice_typeid = tb_counterservice.counterservice_type')
            ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
            ->leftJoin('tb_doctor', 'tb_doctor.doc_id = tb_quequ.doctor_id')
            ->where([
                'tb_caller.call_status' => ['calling', 'callend'],
                'tb_counterservice_type.counterservice_typeid' => $config['counterservice_id'],
                'tb_service.serviceid' => $config['service_id']
            ])
            ->andWhere('DATE( tb_quequ.q_timestp ) = CURRENT_DATE')
            ->limit(empty($config['display_limit']) ? 2 : $config['display_limit'])
            ->orderBy([
                'tb_caller.call_timestp' => SORT_DESC
            ]);
        if ($lastcalling) {
            $query->andWhere('tb_caller.call_timestp <= :call_timestp', [':call_timestp' => $lastcalling['call_timestp']]);
        }
        if($current_qid){
            $query->andWhere('tb_quequ.q_ids <= :q_ids', [':q_ids' => $current_qid]);
        }
        return $query->all();
    }

    protected function lastCalling($config)
    {
        $rows = (new \yii\db\Query())
            ->select([
                'tb_caller.caller_ids',
                'tb_quequ.q_num',
                'tb_caller.call_timestp',
                'tb_counterservice.counterservice_callnumber',
                'tb_service.serviceid',
                'tb_service.service_name',
                'tb_service.service_prefix',
                'tb_quequ.pt_name',
                'tb_quequ.pt_pic'
            ])
            ->from('tb_caller')
            ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_caller.q_ids')
            ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
            ->innerJoin('tb_counterservice_type', 'tb_counterservice_type.counterservice_typeid = tb_counterservice.counterservice_type')
            ->leftJoin('tb_service', 'tb_service.serviceid = tb_quequ.serviceid')
            ->where([
                'tb_caller.call_status' => ['calling'],
                'tb_counterservice_type.counterservice_typeid' => $config['counterservice_id'],
                'tb_service.serviceid' => $config['service_id']
            ])
            ->andWhere('DATE( tb_quequ.q_timestp ) = CURRENT_DATE')
            ->orderBy([
                'tb_caller.call_timestp' => SORT_ASC
            ])
            ->one();
        return $rows;
    }

    public function actionDataDisplayLastq()
    {
        $request = Yii::$app->request;

        if ($request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $config = $request->post('config', []);
            $query = $this->findDisplayData($config);
            $model = TbDisplayConfig::findOne($config['display_ids']);
            $services = !empty($model['service_id']) ? explode(",", $model['service_id']) : [];
            $counters = !empty($model['counterservice_id']) ? explode(",", $model['counterservice_id']) : [];
            $servicePrefixs = ArrayHelper::map(TbService::find()->where(['serviceid' => $services])->orderBy(['service_prefix' => SORT_ASC])->all(), 'serviceid', 'service_prefix');

            $map = ArrayHelper::map($query, 'serviceid', 'service_prefix');
            $caller_ids = ArrayHelper::getColumn($query, 'caller_ids');
            $mapArr = [];
            foreach ($servicePrefixs as $prefix) {
                $rows = (new \yii\db\Query())
                    ->select(['tb_quequ.q_num', 'tb_quequ.quickly'])
                    ->from('tb_caller')
                    ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_caller.q_ids')
                    ->innerJoin('tb_counterservice', 'tb_counterservice.counterserviceid = tb_caller.counter_service_id')
                    ->where([
                        'tb_quequ.serviceid' => $services,
                        'tb_counterservice.counterservice_type' => $counters
                    ])
                    ->andWhere('tb_quequ.q_num LIKE :q_num')
                    ->andWhere('DATE( tb_quequ.q_timestp ) = CURRENT_DATE')
                    ->addParams([':q_num' => $prefix . '%'])
                    ->orderBy(['tb_caller.call_timestp' => SORT_DESC])
                    ->one();
                if ($rows) {
                    $mapArr[] = [
                        'prefix' => $prefix,
                        'qnum' => $rows['q_num'],
                        'DT_RowAttr' => ['data-key' => $prefix],
                        'DT_RowId' => $prefix,
                    ];
                }
            }
            $columns = [];
            $display_limit = !empty($config['display_limit']) ? $config['display_limit'] : 1;
            $i = ($display_limit * 3);
            if ($mapArr) {
                $arr = [];
                $length = count($mapArr);
                $x = 1;
                foreach ($mapArr as $index => $data) {
                    $arr[] = Html::button($data['qnum'], ['class' => 'width-fixed']);
                    if (($x++) == $length && count($arr) < 3) {
                        for ($y = count($arr); $y <= 3; $y++) {
                            $arr[] = Html::button('-', ['class' => 'width-fixed']);
                            if (count($arr) == 3) {
                                $columns[] = ['qnum' => implode(" ", $arr)];
                                $arr = [];
                            }
                        }
                    }
                    if (count($arr) == 3) {
                        $columns[] = ['qnum' => implode(" ", $arr)];
                        $arr = [];
                    }
                }
                /* for($x = 0; $x <= $i; $x++){
                    if(isset($mapArr[$x]) && count($arr) < 3){
                        if($mapArr[$x]['qnum'] != '-'){
                            $arr[] = Html::button($mapArr[$x]['qnum'],['class' => 'width-fixed']);
                        }
                    }elseif(count($arr) == 3){
                        $columns[] = ['qnum' => implode(" ", $arr), 'x' => $x];
                        $arr = [];
                    }else{
                        $arr[] = Html::button('-',['class' => 'width-fixed']);
                    }
                } */
            }
            if (count($columns) < $display_limit) {
                $arr = [];
                for ($x = count($columns); $x <= $display_limit; $x++) {
                    for ($y = 0; $y <= 3; $y++) {
                        if (count($arr) == 3) {
                            $columns[] = ['qnum' => implode(" ", $arr)];
                            $arr = [];
                        } else {
                            $arr[] = Html::button('-', ['class' => 'width-fixed']);
                        }
                    }
                }
            }
            return ['data' => $columns, 'mapArr' => $mapArr];
        } else {
            throw new NotFoundHttpException('Invalid request. Please do not repeat this request again.');
        }
    }
}
