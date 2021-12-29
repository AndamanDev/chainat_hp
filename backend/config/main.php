<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableUnconfirmedLogin' => true,
            'confirmWithin' => 21600,
            'cost' => 12,
            'admins' => ['admin'],
            'modelMap' => [
                'User' => 'homer\user\models\User',
                'Profile' => 'homer\user\models\Profile',
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
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'csrfCookie' => [
                'httpOnly' => true,
                'path' => '/admin',
            ],
        ],
        'user' => [
            'identityClass' => 'homer\user\models\User',
            'identityCookie' => [
                'name'     => '_backendIdentity',
                'path'     => '/admin',
                'httpOnly' => true,
            ],
        ],
        'session' => [
            'name' => 'BACKENDSESSID',
            'cookieParams' => [
                'httpOnly' => true,
                'path'     => '/admin',
            ],
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
            ],
        ],
    ],
    'params' => $params,
];
