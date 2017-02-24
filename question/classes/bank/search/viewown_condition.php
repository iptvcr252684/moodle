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
 * A search class which controls the display of question(s) created by current viewing user.
 *
 * @package   core_question
 * @copyright 2016 Nadav Kavalerchik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question\bank\search;
defined('MOODLE_INTERNAL') || die();

/**
 * This class controls view of question(s) created by current user.
 *
 * @copyright 2016 Nadav Kavalerchik
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class viewown_condition extends condition {

    /** @var string SQL fragment to add to the where clause. */
    protected $where;

    /**
     * Constructor.
     */
    public function __construct() {
        global $USER;
        $this->where = 'q.createdby = '.$USER->id;
    }

    /**
     * Returns WHERE conditions.
     * @return string the sql WHERE part of the query.
     */
    public function where() {
        return  $this->where;
    }

    /**
     * Print HTML to display a message indicating:
     * "In this category you can only see the questions you created"
     */
    public function display_options() {
        echo \html_writer::div(get_string('showownquestions', 'question'));
    }
}
