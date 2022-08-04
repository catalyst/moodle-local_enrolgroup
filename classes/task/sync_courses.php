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
 * Sync enrolment groups task.
 * @package   local_enrolgroup
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enrolgroup\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Sync enrolment groups task.
 * @package   local_enrolgroup
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_courses extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('sync_coursestask', 'local_enrolgroup');
    }

    /**
     * Run task for synchronising users.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/database/lib.php');
        $trace = new \text_progress_trace();

        if (!enrol_is_enabled('database')) {
            $trace->output('Plugin not enabled');
            return;
        }

        $enrol = enrol_get_plugin('database');
        return $enrol->sync_courses($trace);
    }
}
