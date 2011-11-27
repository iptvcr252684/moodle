<?php
/*
 * This script will copy the temporary video clip that was saved by the Video Recorder Java applet
 * inside a temoprary folder : moodledata/videocapture to the proper hased file record Moodle2 uses
 * It is called by an Ajax function from the file media.js that is responsible for the "Insert" button click event
 *
 * todo: get better filearea, component and itemid parameters from Moodle
 *
 * feedback and support: Nadav Kavalerchik, nadavkav@gmail.com
 */
//define('NO_MOODLE_COOKIES', true);

require("../../../../../../../config.php");

$videofolder = required_param('videofolder', PARAM_SAFEDIR);
$videofile = required_param('videofile', PARAM_CLEANFILE);
$tempvideofile = $CFG->dataroot.'/'.$videofolder .'/mp4/'.$videofile.'.mp4';

$context = get_context_instance(CONTEXT_USER, $USER->id);
$fs = get_file_storage();

// Prepair file info record for Moodle2 file storage DB record
$record = new stdClass();
$record->filename = $videofile.'.mp4';
$record->mimetype = 'video/mp4';
$record->filearea = 'private';
$record->component = 'user';
$record->filepath = '/'; //optional_param('savepath', '/', PARAM_PATH);
$record->itemid   = 0; //optional_param('itemid', 0, PARAM_INT);
$record->license  = $CFG->sitedefaultlicense; //optional_param('license', $CFG->sitedefaultlicense, PARAM_TEXT);
$record->author   = ''; //optional_param('author', '', PARAM_TEXT);
$record->contextid = $context->id;
$record->userid    = $USER->id;
$record->source    = 'videocapture';

if ($stored_file = $fs->create_file_from_pathname($record, $tempvideofile)) {
    unlink($tempvideofile); // delete temporary video clip
}

echo "{$CFG->wwwroot}/pluginfile.php/{$context->id}/{$record->component}/{$record->filearea}/{$videofile}.mp4";
