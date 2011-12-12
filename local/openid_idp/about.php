<?php
if (!defined('MOODLE_INTERNAL')) {
    require_once('../../config.php');
}

require_once($CFG->dirroot . '/local/openid_idp/lib/openid_helper.php');

$helper = openid_helper::get_instance();

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url($helper->make_url());
$PAGE->set_heading(get_string('pluginname', 'local_openid_idp'));
$PAGE->set_title(get_string('pluginname', 'local_openid_idp'));
echo $OUTPUT->header();
print_string('description', 'local_openid_idp');
echo $OUTPUT->footer();
