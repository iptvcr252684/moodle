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
 * Admin settings class for the assignfeedback PDF plugin
 * subclassing admin_setting_configselect to lazy load the font list
 * from a folder on the server
 *
 * @package    assignfeedback_editpdf
 * @copyright  2013 Davo Smith
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class assignfeedback_editpdf_admin_setting_configselect extends admin_setting_configselect {
    /**
     * This function is used to lazy load list of font choices
     *
     * Override this method if loading of choices is expensive, such
     * as when it requires multiple db requests.
     *
     * @return bool true if loaded, false if error
     */
    public function load_choices() {
        global $CFG;
        require_once($CFG->libdir.'/pdflib.php');

        if (is_array($this->choices)) {
            return true;
        }

        // Select pdf editing font.
        $fonts = array();
        $filesindir = scandir(K_PATH_FONTS);
        foreach ($filesindir as $file) {
            if (substr($file, -4) != '.php') {
                continue;
            }
            $contents = file_get_contents(K_PATH_FONTS . $file);
            if (preg_match('/\$name\s*=\s*\'(.*)\';/', $contents, $fontname) !== 1) {
                continue;
            }
            $fonts[substr($file, 0, strlen($file) - 4)] = $fontname[1];
        }
        $this->choices = $fonts;
        return true;
    }
}
