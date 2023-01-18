<?php
namespace homer\widgets;

use yii\base\Widget;
use homer\assets\DatatablesAsset;
use yii\helpers\Json;
use kartik\select2\Select2Asset;
use kartik\select2\ThemeBootstrapAsset;
use yii\web\View;
use homer\assets\SweetAlert2Asset;

class Datatables extends Widget
{
    public $clientOptions = [];

    public $clientEvents = [];

    public $autoIndex = false;

    public $options = [];

    public $select2 = false;

    public $buttons = false;

    public function init()
    {
        parent::init();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    public function run(){
        $this->registerPlugin('DataTable');
    }

    protected function registerPlugin($name)
    {
        $view = $this->getView();
        if($this->select2){
            Select2Asset::register($view);
            ThemeBootstrapAsset::register($view);
        }
        $bundle = DatatablesAsset::register($view);
        if($this->buttons){
            $bundle->css[] = 'ext/buttons/css/buttons.dataTables.min.css';
            $bundle->js[] = 'ext/buttons/js/dataTables.buttons.min.js';
            $bundle->js[] = 'ext/buttons/js/buttons.print.min.js';
            $bundle->js[] = 'ext/buttons/js/buttons.html5.min.js';
            $bundle->js[] = 'ext/buttons/js/pdfmake.min.js';
            $bundle->js[] = 'ext/buttons/js/vfs_fonts.js';
            $bundle->js[] = 'ext/buttons/js/jszip.min.js';
            $bundle->js[] = 'ext/buttons/js/buttons.flash.min.js';
            $bundle->js[] = 'ext/buttons/js/buttons.colVis.min.js';
        }
        SweetAlert2Asset::register($view);
        $view->registerJs('$.fn.dataTable.ext.errMode = \'throw\';',View::POS_END);
        $id = $this->options['id'];
        if ($this->clientOptions !== false) {
            $dtId = str_replace('-','',preg_replace('/(\w+) (\d+), (\d+)/i', '', $id));
            $options = empty($this->clientOptions) ? '' : Json::encode($this->clientOptions);
            $js = "var dt_".$dtId." = jQuery('#$id').$name($options);";
            $view->registerJs($js);
        }
        $this->registerClientEvents();
    }

    protected function registerClientEvents()
    {
        if (!empty($this->clientEvents)) {
            $id = $this->options['id'];
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }
}