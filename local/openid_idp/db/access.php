<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/openid_idp:logintoremote' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW,
        )
    ),
);
