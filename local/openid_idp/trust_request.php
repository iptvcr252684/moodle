<?php

// this script may either be called directly, or from openid_helper
if (!defined('MOODLE_INTERNAL')) {
    require_once('../../config.php');
}

require_once($CFG->dirroot . '/local/openid_idp/lib/openid_helper.php');

require_once($CFG->dirroot.'/lib/formslib.php');

class trust_request_form extends moodleform {
    function definition() {
        $mform =& $this->_form;

        $url = s($this->_customdata['url']);
        $mform->addElement('static', '', '', get_string('trust_prompt', 'local_openid_idp', s($url)));

        $helper = openid_helper::get_instance();
        $extensions = $helper->get_extensions();
        foreach ($extensions as $extension) {
            if (function_exists('trust_request_form_'.$extension)) {
                call_user_func('trust_request_form_'.$extension, $mform, $this->_customdata);
            }
        }

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'always', get_string('trust_always', 'local_openid_idp'));
        $buttonarray[] = &$mform->createElement('submit', 'once', get_string('trust_once', 'local_openid_idp'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('trust_no', 'local_openid_idp'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}

require_login(null, false);
require_capability('local/openid_idp:logintoremote', get_context_instance(CONTEXT_SYSTEM));

$helper = openid_helper::get_instance();

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url($helper->make_url());
$PAGE->set_heading(get_string('pluginname', 'local_openid_idp'));
$PAGE->set_title(get_string('pluginname', 'local_openid_idp'));

if (!isset($request)) {
    if (isset($SESSION->openid_request)) {
        $request = unserialize($SESSION->openid_request);
    } else {
        print_error('no_request', 'local_openid_idp');
    }
}

//check requested identity (if specified)
$identity = $helper->make_url(array('action' => 'user', 'id' => $USER->id))->out(false);
if (!$request->idSelect() && $request->identity != $identity) {
    print_error('incorrect_identity', 'local_openid_idp');
}

$customdata = array();
$customdata['request'] = $request;
$customdata['url'] = $request->trust_root;
$form = new trust_request_form(new moodle_url('/local/openid_idp/trust_request.php'), $customdata);
if ($form->is_cancelled()) {
    unset($SESSION->openid_request);
    if (isset($request)) {
        $url = $request->getCancelURL();
    } else {
        $url = $CFG->wwwroot;
    }
    redirect($url);
} else if ($data = $form->get_data()) {
    if (!empty($data->always)) {
        // user wants to always trust the RP
        unset($data->always);
        $trust = new stdClass;
        $trust->url = $request->trust_root;
        $trust->userid = $USER->id;
        $trust->options = serialize($data);
        $DB->insert_record('local_openid_idp_trusted_rps', $trust);
    }

    $req_url = $helper->make_url(array('action' => 'user', 'id' => $USER->id));
    $response =& $request->answer(true, null, $req_url->out(false));

    $extensions = $helper->get_extensions();
    foreach ($extensions as $extension) {
        if (function_exists('trust_request_form_response_'.$extension)) {
            call_user_func('trust_request_form_response_'.$extension, $request, $response, $data, $USER);
        }
    }

    $helper->send_response($response);
    unset($SESSION->openid_request);
} else {
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}
