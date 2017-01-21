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
 * Download a detailed list of all the users within a given course.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

require_once('../config.php');
require_once($CFG->dirroot . '/lib/dataformatlib.php');

$dataformat = optional_param('dataformat', '', PARAM_ALPHA);
$courseid = optional_param('courseid', $COURSE->id, PARAM_INT);
$roleid = optional_param('roleid', 5, PARAM_INT); // Default roleid = 5 = student.

$context = context_course::instance($courseid);
if (! $course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
require_login($course);

// TODO: change to a better capability,
require_capability('moodle/course:enrolreview', $context);

$columns = array(
    'idnumber' => get_string('idnumber'),
    'firstname' => get_string('firstname'),
    'lastname' => get_string('lastname'),
    'email' => get_string('email'),
    'phone1' => get_string('phone'),
    'city' => get_string('city'),
);

if ($roleid == 0) {
    $androle = '';
} else {
    $androle = ' AND roleid = ' . $roleid;
}

$sql = 'SELECT u.idnumber, u.firstname, u.lastname, u.email, u.phone1, u.city
FROM {course} AS c
JOIN {context} AS ctx ON c.id = ctx.instanceid
JOIN {role_assignments} AS ra ON ra.contextid = ctx.id
JOIN {user} AS u ON u.id = ra.userid
WHERE c.id=' . $courseid . $androle;

$rs = $DB->get_recordset_sql($sql);
download_as_dataformat('courseid_' . $courseid . '_participants', $dataformat, $columns, $rs);
$rs->close();