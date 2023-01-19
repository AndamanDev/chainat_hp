<?php

use yii\jui\JuiAsset;
use homer\assets\jPlayerAsset;
use homer\assets\SocketIOAsset;
use homer\assets\ToastrAsset;
use homer\assets\SweetAlert2Asset;
use frontend\modules\app\models\TbSoundStation;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\View;

JuiAsset::register($this);
SocketIOAsset::register($this);
ToastrAsset::register($this);
SweetAlert2Asset::register($this);
$bundle = jPlayerAsset::register($this);
$bundle->js[] = 'vendor/jPlayer/dist/add-on/jquery.jplayer.inspector.js';
$this->registerJs('var baseUrl = ' . Json::encode(Url::base(true)) . '; ', View::POS_HEAD);
$this->registerJs('var model = ' . Json::encode($model) . '; ', View::POS_HEAD);

$this->title  = 'โปรแกรมเสียงเรียกคิว';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .jp-gui {
        position: relative;
        padding: 20px;
        width: auto;
    }

    .jp-gui.jp-no-volume {
        width: 432px;
    }

    .jp-gui ul {
        margin: 0;
        padding: 0;
    }

    .jp-gui ul li {
        position: relative;
        float: left;
        list-style: none;
        margin: 2px;
        padding: 4px 0;
        cursor: pointer;
    }

    .jp-gui ul li a {
        margin: 0 4px;
    }

    .jp-gui li.jp-repeat,
    .jp-gui li.jp-repeat-off {
        margin-left: 344px;
    }

    .jp-gui li.jp-mute,
    .jp-gui li.jp-unmute {
        margin-left: 20px;
    }

    .jp-gui li.jp-volume-max {
        margin-left: 120px;
    }

    li.jp-pause,
    li.jp-repeat-off,
    li.jp-unmute,
    .jp-no-solution {
        display: none;
    }

    .jp-progress-slider {
        position: absolute;
        top: 28px;
        left: 100px;
        width: 300px;
    }

    .jp-progress-slider .ui-slider-handle {
        cursor: pointer;
    }

    .jp-volume-slider {
        position: absolute;
        top: 31px;
        left: 508px;
        width: 100px;
        height: .4em;
    }

    .jp-volume-slider .ui-slider-handle {
        height: .8em;
        width: .8em;
        cursor: pointer;
    }

    .jp-gui.jp-no-volume .jp-volume-slider {
        display: none;
    }

    .jp-current-time,
    .jp-duration {
        position: absolute;
        top: 42px;
        font-size: 0.8em;
        cursor: default;
    }

    .jp-current-time {
        left: 100px;
    }

    .jp-duration {
        left: 360px;
    }

    .jp-gui.jp-no-volume .jp-duration {
        right: 70px;
    }

    .jp-clearboth {
        clear: both;
    }

    .jp-jplayer {
        width: auto !important;
        height: auto !important;
    }

    .jp-title {
        left: 140px;
        position: absolute;
        top: 42px;
        font-size: 0.8em;
        cursor: default;
    }
    .ui-widget-content{
        color: #222222;
    }

    /* div#jp_container * {
    background: inherit !important;
}
.ui-state-default {
    border: 1px solid inherit !important;
}
.ui-widget-content {
    color: white;
} */
</style>

<div class="row" style="height: 1px;">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="hpanel">

            <section>
                <div id="jquery_jplayer_N" class="jp-jplayer"></div>

                <div id="jp_container">
                    <div class="jp-gui ui-widget ui-widget-content ui-corner-all">
                        <ul>
                            <li class="jp-play ui-state-default ui-corner-all"><a href="javascript:;" class="jp-play ui-icon ui-icon-play" tabindex="1" title="play">play</a></li>
                            <li class="jp-pause ui-state-default ui-corner-all"><a href="javascript:;" class="jp-pause ui-icon ui-icon-pause" tabindex="1" title="pause">pause</a></li>
                            <li class="jp-stop ui-state-default ui-corner-all"><a href="javascript:;" class="jp-stop ui-icon ui-icon-stop" tabindex="1" title="stop">stop</a></li>
                            <li class="jp-repeat ui-state-default ui-corner-all"><a href="javascript:;" class="jp-repeat ui-icon ui-icon-refresh" tabindex="1" title="repeat">repeat</a></li>
                            <li class="jp-repeat-off ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-repeat-off ui-icon ui-icon-refresh" tabindex="1" title="repeat off">repeat off</a></li>
                            <li class="jp-mute ui-state-default ui-corner-all"><a href="javascript:;" class="jp-mute ui-icon ui-icon-volume-off" tabindex="1" title="mute">mute</a></li>
                            <li class="jp-unmute ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-unmute ui-icon ui-icon-volume-off" tabindex="1" title="unmute">unmute</a></li>
                            <li class="jp-volume-max ui-state-default ui-corner-all"><a href="javascript:;" class="jp-volume-max ui-icon ui-icon-volume-on" tabindex="1" title="max volume">max volume</a></li>
                        </ul>
                        <div class="jp-progress-slider"></div>
                        <div class="jp-volume-slider"></div>
                        <div class="jp-current-time"></div>
                        <div class="jp-title"></div>
                        <div class="jp-duration"></div>
                        <div class="jp-clearboth"></div>
                    </div>
            
                </div>
                <div id="jplayer_inspector"></div>
            </section>
        </div>
    </div>
</div>


<?php /*
<div class="row" style="height: 1px;">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" >
        <div class="hpanel">
            <div class="panel-body">
        
                <?php $form = ActiveForm::begin([
                        'id' => 'calling-form',
                        'type' => 'horizontal',
                        'options' => ['autocomplete' => 'off'],
                        'formConfig' => ['showLabels' => false],
                    ]);
                ?>
                <div class="form-group" style="margin-bottom: 0px;margin-top: 0px;">
                    <div class="col-md-4">
                        <?=
                        $form->field($model, 'sound_station_id')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map(TbSoundStation::find()->where(['sound_station_status' => 1])->asArray()->all(),'sound_station_id','sound_station_name'),
                            'options' => ['placeholder' => 'เลือกแผนก...'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                            'theme' => Select2::THEME_BOOTSTRAP,
                            'size' => Select2::LARGE,
                            'pluginEvents' => [
                                "change" => "function() {
                                    if($(this).val() != '' && $(this).val() != null){
                                        location.replace(baseUrl + \"/app/calling/play-sound?stationid=\" + $(this).val());
                                    }else{
                                        location.replace(baseUrl + \"/app/calling/play-sound\");
                                    }
                                }",
                            ]
                        ]);
                        ?>
                    </div>
                </div>
                <?php ActiveForm::end() ?>
           
                <section>
                    <div id="jquery_jplayer_N" class="jp-jplayer"></div>

                    <div id="jp_container">
                        <div class="jp-gui ui-widget ui-widget-content ui-corner-all">
                            <ul>
                                <li class="jp-play ui-state-default ui-corner-all"><a href="javascript:;" class="jp-play ui-icon ui-icon-play" tabindex="1" title="play">play</a></li>
                                <li class="jp-pause ui-state-default ui-corner-all"><a href="javascript:;" class="jp-pause ui-icon ui-icon-pause" tabindex="1" title="pause">pause</a></li>
                                <li class="jp-stop ui-state-default ui-corner-all"><a href="javascript:;" class="jp-stop ui-icon ui-icon-stop" tabindex="1" title="stop">stop</a></li>
                                <li class="jp-repeat ui-state-default ui-corner-all"><a href="javascript:;" class="jp-repeat ui-icon ui-icon-refresh" tabindex="1" title="repeat">repeat</a></li>
                                <li class="jp-repeat-off ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-repeat-off ui-icon ui-icon-refresh" tabindex="1" title="repeat off">repeat off</a></li>
                                <li class="jp-mute ui-state-default ui-corner-all"><a href="javascript:;" class="jp-mute ui-icon ui-icon-volume-off" tabindex="1" title="mute">mute</a></li>
                                <li class="jp-unmute ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-unmute ui-icon ui-icon-volume-off" tabindex="1" title="unmute">unmute</a></li>
                                <li class="jp-volume-max ui-state-default ui-corner-all"><a href="javascript:;" class="jp-volume-max ui-icon ui-icon-volume-on" tabindex="1" title="max volume">max volume</a></li>
                            </ul>
                            <div class="jp-progress-slider"></div>
                            <div class="jp-volume-slider"></div>
                            <div class="jp-current-time"></div>
                            <div class="jp-title"></div>
                            <div class="jp-duration"></div>
                            <div class="jp-clearboth"></div>
                        </div>
                        <!-- <div class="jp-playlist">
                            <ul>
                                <li></li>
                            </ul>
                        </div> -->
                    </div>
                    <div id="jplayer_inspector"></div>
                </section>
            </div>
        </div>
    </div>
</div>

*/ ?>
<?php
$this->registerJs(
    <<<JS
var jPlayerid = "#jquery_jplayer_N";
var jp_container = "#jp_container";
var i = 0;
var Timeoutended;
var myPlayer = $(jPlayerid),
    myPlayerData,
    fixFlash_mp4, // Flag: The m4a and m4v Flash player gives some old currentTime values when changed.
    fixFlash_mp4_id, // Timeout ID used with fixFlash_mp4
    ignore_timeupdate, // Flag used with fixFlash_mp4
    myControl = {
        progress: $(jp_container + " .jp-progress-slider"),
        volume: $(jp_container + " .jp-volume-slider")
    };
var myPlaylist = new jPlayerPlaylist({
        jPlayer: jPlayerid,
        cssSelectorAncestor: jp_container
    }, [
    
    ], {
    playlistOptions: {
        autoPlay: true,
        enableRemoveControls: true,
    },
    ready: function (event) {
        // Hide the volume slider on mobile browsers. ie., They have no effect.
        if(event.jPlayer.status.noVolume) {
            // Add a class and then CSS rules deal with it.
            $(".jp-gui").addClass("jp-no-volume");
        }
        // Determine if Flash is being used and the mp4 media type is supplied. BTW, Supplying both mp3 and mp4 is pointless.
        fixFlash_mp4 = event.jPlayer.flash.used && /m4a|m4v/.test(event.jPlayer.options.supplied);
    },
    timeupdate: function(event) {
        if(!ignore_timeupdate) {
            myControl.progress.slider("value", event.jPlayer.status.currentPercentAbsolute);
        }
    },
    volumechange: function(event) {
        if(event.jPlayer.options.muted) {
            myControl.volume.slider("value", 0);
        } else {
            myControl.volume.slider("value", event.jPlayer.options.volume);
        }
    },
    playing: function (event) {
        app.isPlaying = true
        var current = myPlaylist.current;
        // var data = myPlaylist.playlist[current];
        const data = event.jPlayer.status.media;
        if(data){ // data.wav.indexOf("please.wav") >= 0
            //dt_tabledisplay.ajax.url( '/app/display/data-display?id='+ query.id + '&q_ids='+data.artist.modelQueue.q_ids).load();
            // Display.reloadDisplay(data.artist.modelQueue.q_ids);
            // Display.reloadDisplay2();
            // Display.reloadHold();
            if(data.wav.indexOf("please.wav") !== -1 || data.wav.indexOf(".mp3") !== -1) {
              // var query = yii.getQueryParams(window.location.search)
              app.caller_ids = parseInt(data.artist.modelCaller.caller_ids)
              setTimeout(function(){
                  Display.blink(data);
              }, 500);
              socket.emit('display', data);//sending data
              tlastq.ajax.reload();//โหลดข้อมูลคิวล่าสุด
              // Queue.updateStatus(data.artist.modelCaller.caller_ids);//update tb_caller status = callend
            }
            //toastr.success(' ' + data.title, 'Calling!', {timeOut: 5000,positionClass: "toast-top-right"});
        }
        // if(data.wav.indexOf("Prompt1_Sir.wav") >= 0 || data.wav.indexOf("Prompt2_Sir.wav") >= 0){
        //     Queue.updateStatus(data.artist.modelCaller.caller_ids);//update tb_caller status = callend
        // }
        // if((current + 1) === myPlaylist.playlist.length){
        //     myPlaylist.remove();//reset q
        // }
    },
    loadstart: function (event) {
        //console.log(myPlaylist.playlist);
      if(Timeoutended){
        clearTimeout(Timeoutended)
      }
    },
    ended: function (event) {
      const data = event.jPlayer.status.media;
      if (data) {
        const caller = _.get(data, "artist.modelCaller");
        const index = _.findIndex(app.qlist, (item) => parseInt(item.caller_id) === parseInt(caller.caller_id));

        Timeoutended = setTimeout(() => {
          if (myPlaylist.playlist.length - 1 === myPlaylist.current) {
            myPlaylist.remove();
          } 
          
          if (myPlaylist.playlist.length && !app.isPlaying) {
            if ($(jPlayerid).data().jPlayer.status.paused === true) {
              myPlaylist.next();
            }
          }
        }, 300);

        if(data.wav.indexOf("Prompt1_Sir.wav") !== -1 || data.wav.indexOf("Prompt2_Sir.wav") !== -1 || data.wav.indexOf(".mp3") !== -1){
          Queue.updateStatus(data.artist.modelCaller.caller_ids);//update tb_caller status = callend
        }

        app.isPlaying = false
      }
    },
    error: function (event) {
        console.log(event);
    },
    loop: false,
    swfPath: "/vendor/jPlayer/dist/jplayer",
    supplied: "m4a, oga, mp3, wav, mp4, rtmp, flv, ogg, webmv, ogv, m4v",
    cssSelectorAncestor: jp_container,
    wmode: "window",
    keyEnabled: true,
    volume: 1,
    audioFullScreen: true,
    preload: 'auto'
});

myPlayerData = $(jPlayerid).data("jPlayer");

myControl.progress.slider({
    animate: "fast",
    max: 100,
    range: "min",
    step: 0.1,
    value : 0,
    slide: function(event, ui) {
        var sp = myPlayerData.status.seekPercent;
        if(sp > 0) {
            // Apply a fix to mp4 formats when the Flash is used.
            if(fixFlash_mp4) {
                ignore_timeupdate = true;
                clearTimeout(fixFlash_mp4_id);
                fixFlash_mp4_id = setTimeout(function() {
                    ignore_timeupdate = false;
                },1000);
            }
            // Move the play-head to the value and factor in the seek percent.
            $(jPlayerid).jPlayer("playHead", ui.value * (100 / sp));
        } else {
            // Create a timeout to reset this slider to zero.
            setTimeout(function() {
                myControl.progress.slider("value", 0);
            }, 0);
        }
    }
});

// Create the volume slider control
myControl.volume.slider({
    animate: "fast",
    max: 1,
    range: "min",
    step: 0.01,
    value : myPlaylist.options.volume,
    slide: function(event, ui) {
        $(jPlayerid).jPlayer("option", "muted", false);
        $(jPlayerid).jPlayer("option", "volume", ui.value);
    }
});

$("#jplayer_inspector").jPlayerInspector({jPlayer:$(jPlayerid)});

//Socket Event
socket
.on('call', (res) => {
    if(model != null && Object.keys(model).length && myPlaylist.playlist.filter(r => r.title === res.modelQueue.q_num).length === 0){
        var counters = (model.counterserviceid).split(',').map(v => parseInt(v));
        if(jQuery.inArray(parseInt(res.counter.counterserviceid), counters) != -1) {
            if(jQuery.inArray((res.modelQueue.serviceid).toString(), config.service_id) != -1 && jQuery.inArray((res.counter.counterservice_type).toString(), config.counterservice_id) != -1) {
                // Display.reloadDisplay();
                // Display.reloadDisplay2();
                // Display.reloadHold();
                
                setTimeout(function(){
                    // Queue.addMedia(res);
                   
                }, 1000);
            }
        }
    }
    
    // if(jQuery.inArray(parseInt(res.data.counter_service_id), counters) != -1) {
    //     Queue.addMedia(res);
    // }
});

Queue = {
    updateStatus: function(ids){
        $.ajax({
            method: "GET",
            url: "/app/calling/update-status",
            data: {ids:ids},
            dataType: "json",
            success: function(res){
                if(res.status == 200){
                   // toastr.success('Update Status Completed!', 'Success!', {timeOut: 5000,positionClass: "toast-top-right",});
                }else{
                    swal({
                        type: 'error',
                        title: 'เกิดข้อผิดพลาด!!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            },
            error:function(jqXHR, textStatus, errorThrown){
                swal({
                    type: 'error',
                    title: errorThrown,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    },
    autoloadMedia: function(){
        var counters = model.counterserviceid ? (model.counterserviceid).split(',') : [];
        $.ajax({
            method: "POST",
            url: baseUrl+"/app/calling/autoload-media",
            data: model,
            dataType: "json",
            success: function(res){
                if(res.status == 200 && res.rows.length){
                    $.each(res.rows, function( index, data ) {
                        if(jQuery.inArray(data.model.counterserviceid, counters) != -1) {
                            $.each(data.sound, function( i, sound ) {
                                myPlaylist.add({
                                    title: data.model.q_num,
                                    artist: data,
                                    wav: sound
                                });
                            });
                        }
                    });
                    $(jPlayerid).jPlayer("play");
                }
            },
            error:function(jqXHR, textStatus, errorThrown){
                swal({
                    type: 'error',
                    title: errorThrown,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    },
    addMedia: function(res){
      if(res.audio) {
        myPlaylist.add({
            title: res.modelQueue.q_num,
            artist: res,
            wav: res.audio.file1 + "?v=" + Date.now()
        });
      } else {
        $.each(res.sound, function( index, sound ) {
            myPlaylist.add({
                title: res.modelQueue.q_num,
                artist: res,
                wav: sound + "?v=" + Date.now()
            });
        });
      }
        
      $(jPlayerid).jPlayer("play");
    },
    init: function(){
        var self = this;
        self.autoloadMedia();
    }
};
Queue.init();
//hidden menu
$('body').addClass('hide-sidebar');
JS
);
?>