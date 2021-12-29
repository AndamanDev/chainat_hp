<?php

use \yii\web\Request;

$baseUrl = str_replace('/frontend/web', '', (new Request)->getBaseUrl());
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'dektrium\user\Bootstrap'],
    'controllerNamespace' => 'frontend\controllers',
    'name' => 'ระบบคิว รพ. ชัยนาทนเรนทร',
    'defaultRoute' => '/app/display/display-list',
    'controllerMap' => [
        'file-manager-elfinder' => [
            'class' => mihaildev\elfinder\Controller::class,
            'access' => ['@'],
            'disabledCommands' => ['netmount'],
            'roots' => [
                [
                    'baseUrl' => '@web',
                    'basePath' => '@webroot',
                    'path' => '/',
                    'access' => ['read' => 'Admin', 'write' => 'Admin'],
                    'options' => [
                        'attributes' => [
                            [
                                'pattern' => '!^/assets!',
                                'hidden' => true
                            ]
                        ],
                    ]
                ]
            ]
        ]
    ],
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableUnconfirmedLogin' => true,
            'confirmWithin' => 21600,
            'cost' => 12,
            'enableRegistration' => false,
            'admins' => ['Admin'],
            'modelMap' => [
                'User' => 'homer\user\models\User',
                'Profile' => 'homer\user\models\Profile',
                'RegistrationForm' => 'homer\user\models\RegistrationForm'
            ],
            'controllerMap' => [
                'registration' => [
                    'class' => 'homer\user\controllers\RegistrationController',
                    'layout' => '@homer/views/layouts/main-login',
                ],
                'recovery' => [
                    'class' => 'homer\user\controllers\RecoveryController',
                    'layout' => '@homer/views/layouts/main-login',
                ],
                'admin' => [
                    'class' => 'homer\user\controllers\AdminController',
                ],
                'security' => [
                    'class' => 'homer\user\controllers\SecurityController',
                    'layout' => '@homer/views/layouts/main-login',
                ],
                'profile' => [
                    'class' => 'homer\user\controllers\ProfileController',
                ],
                'settings' => [
                    'class' => 'homer\user\controllers\SettingsController',
                ],
            ],
        ],
        'admin-manager' => [
            'class' => 'mdm\admin\Module',
            'layout' => 'top-menu',
            'menus' => [
                'assignment' => [
                    'label' => 'การกำหนด'
                ],
                'role' => [
                    'label' => 'บทบาท'
                ],
                'permission' => [
                    'label' => 'สิทธิ์'
                ],
                'route' => [
                    'label' => 'เส้นทาง'
                ],
                'rule' => null,
                'menu' => null,
                'user' => null,
            ],
            'controllerMap' => [
                'assignment' => [
                    'class' => 'mdm\admin\controllers\AssignmentController',
                    /* 'userClassName' => 'app\models\User', */
                    'idField' => 'user_id',
                    'usernameField' => 'username',
                    'extraColumns' => [
                        [
                            'label' => 'ชื่อ',
                            'value' => function ($model, $key, $index, $column) {
                                return $model->profile->name;
                            },
                        ],
                        [
                            'label' => 'สิทธิ์การใช้งาน',
                            'value' => function ($model, $key, $index, $column) {
                                return $model->profile->permissions;
                            },
                        ],
                    ],
                ],
            ],
        ],
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
        ],
        'kiosk' => [
            'class' => 'frontend\modules\kiosk\Module',
        ],
        'settings' => [
            'class' => 'frontend\modules\settings\Module',
        ],
        'app' => [
            'class' => 'frontend\modules\app\Module',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'baseUrl' => '',
        ],
        'user' => [
            'identityClass' => 'homer\user\models\User',
            'identityCookie' => [
                'name'     => '_frontendIdentity',
                'path'     => '/',
                'httpOnly' => true,
            ],
        ],
        'session' => [
            'name' => 'FRONTENDSESSID',
            'cookieParams' => [
                'httpOnly' => true,
                'path'     => '/',
            ],
            'timeout' => 3600 * 8,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'dashboard' => 'site/index',
                'displaylist' => 'app/display/data-display',
            ],
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@homer/views',
                    '@dektrium/user/views' => '@homer/user/views'
                ],
            ],
        ],
        'glide' => [
            'class' => 'trntv\glide\components\Glide',
            'sourcePath' => '@app/web/uploads',
            'cachePath' => '@runtime/glide',
            'signKey' => false
        ],
        'fileStorage' => [
            'class' => 'trntv\filekit\Storage',
            'baseUrl' => '@web/uploads',
            'filesystem' => [
                'class' => 'common\components\filesystem\LocalFlysystemBuilder',
                'path' => '@webroot/uploads'
            ],
            'as log' => [
                'class' => 'common\behaviors\FileStorageLogBehavior',
                'component' => 'fileStorage'
            ]
        ],
        'keyStorage' => [
            'class' => 'common\components\keyStorage\KeyStorage'
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@frontend/runtime/cache'
        ],
        // 'cache' => [
        //     'class' => 'yii\redis\Cache',
        //     'redis' => 'redis'
        // ],
        'assetManager' => [
            'appendTimestamp' => true,
            /* 'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'depends' => [                  
                        'yii\jui\JuiAsset',
                    ],
                ],
            ], */
        ],
        'soapClient' => [
            'class' => 'mcomscience\soapclient\Client',
            'url' => 'http://ucws.nhso.go.th/ucwstokenp1/UCWSTokenP1?WSDL',
            'options' => [
                'cache_wsdl' => WSDL_CACHE_NONE,
            ],
        ],
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'user/registration/*',
            'user/security/*',
            'user/recovery/*',
            'debug/*',
            'mobile-view/*',
            'app/display/*',
            'app/calling/play-sound',
            'app/calling/autoload-media',
            'app/calling/update-status',
            'site/index',
            'app/kiosk/led-options',
            'app/settings/save-nhso-token',
            'app/kiosk/pt-right',
            'app/kiosk/create-queue',
            'app/calling/calling-queue',
            'app/calling/hold-queue',
            'app/calling/end-queue',
            'app/calling/send-to-doctor',
            'app/calling/waiting-doctor-queue',
            'app/calling/waiting-pharmacy-queue',
            'app/calling/next-queue',
            'app/calling/queue-by-vn-hn',
            'app/drug-dispensing/create-drug-dispensing',
            'app/kiosk/scan-queue-mobile-qn',
            'app/kiosk/scan-queue-mobile-hn',
            'app/kiosk/queue-list',
            'app/kiosk/print-ticket',
            'app/drug-dispensing/drug-dispensing-list',
        ]
    ],
    'params' => $params,
];
