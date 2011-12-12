<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/local/openid_idp/lib/openid_helper.php');

$action = optional_param('action', 'request', PARAM_ACTION);

$helper = openid_helper::get_instance();
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url($helper->make_url(array('action' => $action)));

$methodname = 'do_'.$action;
if (method_exists($helper, $methodname)) {
    call_user_func(array($helper, $methodname));
} else {
    print_error('unknownaction', 'local_openid_idp');
}
