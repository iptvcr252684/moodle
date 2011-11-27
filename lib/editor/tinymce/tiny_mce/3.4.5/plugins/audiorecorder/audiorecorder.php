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

$vcpath = $CFG->wwwroot."/lib/editor/tinymce/tiny_mce/3.4.5/plugins/audiorecorder";
$tempaudiofolder = 'audiocapture';
$filename = $USER->id.'_audioclip_'.strftime("%H%M%S",time());
if (!mkdir($CFG->dataroot.'/'.$tempaudiofolder, 0777, true)) {
    //die('Failed to create folders...');
}

@header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php print_string('audiorecorder:title', 'editor_tinymce')?></title>
    <script type="text/javascript">
        var tempaudiofile = '<?php echo $filename; ?>';
        var tempaudiofolder = '<?php echo $tempaudiofolder; ?>';
    </script>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/media.js"></script>
	<link href="css/media.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="tabs"></div>
    <form onsubmit="insertMedia();return false;" action="#">
		<div class="panel_wrapper">
			<div id="general_panel" class="panel current">
<!--                <input id="src" name="src" type="hidden" value="" class="mceFocus" onchange="generatePreview();" />-->
<!--                <input id="filename" name="filename" type="hidden" value="" />-->

                <TABLE>
                  <TR>
                    <TD width="375">


                      <div align="center" style="color:#000000;font-family: Verdana, Arial, Helvetica, sans-serif;font-size:14px">
                          <applet
                              CODE="com.softsynth.javasonics.recplay.RecorderUploadApplet"
                              CODEBASE="codebase"
                              ARCHIVE="JavaSonicsListenUp.jar"
                              NAME="JavaSonicRecorderUploader"
                              WIDTH="400" HEIGHT="120">

                              <!-- Use a low sample rate that is good for voice. -->
                              <param name="frameRate" value="11025.0">
                              <!-- Most microphones are monophonic so use 1 channel. -->
                              <param name="numChannels" value="1">
                              <!-- Set maximum message length to whatever you want. -->
                              <param name="maxRecordTime" value="60.0">

                          	<!-- Specify URL and file to be played after upload. -->
                            <param name="refreshURL" value="play_message.php?AudioFile=<?php echo $tempaudiofolder.'/'.$filename; ?>">

                          	<!-- Specify name of file uploaded.
                          	     There are alternatives that allow dynamic naming. -->
                            <param name="uploadFileName" value="<?php echo $filename; ?>">

                          	<!-- Server script to receive the multi-part form data. -->
                            <param name="uploadURL" value="handle_upload_file.php?courseid=<?php echo $COURSE->id; ?>&userid=<?php echo $USER->id; ?>&tempaudiofolder=<?php echo $tempaudiofolder; ?>&tempaudiofile=<?php echo $filename; ?>">
                          <?php


                          	// Pass username and password from server to Applet if required.
                          	if( isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) )
                              {
                          		$authUserName = $_SERVER['PHP_AUTH_USER'];
                          		echo "    <param name=\"userName\" value=\"$authUserName\">\n";

                          		$authPassword = $_SERVER['PHP_AUTH_PW'];
                          		echo "    <param name=\"password\" value=\"$authPassword\">\n";
                          	}
                          ?>
                          </applet>
                      </div>



                    </TD>
                  </TR>
                </TABLE>
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
