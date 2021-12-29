<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@homer' => '@frontend/themes/homer',
        '@homer/user' => '@frontend/themes/homer/modules/user',
        '@dektrium/user' => '@common/modules/yii2-user',
        // '@mdm/admin' => '@common/modules/yii2-admin',
        '@yii/icons' => '@homer/widgets/yii2-icons',
        '@homer/mpdf' => '@homer/extensions/yii2-mpdf',
        '@Mpdf' => '@common/lib/mpdf/src',
        '@homer/menu' => '@common/modules/yii2-menu',
        '@kartik/daterange' => '@homer/widgets/yii2-date-range',
        '@kartik/sortinput' => '@homer/widgets/yii2-sortable-input/src'
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    # ตั้งค่าการใช้งานภาษาไทย (Language)
    'language' => 'th', // ตั้งค่าภาษาไทย
    # ตั้งค่า TimeZone ประเทศไทย
    'timeZone' => 'Asia/Bangkok', // ตั้งค่า TimeZone
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableUnconfirmedLogin' => true,
            'confirmWithin' => 21600,
            'cost' => 12,
            'admins' => ['admin'],
        ],
        'rbac' => [
            'class' => 'mdm\admin\Module',
        ],
        'menu' => [
            'class' => 'homer\menu\Module',
        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager', // or use 'yii\rbac\DbManager'
        ],
        // 'cache' => [
        //     'class' => 'yii\redis\Cache',
        //     'redis' => 'redis'
        // ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@frontend/runtime/cache'
        ], 
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
            'dateFormat' => 'php:Y-m-d',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'timeFormat' => 'php:H:i:s',
            'defaultTimeZone' => 'Asia/Bangkok',
            'timeZone' => 'Asia/Bangkok'
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                ],
                'user*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@dektrium/user/messages',
                    //'sourceLanguage' => 'en-US',
                ],
            ],
        ],
    ],
    'params' => [
        'icon-framework' => 'fa',  // Font Awesome Icon framework
        'dtLanguage' => [
            "loadingRecords" => "กำลังดำเนินการ...",
            "zeroRecords" =>  "",
            "lengthMenu" =>  "แสดง _MENU_ แถว",
            "info" => "แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว",
            "infoEmpty" => "แสดง 0 ถึง 0 จาก 0 แถว",
            "infoFiltered" => "(กรองข้อมูล _MAX_ ทุกแถว)",
            "emptyTable" => "ไม่พบข้อมูล",
            "oPaginate" => [
                "sFirst" => "หน้าแรก",
                "sPrevious" => "ก่อนหน้า",
                "sNext" => "ถัดไป",
                "sLast" => "หน้าสุดท้าย"
            ],
        ],
    ],
];
