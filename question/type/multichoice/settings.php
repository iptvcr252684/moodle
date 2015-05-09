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
 * Admin settings for the multichoice question type.
 *
 * @package   qtype_multichoice
 * @copyright  2015 onwards Nadav Kavalerchik
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/settingslib.php');

if ($ADMIN->fulltree) {
    $menu = array(
        get_string('answersingleno', 'qtype_multichoice'),
        get_string('answersingleyes', 'qtype_multichoice'),
    );
    $settings->add(new admin_setting_configselect('qtype_multichoice/answerhowmany',
        get_string('answerhowmany', 'qtype_multichoice'),
        get_string('answerhowmany_desc', 'qtype_multichoice'), '1', $menu));

    $settings->add(new admin_setting_configcheckbox('qtype_multichoice/shuffleanswers',
        get_string('shuffleanswers', 'qtype_multichoice'),
        get_string('shuffleanswers_desc', 'qtype_multichoice'), '1'));

    $settings->add(new qtype_multichoice_admin_setting_answernumbering('qtype_multichoice/answernumbering',
        get_string('answernumbering', 'qtype_multichoice'),
        get_string('answernumbering_desc', 'qtype_multichoice'), 'abc', null ));

}
