<?php
/*
 * This is the Video Recorder java applet dialog.
 * It records a temporary video (+audio) clip and save it into the user's private files
 * (file is uploaded into a temporary moodledata/videocapture folder and then moved into
 * Moodle2 DB file storage)
 * Then, embed a link to the file, into the HTML content of the current tinymce editor.
 *
 * feedback and support: Nadav Kavalerchik, nadavkav@gmail.com
 *
 */
define('NO_MOODLE_COOKIES', true);

require("../../../../../../../config.php");

$lang = optional_param('language','en', PARAM_SAFEDIR);

if (!get_string_manager()->translation_exists($lang, false)) {
    $lang = 'en';
}
$SESSION->lang = $lang;

$langmapping = array('cs'=>'cz', 'pt_br'=>'pt-br');

// fix non-standard lang names
if (array_key_exists($lang, $langmapping)) {
    $lang = $langmapping[$lang];
}

$vcpath = $CFG->wwwroot."/lib/editor/tinymce/tiny_mce/3.4.5/plugins/videocapture";
$filename = 'videoclip_'.strftime("%H%M%S",time());
$uploads_dir = 'videocapture';
if (!mkdir($CFG->dataroot.'/'.$uploads_dir .'/mp4', 0777, true)) {
    //die('Failed to create folders...');
}

@header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php print_string('videocapture:title', 'editor_tinymce')?></title>
    <script type="text/javascript">
        var tempvideofile = '<?php echo $filename; ?>';
        var tempvideofolder = '<?php echo $uploads_dir; ?>';
    </script>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/media.js"></script>
	<link href="css/media.css" rel="stylesheet" type="text/css" />

<SCRIPT language="JavaScript">
  function vc_visibility()
  {
    document.getElementById("loading").style.visibility="hidden";
    document.getElementById("loaded").style.visibility="visible";
  }

  function setStatus(num, str)	{
    // Handle status changes
    //**********************
    // Status codes:
    // StartUpload = 0;
    // UploadDone = 1;
    // StartRecord = 2;
    // StartPlay = 3;
    // PauseSet = 4;
    // Stopped = 5;

    //document.Gui_RP.Status.value = str;
    //window.frames[1].document.getElementById('status').value = str;
    document.getElementById('status').value = str;
  }



  function setTimer(str)	{
    document.getElementById('timer').value = str;
  }

  function RECORD_RP()	{
    document.VimasVideoApplet.RECORD_VIDEO();
  }


  function PLAYBACK_RP()	{
    document.VimasVideoApplet.PLAY_VIDEO();
  }

  function PAUSE_RP()	{
    document.VimasVideoApplet.PAUSE_VIDEO();
  }

  function STOP_RP()	{
    document.VimasVideoApplet.STOP_VIDEO();
  }

  function UPLOAD_RP()	{
      document.VimasVideoApplet.UPLOAD_VIDEO(String(document.getElementById('FileName').value));
    //document.VimasVideoApplet.UPLOAD_VIDEO(String('<?php echo $filename; ?>'));
  }

</SCRIPT>


</head>
<body onLoad="vc_visibility()" style="display: none">
    <div class="tabs"></div>
    <form onsubmit="insertMedia();return false;" action="#">
		<div class="panel_wrapper">
			<div id="general_panel" class="panel current">
                <input id="src" name="src" type="hidden" value="" class="mceFocus" onchange="generatePreview();" />
                <input id="filename" name="filename" type="hidden" value="" />

                <TABLE>
                  <TR>
                    <TD width="375">

                      <SPAN ID="loading" style="visibility:visible">
                    <div align="left" style="color:#000000;font-family: Verdana, Arial, Helvetica, sans-serif;font-size:14px">
                    Loading Java applet...
                    </div>
                      </SPAN>

                      <SPAN ID="loaded" style="visibility:hidden">
                      <div align="center" style="color:#000000;font-family: Verdana, Arial, Helvetica, sans-serif;font-size:14px">
                        <applet
                          ID	   = "applet"
                          ARCHIVE  = "VideoApplet.jar"
                          codebase = "VideoApplet"
                          code     = "com.vimas.videoapplet.VimasVideoApplet.class"
                          name     = "VimasVideoApplet"
                          width    = "182"
                          height   = "165"
                          hspace   = "0"
                          vspace   = "0"
                          align    = "middle">

                            <PARAM NAME = "left" 		value="100">
                            <PARAM NAME = "top" 		value="200">
                            <PARAM NAME = "Registration"	VALUE = "demo">
                            <PARAM NAME = "LocalizationFile" 	VALUE = "<?php echo "$vcpath/VideoApplet/Localization/localization.xml"; ?>">
                            <PARAM NAME = "ServerScript"	VALUE = "<?php echo "$vcpath/VideoApplet/retrive_v.php";?>">
                            <PARAM NAME = "VideoServerFolder"	VALUE = "<?php echo $uploads_dir;?>">
                            <PARAM NAME = "TimeLimit"		VALUE = "30">
                            <PARAM NAME = "BlockSize"		VALUE = "10240">
                            <PARAM NAME = "UserServerFolder"	VALUE = "mp4">

                            <PARAM NAME = "LowQuality" 		VALUE = "96,24">
                            <PARAM NAME = "NormalQuality" 	VALUE = "160,32">
                            <PARAM NAME = "HighQuality" 	VALUE = "256,48">

                            <PARAM NAME = "FrameSize"		VALUE = "small">
                            <PARAM NAME = "interface"		VALUE = "compact">

                            <PARAM NAME = "UserPostVariables"	VALUE = "name,country">
                            <PARAM NAME = "name"		VALUE = "Vimas Video Recorder">
                            <PARAM NAME = "country"		VALUE = "Israel">
                        </applet>
                      </div>

                      </SPAN>
                      <FORM name="Gui_RP" id="Gui_RP" onsubmit="event.returnValue=false;return false;">
                    <TABLE CELLSPACING=1 style="color:#000000;font-family:Tahoma;font-size:10pt" border="0">
                      <TR>
                        <TD width="70"><?php print_string('videocapture:recorder', 'editor_tinymce')?></TD>
                        <TD width="70"><input TYPE=button VALUE="<?php print_string('videocapture:record', 'editor_tinymce')?>" STYLE="width:70;font-family:Tahoma;font-size:10pt" onClick="RECORD_RP();"></TD>
                        <TD width="75"><input TYPE=button VALUE="<?php print_string('videocapture:stop', 'editor_tinymce'); ?>" STYLE="width:75;font-family:Tahoma;font-size:10pt" onClick="STOP_RP();"></TD>
                        <TD width="70"><input TYPE=button VALUE="<?php print_string('videocapture:play', 'editor_tinymce'); ?>" STYLE="width:70;font-family:Tahoma;font-size:10pt" onClick="PLAYBACK_RP();"></TD>
                        <TD width="75"><input TYPE=button VALUE="<?php print_string('videocapture:pause', 'editor_tinymce'); ?>" STYLE="width:75;font-family:Tahoma;font-size:10pt" onClick="PAUSE_RP();"></TD>
                      </TR>
                      <TR>
                        <TD COLSPAN="4"><?php print_string('videocapture:whendone', 'editor_tinymce'); ?></TD>
                        <!--TD ALIGN=right  width="130"--><input TYPE=hidden ID="FileName" NAME="FileName" VALUE="<?php echo $filename; ?>" SIZE=20 MAXLENGTH=16 style="width:150;font-family:Tahoma;font-size:10pt"><!--/TD-->
                        <TD width="75" COLSPAN=2><input TYPE=button VALUE="send" STYLE="width:75" onClick="UPLOAD_RP();"></TD>
                      </TR>
                      <TR>
                        <TD><?php print_string('videocapture:status', 'editor_tinymce'); ?></TD>
                        <TD COLSPAN=3><input ID="status" TYPE=text NAME="Status" VALUE="" SIZE=34 MAXLENGTH=60 style="width:240;font-family:Tahoma;font-size:10pt"></TD>
                        <TD><input TYPE=text ID="timer" NAME="Timer" SIZE=7 style="width:75;font-family:Tahoma;font-size:10pt"></TD>
                      </TR>
                    </TABLE>
                      </FORM>
                    </TD>
                  </TR>
                </TABLE>
                <div><br/><br/><br/>Web video recorder  powered by<A target="_new" HREF="http://www.vimas.com/">&ldquo;VIMAS Technologies&rdquo;</a></div>
			</div>

		</div>

		<div class="mceActionPanel">
			<div style="float: left">
				<input type="submit" id="insert" name="insert" value="{#insert}" />
			</div>

			<div style="float: right">
				<input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
			</div>
		</div>
	</form>
</body>
</html>
