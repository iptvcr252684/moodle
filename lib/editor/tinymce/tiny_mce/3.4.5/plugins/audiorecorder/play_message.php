<?php
/**
 * Created by Nadav Kavalerchik.
 * Contact info: nadavkav@gmail.com
 *
 */

require_once("../../../../../../../config.php");

$vcpath = $CFG->wwwroot."/lib/editor/tinymce/tiny_mce/3.4.5/plugins/audiorecorder/lang";

?>

<body onload="closeMe();">
  <div style="text-align:center;">
    <?php echo get_string('saveedsuccessfully','editor_tinymce'); ?>

    <br/><input type="button" onclick="onOK();" value="<?php echo get_string('closewindow','editor_tinymce' ); ?>">
  </div>

<?php

    if (file_exists('/usr/bin/ffmpeg')) {

        $jwplayer = "<script type='text/javascript' src='".$CFG->wwwroot."/lib/editor/tinymce/tiny_mce/3.4.5/plugins/audiorecorder/jwp/jwplayer.js'></script>

        <div id='mediaspace'>Play me</div>

        <script type='text/javascript'>
          jwplayer('mediaspace').setup({
            'flashplayer': '".$CFG->wwwroot."/lib/editor/tinymce/tiny_mce/3.4.5/plugins/audiorecorder/jwp/player.swf',
            'file': '".$_GET['AudioFile'].".flv',
            'duration': '33',
            'controlbar': 'bottom',
            'width': '470',
            'height': '24'
          });
        </script>";
        // No point in showing a player, since the file is not accessable, yet.
        //echo $jwplayer;

//      echo '<span id="mp3palyer" style="border:1px dashed;"><img src="'.$CFG->wwwroot.'/lib/editor/tinymce/tiny_mce/3.4.5/plugins/audiorecorder/img/icon.png">
//      <object height="15" width="200" type="application/x-shockwave-flash"
//        data="'.$CFG->wwwroot.'/lib/editor/tinymce/tiny_mce/3.4.5/plugins/audiorecorder/mp3player.swf?soundFile='.$_GET["AudioFile"].'.flv">
//        <param name="quality" value="high"></object></span>';
   } else {
      echo '<a target="_new" href="'.$_GET["AudioFile"].'">'.get_string("clicktoplay","editor_tinymce" ).'</a>';

  }

?>

</body>

<script language="javascript">
var howLong = 5000;
var t = null;

function closeMe(){
//  t = setTimeout("onOK()",howLong);
}

</script>

<?php
/* $timecode = time(); // some unique code for the mp3flash player, in case several are in the page.

    echo "<span id=\"mp2player_$timecode;\"></span><script type=\"text/javascript\">
var FO = { movie:\"{$CFG->wwwroot}/lib/editor/htmlarea/custom_plugins/audiorecorder/mp3player.swf?src=".$_GET['AudioFile'].".mp3\",
width:\"90\", height:\"15\", majorversion:\"6\", build:\"40\", flashvars:\"bgColour=000000&btnColour=ffffff&btnBorderColour=cccccc&iconColour=000000&iconOverColour=00cc00&trackColour=cccccc&handleColour=ffffff&loaderColour=ffffff&waitForPlay=yes\", quality: \"high\" };
UFO.create(FO, \"mp3player_$timecode\");
<\/script>";

 */
?>
