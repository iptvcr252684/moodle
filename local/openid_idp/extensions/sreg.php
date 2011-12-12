<?php

defined('MOODLE_INTERNAL') || die();

require_once('Auth/OpenID/SReg.php');

global $OPENID_SREG_MAP;
$OPENID_SREG_MAP = array(
    'fullname' => function ($user) { return fullname($user); },
    'nickname' => function ($user) { return $user->username; },
    'email' => function ($user) { return $user->email; },
    'country' => function ($user) { return $user->country; },
    'language' => function ($user) { return $user->lang; },
    'timezone' => function ($user) {
        global $CFG;
        return $user->timezone != 99 ? $user->timezone :
            $CFG->timezone != 99 ? $CFG->timezone : null;
    },
);

function filter_sreg_fields($fields) {
    return array_filter($fields,
                        function ($field) {
                            global $OPENID_SREG_MAP;
                            return isset($OPENID_SREG_MAP[$field]);
                        });
}

function sreg_get_required($args) {
    if (!isset($args['required'])) {
        return array();
    }

    $fields = explode(',', $args['required']);
    $fields = filter_sreg_fields($fields);
    return $fields;
}

function sreg_get_optional($args) {
    if (!isset($args['optional'])) {
        return array();
    }

    $fields = explode(',', $args['optional']);
    $fields = filter_sreg_fields($fields);
    return $fields;
}

function trust_request_form_sreg($mform, $customdata) {
    if (!isset($customdata['request'])) {
        return;
    }
    if (function_exists('trust_request_form_ax')) {
        // don't do anything if the AX extension is loaded -- AX will combine
        // SREG and AX form elements
        return;
    }

    $sreg_request = Auth_OpenID_SRegRequest::fromOpenIDRequest($customdata['request']);
    $requested = $sreg_request->allRequestedFields();
    if (empty($requested)) {
        return;
    }

    $mform->addElement('static', '', '', get_string('sreg_requested', 'local_openid_idp'));
    $args = $sreg_request->getExtensionArgs();

    // list the required fields
    $fields = sreg_get_required($args);
    if (!empty($fields)) {
        $fields = array_map(
            function ($field) {
                return get_string('sreg_field_'.$field, 'local_openid_idp');
            }, $fields);
        $mform->addElement('static', '', get_string('sreg_required', 'local_openid_idp'), implode(', ', $fields));
    }

    // list the optional fields with checkboxes to select which fields to send
    $fields = sreg_get_required($args);
    if (!empty($fields)) {
        $checkboxarray = array_map(
            function ($field) use ($mform) {
                return $mform->createElement('checkbox', 'sreg_'.$field, '', get_string('sreg_field_'.$field, 'local_openid_idp'));
            }, $fields);
        $mform->addGroup($checkboxarray, '', get_string('sreg_optional', 'local_openid_idp'), array('<br />'), false);
    }
}

function trust_request_form_response_sreg($request, $response, $data, $user) {
    global $OPENID_SREG_MAP;

    $sreg_request = Auth_OpenID_SRegRequest::fromOpenIDRequest($request);
    $requested = $sreg_request->allRequestedFields();
    if (empty($requested)) {
        return;
    }

    $sreg_data = array();

    $args = $sreg_request->getExtensionArgs();

    $fields = sreg_get_required($args);
    if (!empty($fields)) {
        foreach ($fields as $field) {
            $sreg_data[$field] = call_user_func($OPENID_SREG_MAP[$field], $user);
        }
    }

    $fields = sreg_get_optional($args);
    if (!empty($fields)) {
        foreach ($fields as $field) {
            if (!empty($data->{'sreg_'.$field})) {
                $sreg_data[$field] = call_user_func($OPENID_SREG_MAP[$field], $user);
            }
        }
    }
    $sreg_response = Auth_OpenID_SRegResponse::extractResponse($sreg_request, $sreg_data);
    $sreg_response->toMessage($response->fields);
}

function trustedrptable_init_sreg($table) {
    if (function_exists('trustedrptable_init_ax')) {
        // don't do anything if the AX extension is loaded -- AX will combine
        // SREG and AX form elements
        return;
    }
    $table->head[] = get_string('sreg_config_sent', 'local_openid_idp');
}

function trustedrptable_add_data_sreg($rp, $row) {
    global $OPENID_SREG_MAP;

    if (function_exists('trustedrptable_init_ax')) {
        // don't do anything if the AX extension is loaded -- AX will combine
        // SREG and AX form elements
        return;
    }

    $options = unserialize($rp->options);
    $fields = array();
    foreach ($OPENID_SREG_MAP as $field => $dummy) {
        if (!empty($options->{'sreg_'.$field})) {
            $fields[] = get_string('sreg_field_'.$field, 'local_openid_idp');
        }
    }
    $row->cells[] = implode(', ', $fields);
}

function trustedrp_form_sreg($mform, $customdata) {
    global $OPENID_SREG_MAP;

    if (function_exists('trustedrp_form_ax')) {
        // don't do anything if the AX extension is loaded -- AX will combine
        // SREG and AX form elements
        return;
    }

    $checkboxarray = array_map(
            function ($field) use ($mform) {
                return $mform->createElement('checkbox', 'sreg_'.$field, '', get_string('sreg_field_'.$field, 'local_openid_idp'));
            }, array_keys($OPENID_SREG_MAP));
    $mform->addGroup($checkboxarray, '', get_string('sreg_config_sent', 'local_openid_idp'), array('<br />'), false);
}
