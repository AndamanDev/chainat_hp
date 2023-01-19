<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class Select2Asset extends AssetBundle
{
  public $sourcePath = '@bower/select2/dist';
    public $css = [
      'css/select2.min.css'
    ];
    public $js = [
        'js/select2.full.min.js',
        'js/i18n/th.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}