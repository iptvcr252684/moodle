<?php
/*
 * This script will copy the temporary audio clip that was saved by the audio Recorder Java applet
 * inside a temoprary folder : moodledata/audiocapture to the proper hased file record Moodle2 uses
 * It is called by an Ajax function from the file media.js that is responsible for the "Insert" button click event
 *
 * todo: get better filearea, component and itemid parameters from Moodle
 *
 * feedback and support: Nadav Kavalerchik, nadavkav@gmail.com
 */
//define('NO_MOODLE_COOKIES', true);

require_once("../../../../../../../config.php");

$audiofolder = required_param('audiofolder', PARAM_SAFEDIR);
$audiofile = required_param('audiofile', PARAM_CLEANFILE);
if (file_exists('/usr/bin/ffmpeg')) {
    $tempaudiofile = $CFG->dataroot.'/'.$audiofolder .'/'.$audiofile.'.flv';
    $fileext ='.flv';
} else {
    $tempaudiofile = $CFG->dataroot.'/'.$audiofolder .'/'.$audiofile.'.wav';
    $fileext ='.wav';
}

$context = get_context_instance(CONTEXT_USER, $USER->id);
$fs = get_file_storage();

// Prepair file info record for Moodle2 file storage DB record
$record = new stdClass();
$record->filename = $audiofile.$fileext;
$record->mimetype = 'audio/'.$fileext;
$record->filearea = 'private';
$record->component = 'user';
$record->filepath = '/'; //optional_param('savepath', '/', PARAM_PATH);
$record->itemid   = 0; //optional_param('itemid', 0, PARAM_INT);
$record->license  = $CFG->sitedefaultlicense; //optional_param('license', $CFG->sitedefaultlicense, PARAM_TEXT);
$record->author   = ''; //optional_param('author', '', PARAM_TEXT);
$record->contextid = $context->id;
$record->userid    = $USER->id;
$record->source    = 'audiocapture';

if ($stored_file = $fs->create_file_from_pathname($record, $tempaudiofile)) {
    unlink($tempaudiofile); // delete temporary audio clip
}

echo "{$CFG->wwwroot}/pluginfile.php/{$context->id}/{$record->component}/{$record->filearea}/{$audiofile}".$fileext;
