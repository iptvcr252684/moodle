<?php

defined('MOODLE_INTERNAL') || die();

require_once('Auth/OpenID/AX.php');

global $OPENID_AX_MAP, $OPENID_AX_URI_MAP;
$OPENID_AX_MAP = array(
    'fullname' => function ($user) { return fullname($user); },
    'nickname' => function ($user) { return $user->username; },
    'timezone' => function ($user) {
        global $CFG;
        return $user->timezone != 99 ? $user->timezone :
            $CFG->timezone != 99 ? $CFG->timezone : null;
    },
    'language' => function ($user) { return $user->lang; },
    'phone' => function ($user) { return $user->phone1; },
    'mobilephone' => function ($user) { return $user->phone2; },
    'userid' => function ($user) { return $user->id; },
    'picturetimemodified' => function ($user) {
        $usericonfile = _ax_get_user_icon_file($user);
        if ($usericonfile) {
            return $usericonfile->get_timemodified();
        }
    },
    'picturemimetype' => function ($user) {
        $usericonfile = _ax_get_user_icon_file($user);
        if ($usericonfile) {
            return $usericonfile->get_mimetype();
        }
    },
    'sessiongclifetime' => function ($user) { return ini_get('session.gc_maxlifetime'); },
);

function _ax_get_user_icon_file($user) {
    $fs = get_file_storage();
    $usercontext = get_context_instance(CONTEXT_USER, $user->id, MUST_EXIST);
    if ($usericonfile = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f1.png')) {
        return $usericonfile;
    } else if ($usericonfile = $fs->get_file($usercontext->id, 'user', 'icon', 0, '/', 'f1.jpg')) {
        return $usericonfile;
    }
}

$OPENID_AX_URI_MAP = array(
    // axschema.org
    'http://axschema.org/namePerson/friendly' => 'nickname',
    'http://axschema.org/contact/email' => 'email',
    'http://axschema.org/contact/country/home' => 'country',
    'http://axschema.org/pref/language' => 'language',
    'http://axschema.org/pref/timezone' => 'timezone',
    'http://axschema.org/namePerson' => 'fullname',
    'http://axschema.org/namePerson/first' => 'firstname',
    'http://axschema.org/namePerson/last' => 'lastname',
    // xmlsoap.org identiy claims
    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname' => 'firstname',
    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname' => 'lastname',
    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress' => 'email',
    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/locality' => 'city',
    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/country' => 'country',
    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/homephone' => 'phone',
    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/mobilephone' => 'mobilephone',
    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/webpage' => 'url',
    // Moodle-specific fields (NOTE: unofficial and may change without notice.
    // Do not use unless you are prepared to deal with breakages.)
    /*
    'http://schemas.moodle.org/user/policyagreed' => 'policyagreed',
    'http://schemas.moodle.org/user/suspended' => 'suspended',
    'http://schemas.moodle.org/user/idnumber' => 'idnumber',
    'http://schemas.moodle.org/user/contact/email/emailstop' => 'emailstop',
    'http://schemas.moodle.org/user/contact/icq' => 'icq',
    'http://schemas.moodle.org/user/contact/skype' => 'skype',
    'http://schemas.moodle.org/user/contact/yahoo' => 'yahoo',
    'http://schemas.moodle.org/user/contact/aim' => 'aim',
    'http://schemas.moodle.org/user/contact/msn' => 'msn',
    'http://schemas.moodle.org/user/contact/institution' => 'institution',
    'http://schemas.moodle.org/user/contact/department' => 'department',
    'http://schemas.moodle.org/user/contact/address' => 'address',
    'http://schemas.moodle.org/user/firstaccess' => 'firstaccess',
    'http://schemas.moodle.org/user/lastaccess' => 'lastaccess',
    'http://schemas.moodle.org/user/lastlogin' => 'lastlogin',
    'http://schemas.moodle.org/user/currentlogin' => 'currentlogin',
    'http://schemas.moodle.org/user/secret' => 'secret',
    'http://schemas.moodle.org/user/picture' => 'picture',
    'http://schemas.moodle.org/user/description' => 'description',
    'http://schemas.moodle.org/user/description/format' => 'descriptionformat',
    'http://schemas.moodle.org/user/contact/email/mailformat' => 'mailformat',
    'http://schemas.moodle.org/user/contact/email/maildigest' => 'maildigest',
    'http://schemas.moodle.org/user/pref/maildisplay' => 'maildisplay',
    'http://schemas.moodle.org/user/pref/htmleditor' => 'htmleditor',
    'http://schemas.moodle.org/user/pref/ajax' => 'ajax',
    'http://schemas.moodle.org/user/pref/forum/autosubscribe' => 'autosubscribe',
    'http://schemas.moodle.org/user/pref/forum/track' => 'trackforums',
    'http://schemas.moodle.org/user/trustbitmap' => 'trustbitmap',
    'http://schemas.moodle.org/user/imagealt' => 'imagealt',
    'http://schemas.moodle.org/user/pref/screenreader' => 'screenreader',
    'http://schemas.moodle.org/session/gc_lifetime' => 'sessiongclifetime',
    'http://schemas.moodle.org/user/picture/timemodified' => 'picturetimemodified',
    'http://schemas.moodle.org/user/picture/mimetype' => 'picturemimetype',
    'http://schemas.moodle.org/user/id' => 'userid',
    */
);

function filter_ax_uris($fields) {
    return array_filter($fields,
                        function ($field) {
                            global $OPENID_AX_URI_MAP;
                            return isset($OPENID_AX_URI_MAP[$field]);
                        });
}

function ax_attr_to_uri($args, $fields) {
    return array_map(
        function ($field) use ($args) {
            return $args['type.' . $field];
        }, $fields);
}

function ax_uri_to_shortname($fields) {
    return array_map(
        function ($field) {
            global $OPENID_AX_URI_MAP;
            return $OPENID_AX_URI_MAP[$field];
        }, $fields);
}

function ax_get_required_shortname($args, $sreg_required) {
    if (!isset($args['required'])) {
        return array();
    }

    $fields = explode(',', $args['required']);
    $fields = ax_attr_to_uri($args, $fields);
    $fields = filter_ax_uris($fields);
    $fields = array_unique(ax_uri_to_shortname($fields));
    $fields = array_diff($fields, $sreg_required);
    return $fields;
}

function ax_get_optional_shortname($args, $ax_required, $sreg_required, $sreg_optional) {
    if (!isset($args['if_available'])) {
        return array();
    }

    $fields = explode(',', $args['if_available']);
    $fields = ax_attr_to_uri($args, $fields);
    $fields = filter_ax_uris($fields);
    $fields = array_unique(ax_uri_to_shortname($fields));
    $fields = array_diff($fields, $ax_required, $sreg_required, $sreg_optional);
    return $fields;
}

function get_ax_value($shortname, $user) {
    global $OPENID_AX_MAP;
    if (isset($OPENID_AX_MAP[$shortname])) {
        $value = call_user_func($OPENID_AX_MAP[$shortname], $user);
    } else {
        $value = $user->$shortname;
    }
    if (empty($value)) {
        return;
    }
    if (!is_array($value)) {
        $value = array($value);
    }
    return $value;
}

function trust_request_form_ax($mform, $customdata) {
    if (!isset($customdata['request'])) {
        return;
    }

    // get the requested SReg fields
    if (function_exists('sreg_get_required')) {
        $sreg_request = Auth_OpenID_SRegRequest::fromOpenIDRequest($customdata['request']);
        $sreg_args = $sreg_request->getExtensionArgs();
        $sreg_required = sreg_get_required($sreg_args);
        $sreg_optional = sreg_get_optional($sreg_args);
    } else {
        $sreg_required = $sreg_optional = array();
    }

    // get the requested AX fields
    $ax_request = Auth_OpenID_AX_FetchRequest::fromOpenIDRequest($customdata['request']);
    if (Auth_OpenID_AX::isError($ax_request)) {
        $ax_required = array();
        $ax_optional = array();
    } else {
        $ax_args = $ax_request->getExtensionArgs();
        $ax_required = ax_get_required_shortname($ax_args, $sreg_required);
        $ax_optional = ax_get_optional_shortname($ax_args, $ax_required, $sreg_required, $sreg_optional);
    }

    if (empty($sreg_required) && empty($sreg_optional) && empty($ax_required) && empty($ax_optional)) {
        return;
    }

    $mform->addElement('static', '', '', get_string('sreg_requested', 'local_openid_idp'));

    // list the required fields
    if (!empty($sreg_required) || !empty($ax_required)) {
        $required = array_map(
            function ($field) {
                return get_string('ax_field_'.$field, 'local_openid_idp');
            }, array_merge($sreg_required, $ax_required));
        $mform->addElement('static', '', get_string('sreg_required', 'local_openid_idp'), implode(', ', $required));
    }

    // list the optional fields with checkboxes to select which fields to send
    if (!empty($sreg_optional) || !empty($ax_optional)) {
        $checkboxarray = array_map(
            function ($field) use ($mform) {
                // use 'sreg_' prefix so that the sreg extension can use the
                // same data
                return $mform->createElement('checkbox', 'sreg_'.$field, '', get_string('ax_field_'.$field, 'local_openid_idp'));
            }, array_merge($sreg_optional, $ax_optional));
        $mform->addGroup($checkboxarray, '', get_string('sreg_optional', 'local_openid_idp'), array('<br />'), false);
    }
}

function trust_request_form_response_ax($request, $response, $data, $user) {
    global $OPENID_AX_URI_MAP, $USER;

    $ax_request = Auth_OpenID_AX_FetchRequest::fromOpenIDRequest($request);

    if (Auth_OpenID_AX::isError($ax_request)) {
        return;
    }

    $args = $ax_request->getExtensionArgs();

    if (empty($args['required']) && empty($args['if_available'])) {
        return;
    }

    $ax_response = new Auth_OpenID_AX_FetchResponse;

    $fields = explode(',', $args['required']);
    $fields = ax_attr_to_uri($args, $fields);
    $fields = filter_ax_uris($fields);
    foreach ($fields as $uri) {
        $shortname = $OPENID_AX_URI_MAP[$uri];
        $value = get_ax_value($shortname, $user);
        if ($value !== null) {
            $ax_response->setValues($uri, $value);
        }
    }

    $fields = explode(',', $args['if_available']);
    $fields = ax_attr_to_uri($args, $fields);
    $fields = filter_ax_uris($fields);
    foreach ($fields as $uri) {
        $shortname = $OPENID_AX_URI_MAP[$uri];
        if (!empty($data->{'sreg_'.$shortname})) {
            $value = get_ax_value($shortname, $user);
            if ($value !== null) {
                $ax_response->setValues($uri, $value);
            }
        }
    }

    $ax_response->toMessage($response->fields);
}

function trustedrptable_init_ax($table) {
    $table->head[] = get_string('sreg_config_sent', 'local_openid_idp');
}

function trustedrptable_add_data_ax($rp, $row) {
    global $OPENID_AX_URI_MAP;
    $known_fields = array_unique($OPENID_AX_URI_MAP);
    $options = unserialize($rp->options);
    $fields = array();
    foreach ($known_fields as $field) {
        if (!empty($options->{'sreg_'.$field})) {
            $fields[] = get_string('ax_field_'.$field, 'local_openid_idp');
        }
    }
    $row->cells[] = implode(', ', $fields);
}

function trustedrp_form_ax($mform, $customdata) {
    global $OPENID_AX_URI_MAP;
    $known_fields = array_unique($OPENID_AX_URI_MAP);

    $checkboxarray = array_map(
            function ($field) use ($mform) {
                return $mform->createElement('checkbox', 'sreg_'.$field, '', get_string('ax_field_'.$field, 'local_openid_idp'));
            }, $known_fields);
    $mform->addGroup($checkboxarray, '', get_string('sreg_config_sent', 'local_openid_idp'), array('<br />'), false);
}
