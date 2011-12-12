<?php

require_once($CFG->libdir . '/formslib.php');

class trustedrp_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;

        $strrequired = get_string('required');

        $helper = openid_helper::get_instance();

        if (isset($this->_customdata->url)) {
            $mform->addElement('static', 'url', get_string('rp_url', 'local_openid_idp'), s($this->_customdata->url));
        } else {
            $mform->addElement('text', 'url', get_string('rp_url', 'local_openid_idp'));
            $mform->setType('url', PARAM_URL);
            $mform->addRule('url', $strrequired, 'required', null, 'client');
        }

        $extensions = $helper->get_extensions();

        foreach ($extensions as $extension) {
            if (function_exists('trustedrp_form_'.$extension)) {
                call_user_func('trustedrp_form_'.$extension, $mform, $this->_customdata);
            }
        }

        $this->add_action_buttons();
    }
}
