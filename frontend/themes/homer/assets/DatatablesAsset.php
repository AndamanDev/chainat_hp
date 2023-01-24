<?php
namespace homer\assets;

use yii\web\AssetBundle;

class DatatablesAsset extends AssetBundle
{
    public $sourcePath = '@homer/assets/datatables';

    public $css = [
        'custom-style.css',
        'dataTables.bootstrap.min.css',
        'ext/responsive-bs/css/responsive.bootstrap.min.css',
        'highlight/dataTables.searchHighlight.css'
    ];

    public $js = [
        'datatable.function.js',
        'jquery.dataTables.min.js',
        'dataTables.bootstrap.min.js',
        'ext/responsive/js/dataTables.responsive.min.js',
        'highlight/dataTables.searchHighlight.min.js',
        'highlight/jquery.highlight.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        // 'homer\assets\SweetAlert2Asset'
    ];
}