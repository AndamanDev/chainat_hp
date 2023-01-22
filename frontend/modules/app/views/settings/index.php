<?php

use homer\assets\DatatablesAsset;
use homer\assets\ToastrAsset;
use yii\helpers\Json;
use yii\web\View;

$bundle = DatatablesAsset::register($this);
$bundle->css[] = 'ext/buttons/css/buttons.dataTables.min.css';
$bundle->js[] = 'ext/buttons/js/dataTables.buttons.min.js';
$bundle->js[] = 'ext/buttons/js/buttons.print.min.js';
$bundle->js[] = 'ext/buttons/js/buttons.html5.min.js';
$bundle->js[] = 'ext/buttons/js/pdfmake.min.js';
$bundle->js[] = 'ext/buttons/js/vfs_fonts.js';
$bundle->js[] = 'ext/buttons/js/jszip.min.js';
$bundle->js[] = 'ext/buttons/js/buttons.flash.min.js';
$bundle->js[] = 'ext/buttons/js/buttons.colVis.min.js';

ToastrAsset::register($this);
$this->registerCssFile("@web/vendor/waitMe/waitMe.min.css", [
  'depends' => [\yii\bootstrap\BootstrapAsset::class],
]);

$this->title = 'ตั้งค่า';

$this->registerJs('var accesstoken = ' . Json::encode(Yii::$app->user->identity->getAuthKey()) . '; ', View::POS_HEAD);
?>
<style>
  .toggle.ios,
  .toggle-on.ios,
  .toggle-off.ios {
    border-radius: 20px;
  }

  .toggle.ios .toggle-handle {
    border-radius: 20px;
  }

  .checkbox label:after,
  .radio label:after {
    content: '';
    display: table;
    clear: both;
  }

  .checkbox .cr,
  .radio .cr {
    position: relative;
    display: inline-block;
    border: 1px solid #a9a9a9;
    border-radius: .25em;
    width: 1.3em;
    height: 1.3em;
    float: left;
    margin-right: .5em;
  }

  .radio .cr {
    border-radius: 50%;
  }

  .checkbox .cr .cr-icon,
  .radio .cr .cr-icon {
    position: absolute;
    font-size: .8em;
    line-height: 0;
    top: 50%;
    left: 20%;
  }

  .radio .cr .cr-icon {
    margin-left: 0.04em;
  }

  .checkbox label input[type="checkbox"],
  .radio label input[type="radio"] {
    display: none;
  }

  .checkbox label input[type="checkbox"]+.cr>.cr-icon,
  .radio label input[type="radio"]+.cr>.cr-icon {
    transform: scale(3) rotateZ(-20deg);
    opacity: 0;
    transition: all .3s ease-in;
  }

  .checkbox label input[type="checkbox"]:checked+.cr>.cr-icon,
  .radio label input[type="radio"]:checked+.cr>.cr-icon {
    transform: scale(1) rotateZ(0deg);
    opacity: 1;
  }

  .checkbox label input[type="checkbox"]:disabled+.cr,
  .radio label input[type="radio"]:disabled+.cr {
    opacity: .5;
  }

  .d-flex {
    display: flex !important;
  }

  .justify-content-end {
    justify-content: end !important;
  }

  .justify-content-start {
    justify-content: start !important;
  }
</style>
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="hpanel">
      <div class="panel-body p-0">
        <?= $this->render('_tabs'); ?>
        <div class="tab-content">

          <div id="tab-service" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'service') : ?>
              <?= $this->render('_content_service_group'); ?>
            <?php endif; ?>
          </div>

          <div id="tab-files" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'files') : ?>
              <?= $this->render('_content_sound_source'); ?>
            <?php endif; ?>
          </div>

          <div id="tab-sound" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'sound') : ?>
              <?= $this->render('_content_sound'); ?>
            <?php endif; ?>
          </div>

          <div id="tab-display" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'display') : ?>
              <?= $this->render('_content_display'); ?>
            <?php endif; ?>
          </div>

          <div id="tab-counter" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'counter') : ?>
              <?= $this->render('_content_counter'); ?>
            <?php endif; ?>
          </div>

          <div id="tab-ticket" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'ticket') : ?>
              <?= $this->render('_content_ticket'); ?>
            <?php endif; ?>
          </div>

          <div id="tab-service-profile" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'service-profile') : ?>
              <?= $this->render('_content_service_profile'); ?>
            <?php endif; ?>
          </div>

          <div id="tab-player" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'player') : ?>
              <?= $this->render('_content_sound_station'); ?>
            <?php endif; ?>
          </div>

          <div id="tab-config-notification" class="tab-pane active">
            <?php if (Yii::$app->controller->action->id == 'config-notification') : ?>
              <?= $this->render('_content_calling_config'); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
echo $this->render('modal');
$this->registerJs(
  <<<JS
$('body').addClass('hide-sidebar');
Events = {
    toggle: function(elm,state,action){
        var status;
        if(state){
            status = 1;
        }else{
            status = 0;
        }
        $.ajax({
            method: "POST",
            url: "/app/settings/" + action,
            data: {
                status: status,
                key: $(elm).data('key')
            },
            dataType: "json",
            success: function(res){
                //api.ajax.reload();
                toastr.success('', 'Success!', {timeOut: 3000,positionClass: "toast-top-right"});
            },
            error: function(jqXHR, textStatus, errorThrown){
                swal('Oops...',errorThrown,'error');
            }
        });
    }
};

$('#tb-service-group')
  .on('preXhr.dt', function ( e, settings, data ) {
    $('#tb-service-group').waitMe({
      effect : 'bouncePulse',
      text : '',
      bg : 'rgba(255,255,255,0.7)',
      color : '#000',
      maxSize : '',
      waitTime : -1,
      textPos : 'vertical',
      fontSize : '',
      source : '',
      onClose : function() {}
    });
  })
  .on('error.dt', function ( e, settings, techNote, message ) {
    $('#tb-service-group').waitMe("hide");
  })
  .on('xhr.dt', function ( e, settings, json, xhr ) {
    $('#tb-service-group').waitMe("hide");
  })
  .on('draw.dt', function () {
    var info = dt_tbservicegroup.page.info();
    dt_tbservicegroup.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = i + 1 + info.start;
    });
  });


  $('#tb-sound')
  .on('preXhr.dt', function ( e, settings, data ) {
    $('#tb-sound').waitMe({
      effect : 'bouncePulse',
      text : '',
      bg : 'rgba(255,255,255,0.7)',
      color : '#000',
      maxSize : '',
      waitTime : -1,
      textPos : 'vertical',
      fontSize : '',
      source : '',
      onClose : function() {}
    });
  })
  .on('error.dt', function ( e, settings, techNote, message ) {
    $('#tb-sound').waitMe("hide");
  })
  .on('xhr.dt', function ( e, settings, json, xhr ) {
    $('#tb-sound').waitMe("hide");
  })
  .on('draw.dt', function () {
    var info = dt_tbsound.page.info();
    dt_tbsound.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = i + 1 + info.start;
    });
  });

  $('#tb-display')
  .on('preXhr.dt', function ( e, settings, data ) {
    $('#tb-display').waitMe({
      effect : 'bouncePulse',
      text : '',
      bg : 'rgba(255,255,255,0.7)',
      color : '#000',
      maxSize : '',
      waitTime : -1,
      textPos : 'vertical',
      fontSize : '',
      source : '',
      onClose : function() {}
    });
  })
  .on('error.dt', function ( e, settings, techNote, message ) {
    $('#tb-display').waitMe("hide");
  })
  .on('xhr.dt', function ( e, settings, json, xhr ) {
    $('#tb-display').waitMe("hide");
  })
  .on('draw.dt', function () {
    var info = dt_tbdisplay.page.info();
    dt_tbdisplay.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = i + 1 + info.start;
    });
  });

  $('#tb-counter')
  .on('preXhr.dt', function ( e, settings, data ) {
    $('#tb-counter').waitMe({
      effect : 'bouncePulse',
      text : '',
      bg : 'rgba(255,255,255,0.7)',
      color : '#000',
      maxSize : '',
      waitTime : -1,
      textPos : 'vertical',
      fontSize : '',
      source : '',
      onClose : function() {}
    });
  })
  .on('error.dt', function ( e, settings, techNote, message ) {
    $('#tb-counter').waitMe("hide");
  })
  .on('xhr.dt', function ( e, settings, json, xhr ) {
    $('#tb-counter').waitMe("hide");
  })
  .on('draw.dt', function () {
    var info = dt_tbcounter.page.info();
    dt_tbcounter.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = i + 1 + info.start;
    });
  });

  $('#tb-ticket')
  .on('preXhr.dt', function ( e, settings, data ) {
    $('#tb-ticket').waitMe({
      effect : 'bouncePulse',
      text : '',
      bg : 'rgba(255,255,255,0.7)',
      color : '#000',
      maxSize : '',
      waitTime : -1,
      textPos : 'vertical',
      fontSize : '',
      source : '',
      onClose : function() {}
    });
  })
  .on('error.dt', function ( e, settings, techNote, message ) {
    $('#tb-ticket').waitMe("hide");
  })
  .on('xhr.dt', function ( e, settings, json, xhr ) {
    $('#tb-ticket').waitMe("hide");
  })
  .on('draw.dt', function () {
    var info = dt_tbticket.page.info();
    dt_tbticket.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = i + 1 + info.start;
    });
  });

  $('#tb-service-profile')
  .on('preXhr.dt', function ( e, settings, data ) {
    $('#tb-service-profile').waitMe({
      effect : 'bouncePulse',
      text : '',
      bg : 'rgba(255,255,255,0.7)',
      color : '#000',
      maxSize : '',
      waitTime : -1,
      textPos : 'vertical',
      fontSize : '',
      source : '',
      onClose : function() {}
    });
  })
  .on('error.dt', function ( e, settings, techNote, message ) {
    $('#tb-service-profile').waitMe("hide");
  })
  .on('xhr.dt', function ( e, settings, json, xhr ) {
    $('#tb-service-profile').waitMe("hide");
  })
  .on('draw.dt', function () {
    var info = dt_tbserviceprofile.page.info();
    dt_tbserviceprofile.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = i + 1 + info.start;
    });
  });

  $('#tb-sound-station')
  .on('preXhr.dt', function ( e, settings, data ) {
    $('#tb-sound-station').waitMe({
      effect : 'bouncePulse',
      text : '',
      bg : 'rgba(255,255,255,0.7)',
      color : '#000',
      maxSize : '',
      waitTime : -1,
      textPos : 'vertical',
      fontSize : '',
      source : '',
      onClose : function() {}
    });
  })
  .on('error.dt', function ( e, settings, techNote, message ) {
    $('#tb-sound-station').waitMe("hide");
  })
  .on('xhr.dt', function ( e, settings, json, xhr ) {
    $('#tb-sound-station').waitMe("hide");
  })
  .on('draw.dt', function () {
    var info = dt_tbsoundstation.page.info();
    dt_tbsoundstation.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = i + 1 + info.start;
    });
  });

  $('#tb-calling-config')
  .on('preXhr.dt', function ( e, settings, data ) {
    $('#tb-calling-config').waitMe({
      effect : 'bouncePulse',
      text : '',
      bg : 'rgba(255,255,255,0.7)',
      color : '#000',
      maxSize : '',
      waitTime : -1,
      textPos : 'vertical',
      fontSize : '',
      source : '',
      onClose : function() {}
    });
  })
  .on('error.dt', function ( e, settings, techNote, message ) {
    $('#tb-calling-config').waitMe("hide");
  })
  .on('xhr.dt', function ( e, settings, json, xhr ) {
    $('#tb-calling-config').waitMe("hide");
  })
  .on('draw.dt', function () {
    var info = dt_tbcallingconfig.page.info();
    dt_tbcallingconfig.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
      cell.innerHTML = i + 1 + info.start;
    });
  });
JS
);

$this->registerJsFile(
  '@web/vendor/waitMe/waitMe.min.js',
  ['depends' => [\yii\web\JqueryAsset::class]]
);
?>