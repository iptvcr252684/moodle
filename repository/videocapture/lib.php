<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A repository plugin to allow user uploading video capture files
 *
 * @since 2.0
 * @package    repository
 * @subpackage videocapture
 * @copyright  2011 Nadav Kavalerchik
 * @author     Kavalerchik Nadav <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class repository_videocapture extends repository {
    private $mimetypes = array();

    public function check_login() {
        //echo "Display video capture dialog";
        return true;
    }
    /**
     * Print a upload form
     * @return array
     */
    public function print_login() {
        //echo "Display video capture dialog";
        //return "Hello";
        return $this->get_listing();
    }

    /**
     * Process video capture uploaded file
     * @return array|bool
     */
    public function upload($saveas_filename, $maxbytes) {
        global $USER, $CFG;

        $types = optional_param('accepted_types', '*', PARAM_RAW);
        if ((is_array($types) and in_array('*', $types)) or $types == '*') {
            $this->mimetypes = '*';
        } else {
            foreach ($types as $type) {
                $this->mimetypes[] = mimeinfo('type', $type);
            }
        }

        $record = new stdClass();
        $record->filearea = 'draft';
        $record->component = 'user';
        $record->filepath = optional_param('savepath', '/', PARAM_PATH);
        $record->itemid   = optional_param('itemid', 0, PARAM_INT);
        $record->license  = optional_param('license', $CFG->sitedefaultlicense, PARAM_TEXT);
        $record->author   = optional_param('author', '', PARAM_TEXT);

        $context = get_context_instance(CONTEXT_USER, $USER->id);
        $elname = 'repo_videocapture_file';

        $fs = get_file_storage();
        $sm = get_string_manager();

        if ($record->filepath !== '/') {
            $record->filepath = file_correct_filepath($record->filepath);
        }

        if (!isset($_FILES[$elname])) {
            throw new moodle_exception('nofile');
        }
        if (!empty($_FILES[$elname]['error'])) {
            switch ($_FILES[$elname]['error']) {
            case UPLOAD_ERR_INI_SIZE:
                throw new moodle_exception('upload_error_ini_size', 'repository_videocapture');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                throw new moodle_exception('upload_error_form_size', 'repository_videocapture');
                break;
            case UPLOAD_ERR_PARTIAL:
                throw new moodle_exception('upload_error_partial', 'repository_videocapture');
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new moodle_exception('upload_error_no_file', 'repository_videocapture');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new moodle_exception('upload_error_no_tmp_dir', 'repository_videocapture');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                throw new moodle_exception('upload_error_cant_write', 'repository_videocapture');
                break;
            case UPLOAD_ERR_EXTENSION:
                throw new moodle_exception('upload_error_extension', 'repository_videocapture');
                break;
            default:
                throw new moodle_exception('nofile');
            }
        }

        if (empty($saveas_filename)) {
            $record->filename = clean_param($_FILES[$elname]['name'], PARAM_FILE);
        } else {
            $ext = '';
            $match = array();
            $filename = clean_param($_FILES[$elname]['name'], PARAM_FILE);
            if (preg_match('/\.([a-z0-9]+)$/i', $filename, $match)) {
                if (isset($match[1])) {
                    $ext = $match[1];
                }
            }
            $ext = !empty($ext) ? $ext : '';
            if (preg_match('#\.(' . $ext . ')$#i', $saveas_filename)) {
                // saveas filename contains file extension already
                $record->filename = $saveas_filename;
            } else {
                $record->filename = $saveas_filename . '.' . $ext;
            }
        }

        if ($this->mimetypes != '*') {
            // check filetype
            $filemimetype = mimeinfo('type', $_FILES[$elname]['name']);
            if (!in_array($filemimetype, $this->mimetypes)) {
                if ($sm->string_exists($filemimetype, 'mimetypes')) {
                    $filemimetype = get_string($filemimetype, 'mimetypes');
                }
                throw new moodle_exception('invalidfiletype', 'repository', '', $filemimetype);
            }
        }

        if (empty($record->itemid)) {
            $record->itemid = 0;
        }

        if (($maxbytes!==-1) && (filesize($_FILES[$elname]['tmp_name']) > $maxbytes)) {
            throw new file_exception('maxbytes');
        }
        $record->contextid = $context->id;
        $record->userid    = $USER->id;
        $record->source    = '';

        if (repository::draftfile_exists($record->itemid, $record->filepath, $record->filename)) {
            $existingfilename = $record->filename;
            $unused_filename = repository::get_unused_filename($record->itemid, $record->filepath, $record->filename);
            $record->filename = $unused_filename;
            $stored_file = $fs->create_file_from_pathname($record, $_FILES[$elname]['tmp_name']);
            $event = array();
            $event['event'] = 'fileexists';
            $event['newfile'] = new stdClass;
            $event['newfile']->filepath = $record->filepath;
            $event['newfile']->filename = $unused_filename;
            $event['newfile']->url = moodle_url::make_draftfile_url($record->itemid, $record->filepath, $unused_filename)->out();

            $event['existingfile'] = new stdClass;
            $event['existingfile']->filepath = $record->filepath;
            $event['existingfile']->filename = $existingfilename;
            $event['existingfile']->url      = moodle_url::make_draftfile_url($record->itemid, $record->filepath, $existingfilename)->out();;
            return $event;
        } else {
            $stored_file = $fs->create_file_from_pathname($record, $_FILES[$elname]['tmp_name']);

            return array(
                'url'=>moodle_url::make_draftfile_url($record->itemid, $record->filepath, $record->filename)->out(),
                'id'=>$record->itemid,
                'file'=>$record->filename);
        }
    }

    /**
     * Return a upload form
     * @return array
     */
    public function get_listing() {
        global $CFG;
        $ret = array();
        $ret['nologin']  = false;
        $ret['nosearch'] = false;
        $ret['norefresh'] = true;
        $ret['list'] = array('filename.ogv');
        $ret['dynload'] = false;
        $ret['videocapture'] = array('label'=>get_string('attachment', 'repository'), 'id'=>'repo-form');
        $ret['upload'] = 'filename.ogv';
        //echo "Display video capture dialog";
        return $ret;
    }

    /**
     * supported return types
     * @return int
     */
    public function supported_returntypes() {
        return FILE_INTERNAL;
    }
}
