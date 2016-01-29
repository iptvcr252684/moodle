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
 * Event to be triggered when a block is deleted.
 *
 * @package    core
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace core\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Class block_deleted
 *
 * Class for event to be triggered when a block is deleted.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string blockname: name of block deleted.
 *      - string instanceid: id of module instance.
 * }
 *
 * @package    core
 * @since      Moodle 3.0
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class block_deleted extends base {

    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'block';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventblockdeleted', 'moodle');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' deleted the '{$this->other['blockname']}' block with " .
            "context id '$this->contextinstanceid'.";
    }

    /**
     * Legacy event name.
     *
     * @return string legacy event name
     */
    public static function get_legacy_eventname() {
        return 'block_deleted';
    }

    /**
     * Legacy event data.
     *
     * @return \stdClass
     */
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename = $this->other['blockname'];
        $eventdata->cmid       = $this->objectid;
        $eventdata->courseid   = $this->courseid;
        $eventdata->userid     = $this->userid;
        return $eventdata;
    }

    /**
     * replace add_to_log() statement.
     *
     * @return array of parameters to be passed to legacy add_to_log() function.
     */
    protected function get_legacy_logdata() {
        return array ($this->courseid, "course", "delete block", "view.php?id=$this->courseid",
                $this->other['blockname'] . " " . $this->other['instanceid'], $this->objectid);
    }

    /**
     * custom validations
     *
     * Throw \coding_exception notice in case of any problems.
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['blockname'])) {
            throw new \coding_exception('The \'blockname\' value must be set in other.');
        }
        if (!isset($this->other['instanceid'])) {
            throw new \coding_exception('The \'instanceid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'block_instances', 'restore' => 'block_instances');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['instanceid'] = base::NOT_MAPPED;

        return $othermapped;
    }
}

