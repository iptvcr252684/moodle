<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('authsettings', new admin_externalpage('openid_idp',
            get_string('pluginname', 'local_openid_idp'),
            new moodle_url('/local/openid_idp/config.php')));
}
