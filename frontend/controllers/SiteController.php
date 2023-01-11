<?php

namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\modules\app\models\TbQuequ;
use frontend\modules\app\models\TbService;
use frontend\modules\app\models\TbQtrans;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use kartik\helpers\Enum;
use yii\helpers\Json;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup', 'index', 'about', 'login', 'contact'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'about', 'login', 'contact'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'homer\actions\ErrorAction',
                'layout' => '@homer/views/layouts/main-error',
                'view' => '@homer/views/error'
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'glide' => 'trntv\glide\actions\GlideAction'
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */

    public function actionDashboard()
    {
        $data = [];
        $services = TbService::find()->all();
        $seriesall = [];
        $serieswait = [];
        $series2 = [];
        $subseries2 = [];
        $y = 5;
        $day = date('Y-m-d');
        $allq = TbQuequ::find()->andWhere('DATE(tb_quequ.q_timestp) = CURRENT_DATE')->count();
        //$allq = TbQuequ::find()->andWhere(['between', 'created_at',$day.' 00:00:00', $day.' 23:59:59'])->count();
        for ($x = 1; $x <= 17; $x++) {
            $d1 = new \DateTime($day . ' ' . $y . ':00:00');
            $d1->modify('+1 hour');
            $h1 = $d1->format('H:i:s') . PHP_EOL;

            $d2 = new \DateTime($day . ' ' . $y . ':00:00');
            $d2->modify('+2 hour');
            $h2 = $d2->format('H:i:s') . PHP_EOL;

            $y++;

            $start = $day . ' ' . $h1;
            $end = $day . ' ' . $h2;
            $count = TbQuequ::find()->where(['between', 'created_at', $start, $end])->count();
            $t = substr($h1, 0, 5) . '-' . substr($h2, 0, 5);
            $series2[] = [
                "name" => $t,
                "y" => intval($count),
                "drilldown" => $t
            ];

            $drilldown = [];
            foreach ($services as $service) {
                $count = TbQuequ::find()->where(['between', 'created_at', $start, $end])->andWhere(['serviceid' => $service['serviceid']])->count();
                $drilldown[] = [
                    $service['service_name'], intval($count)
                ];
            }
            $subseries2[] = [
                'name' => $t,
                'id' => $t,
                'data' => $drilldown
            ];

            unset($drilldown);
        }
        $allwait = 0;
        foreach ($services as $service) {
            $count = TbQuequ::find()
                ->where([
                    'tb_quequ.serviceid' => $service['serviceid'],
                ])
                ->andWhere('DATE(tb_quequ.q_timestp) = CURRENT_DATE')
                //->andWhere(['between', 'created_at',$day.' 00:00:00', $day.' 23:59:59'])
                ->count();
            $wait = TbQtrans::find()
                ->where([
                    'tb_quequ.serviceid' => $service['serviceid'],
                    'tb_qtrans.service_status_id' => [1],
                ])
                ->andWhere('DATE(tb_quequ.q_timestp) = CURRENT_DATE')
                //->andWhere(['between', 'created_at',$day.' 00:00:00', $day.' 23:59:59'])
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->count();
            $waitEx = TbQtrans::find()
                ->where([
                    'tb_quequ.serviceid' => $service['serviceid'],
                    'tb_qtrans.service_status_id' => [4],
                ])
                ->andWhere('DATE(tb_quequ.q_timestp) = CURRENT_DATE')
                //->andWhere(['between', 'created_at',$day.' 00:00:00', $day.' 23:59:59'])
                ->andWhere(['not', ['counter_service_id' => null]])
                ->innerJoin('tb_quequ', 'tb_quequ.q_ids = tb_qtrans.q_ids')
                ->count();
            $wait = $wait + $waitEx;
            $arr = [
                'service_name' => $service['service_name'],
                'count' => $count,
                'wait' => $wait,
            ];
            $allwait = ($allwait + $wait);
            $data[] = $arr;
            $seriesall = ArrayHelper::merge($seriesall, [intval($count)]);
            $serieswait = ArrayHelper::merge($serieswait, [intval($wait)]);
        }
        $all = [
            [
                'service_name' => 'จำนวนคิวทั้งหมด',
                'count' => $allq,
                'wait' => $allwait,
            ]
        ];
        $seriesall = [['name' => 'คิวทั้งหมด', 'data' => $seriesall, 'color' => '#3498db']];
        $serieswait = [['name' => 'คิวรอ', 'data' => $serieswait, 'color' => '#e74c3c']];
        //return Json::encode(ArrayHelper::merge($seriesall, $serieswait));
        return $this->render('dashboard', [
            'data' => ArrayHelper::merge($all, $data),
            'categories' => ArrayHelper::getColumn($services, 'service_name'),
            'series' => ArrayHelper::merge($seriesall, $serieswait),
            'series2' => $series2,
            'subseries2' => $subseries2,
        ]);
    }

    public function actionIndex() {
      return $this->render('index');
    }
    /*
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $data = [];
        $series = [];
        $services = TbService::find()->all();
        if($request->isGet){
            $allq = TbQuequ::find()->count();
            
            foreach($services as $service){
                $arr = [
                    'service_name' => $service['service_name'],
                    'count' => TbQuequ::find()
                                ->where(['serviceid' => $service['serviceid']])
                                ->count(),
                ];
                $data[] = $arr;
            }
            $all = [
                [
                    'service_name' => 'จำนวนคิวทั้งหมด',
                    'count' => $allq
                ]
            ];
        }else{
            $start = $request->post('from_date');
            $end = $request->post('to_date');
            $allq = TbQuequ::find()->andWhere(['between', 'created_at', $start, $end])->count();
            
            foreach($services as $service){
                $count = TbQuequ::find()
                        ->where(['serviceid' => $service['serviceid']])
                        ->andWhere(['between', 'created_at', $start, $end])
                        ->count();
                $arr = [
                    'service_name' => $service['service_name'],
                    'count' => $count,
                ];
                $data[] = $arr;
            }
            $all = [
                [
                    'service_name' => 'จำนวนคิวทั้งหมด',
                    'count' => $allq
                ]
            ];
        }
        foreach($services as $service){
            $series[] = [
                'name' => $service['service_name'],
                'data' => $this->countAllMonth($service['serviceid']),
            ];
        }
        return $this->render('index',[
            'data' => ArrayHelper::merge($all, $data),
            'series' => $series,
        ]);
    }*/

    protected function countAllMonth($serviceid)
    {
        $items = [];
        foreach (Enum::monthList() as $m) {
            $start = date('Y-m-d', strtotime('first day of ' . $m . ' this year'));
            $end = date('Y-m-d', strtotime('last day of ' . $m . ' this year'));
            $count = TbQuequ::find()
                ->where(['serviceid' => $serviceid])
                ->andWhere(['between', 'created_at', $start, $end])
                ->count();
            $items = ArrayHelper::merge($items, [intval($count)]);
        }
        return $items;
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionClearCache()
    {
        $frontendAssetPath = Yii::getAlias('@frontend') . '/web/assets/';
        $backendAssetPath = Yii::getAlias('@backend') . '/web/assets/';

        $this->recursiveDelete($frontendAssetPath);
        $this->recursiveDelete($backendAssetPath);

        if (Yii::$app->cache->flush()) {
            Yii::$app->session->setFlash('crudMessage', 'Cache has been flushed.');
        } else {
            Yii::$app->session->setFlash('crudMessage', 'Failed to flush cache.');
        }

        return Yii::$app->getResponse()->redirect(Yii::$app->getRequest()->referrer);
    }

    public static function recursiveDelete($path)
    {
        if (is_file($path)) {
            return @unlink($path);
        } elseif (is_dir($path)) {
            $scan = glob(rtrim($path, '/') . '/*');
            foreach ($scan as $index => $newPath) {
                self::recursiveDelete($newPath);
            }
            return @rmdir($path);
        }
    }

    public function actionPrint($size)
    {
        return $this->renderAjax('print', ['size' => $size]);
    }
}
