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
 * Sync enrolment groups helper.
 * @package   local_enrolgroup
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enrolgroup\task;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/group/lib.php');
use progress_trace;
use ADOConnection;
use core_php_time_limit;
use stdClass;

/**
 * Sync enrolment groups helper
 * @package   local_enrolgroup
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper extends \enrol_database_plugin {
    /**
     * Perform a sync of all groups from the remote sytem.
     *
     * @param   progress_trace $trace
     */

    public function get_name() {
        return 'database';
    }

    public function sync_groups(progress_trace $trace): void {
        global $CFG, $DB;

        // We do not create courses here intentionally because it requires full sync and is slow.
        if (!$this->is_group_sync_enabled()) {
            $trace->output('Course group synchronisation skipped.');
            $trace->finished();
            return;
        }

        $trace->output('Starting group synchronisation...');

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return;
        }

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $table          = $this->get_config('remotegroupstable');
        $coursefield    = trim($this->get_config('groupscourseidnumber'));

        // Fetch a list of all courses in the remote system which have any groups to synchronise.
        $courseswithgroups = [];
        $sql = $this->db_get_sql_helper($table, [], [$coursefield], true);
        $rs = $extdb->Execute($sql);

        if (!$rs) {
            $trace->output('Error reading data from the external enrolment table');
            $extdb->Close();
            return;
        }

        if (!$rs->EOF) {
            while ($idnumber = $rs->FetchRow()) {
                $idnumber = reset($idnumber);
                $idnumber = $this->db_decode($idnumber);
                if (empty($idnumber)) {
                    // invalid mapping
                    continue;
                }
                $courseswithgroups[$idnumber] = $idnumber;
            }
        }
        $rs->Close();

        // Map each of the courses to sync to a Moodle course id.
        $sql = <<<EOF
SELECT idnumber, id FROM {course} WHERE idnumber <> ''
EOF;
        $courseswithidnumbers = $DB->get_records_sql_menu($sql);

        $coursestosync = array_intersect_key(
            $courseswithidnumbers,
            $courseswithgroups
        );

        // For each course perform the sync.
        foreach ($coursestosync as $courseidnumber => $courseid) {
            $this->sync_groups_for_course($trace, $extdb, $courseid, $courseidnumber);
        }
    }

    protected function sync_groups_for_course(
        progress_trace $trace,
        ADOConnection $extdb,
        int $courseid,
        string $courseidnumber
    ): void {
        global $DB;
        $localcourse = get_course($courseid);

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $table          = $this->get_config('remotegroupstable');
        $coursefield    = trim($this->get_config('groupscourseidnumber'));
        $idnumberfield  = trim($this->get_config('groupsgroupidnumber'));
        $namefield      = trim($this->get_config('groupsgroupname'));

        // Fetch a list of all courses in the remote system for this course.
        $params = [
            $coursefield => $courseidnumber,
        ];

        $fields = [
            $coursefield => 'course',
            $idnumberfield => 'idnumber',
            $namefield => 'name',
        ];
        $sql = $this->db_get_sql_helper($table, $params, $fields);
        $rs = $extdb->Execute($sql);

        if (!$rs) {
            $trace->output('Error reading data from the external enrolment table');
            $extdb->Close();
            return;
        }
        // Get enrol instance to use in group
        $instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'database'));
        if (empty($instance)) {
            $trace->output('Error getting enrolment instance from courseid:'.$courseid);
            return;
        }

        // Fetch all remote groups.
        $remotegroups = [];
        if (!$rs->EOF) {
            while ($fields = $rs->FetchRow()) {
                $fields = array_change_key_case($fields, CASE_LOWER);
                $fields = (object) $this->db_decode($fields);

                $name = $fields->name;
                $idnumber = $fields->idnumber;

                if (empty($name) || empty($idnumber)) {
                    continue;
                }

                $remotegroups[$idnumber] = $name;
            }
        }
        $rs->Close();

        // Fetch all local groups.
        $localgroups = [];
        foreach (groups_get_course_data($courseid)->groups as $group) {
            if (empty($group->idnumber)) {
                continue;
            }
            $localgroups[$group->idnumber] = $group;
        }

        // Process groups.
        foreach ($remotegroups as $idnumber => $name) {
            $data = (object) [
                'idnumber' => $idnumber,
                'name' => $name,
                'courseid' => $courseid,
                'component' => 'enrol_database',
                'itemid' => $instance->id,
            ];
            if (array_key_exists($idnumber, $localgroups)) {
                // Check whether the group should be updated
                $localgroup = $localgroups[$idnumber];
                if ($localgroup->name !== $name) {
                    // Update it.
                    $trace->output("Updating group {$name} in {$courseid} with idnumber {$idnumber}");
                    groups_update_group($data);
                }
            } else {
                // Create the group.
                $trace->output("Creating group {$name} in {$courseid} with idnumber {$idnumber}");
                $localgroupid = groups_create_group($data);
                $localgroup = groups_get_group($localgroupid);
            }

            // Update group memberships for this group.
            $this->sync_group_memberships_for_group($trace, $extdb, $localcourse, $localgroup);

            // Unset the remote group to reduce memory.
            unset($remotegroups[$idnumber]);

            // Unset the local group
            unset($localgroups[$idnumber]);
        }

        // Remove any local groups which are not known about.
        $removegroups = $this->get_config('removegroups');
        if (empty($removegroups)) {
            foreach (array_values($localgroups) as $group) {
                $trace->output("Deleting group {$name} from {$courseid} with idnumber {$idnumber}");
                groups_delete_group($group);
            }
        } else {
            $trace->output("Not deleting groups");
        }
    }

    protected function sync_group_memberships_for_group(
        progress_trace $trace,
        ADOConnection $extdb,
        stdClass $localcourse,
        stdClass $localgroup
    ): void {
        global $DB;
        $trace->output('Starting group membershipsynchronisation...');

        if (!$this->is_group_membership_sync_enabled()) {
            $trace->output('Course group membership synchronisation skipped.');
            $trace->finished();
            return;
        }

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $table          = $this->get_config('remotegroupmemberstable');
        $coursefield    = trim($this->get_config('groupmemberscourseidnumber'));
        $idnumberfield  = trim($this->get_config('groupmembersgroupidnumber'));
        $userfield      = trim($this->get_config('groupmembersuseridnumber'));

        // Fetch membership in the remote system for this group.
        $courseswithgroups = [];
        $params = [
            $coursefield => $localcourse->idnumber,
            $idnumberfield => $localgroup->idnumber,
        ];

        $fields = [
            $coursefield => 'courseidnumber',
            $idnumberfield => 'groupidnumber',
            $userfield => 'useridnumber',
        ];
        $sql = $this->db_get_sql_helper($table, $params, $fields);
        $rs = $extdb->Execute($sql);

        if (!$rs) {
            $trace->output('Error reading data from the external enrolment table');
            $extdb->Close();
            return;
        }

        $remotemembers = [];
        if (!$rs->EOF) {
            while ($fields = $rs->FetchRow()) {
                $fields = array_change_key_case($fields, CASE_LOWER);
                $fields = (object) $this->db_decode($fields);

                $useridnumber = $fields->useridnumber;
                if (empty($useridnumber)) {
                    continue;
                }

                $remotemembers[$useridnumber] = $useridnumber;
            }
        }
        $rs->Close();


        $coursecontext = \context_course::instance($localcourse->id);

        $possiblelocalusers = get_enrolled_users($coursecontext, '', 0, 'u.idnumber, u.id, u.username, u.auth', 'u.username ASC');

        // Process group memberships.
        foreach ($possiblelocalusers as $useridnumber => $localuser) {
            if (array_key_exists($useridnumber, $remotemembers)) {
                // Add this user to the group.
                if (!groups_is_member($localgroup->id, $localuser->id)) {
                    $trace->output("Adding {$localuser->id} ({$useridnumber}) to {$localgroup->name}");
                    groups_add_member($localgroup, $localuser->id, 'enrol_database', 0);
                }
            } else if (groups_is_member($localgroup->id, $localuser->id)) {
                $trace->output("Removing {$localuser->id} ({$useridnumber}) from {$localgroup->name}");
                groups_remove_member($localgroup, $localuser->id);
            }
        }
    }

    /**
     * Check whether the group sync configuration is complete enough to allow group sync.
     *
     * @return  bool
     */
    protected function is_group_sync_enabled(): bool {
        if (!$this->get_config('dbtype')) {
            return false;
        }

        if (empty($this->get_config('remotegroupstable'))) {
            return false;
        }

        if (empty($this->get_config('groupscourseidnumber'))) {
            return false;
        }

        if (empty($this->get_config('groupsgroupidnumber'))) {
            return false;
        }

        return true;
    }

    /**
     * Check whether the group membership sync configuration is complete enough to allow sync.
     *
     * @return  bool
     */
    protected function is_group_membership_sync_enabled(): bool {
        if (!$this->get_config('dbtype')) {
            return false;
        }

        if (empty($this->get_config('remotegroupstable'))) {
            return false;
        }

        if (empty($this->get_config('remotegroupmemberstable'))) {
            return false;
        }

        if (empty($this->get_config('groupmemberscourseidnumber'))) {
            return false;
        }

        if (empty($this->get_config('groupmembersgroupidnumber'))) {
            return false;
        }

        if (empty($this->get_config('groupmembersuseridnumber'))) {
            return false;
        }

        return true;
    }

    /**
     * Slightly modified db_get_sql() for group sync.
     *
     * @param string $table
     * @param array $conditions
     * @param array $fieldlist
     * @param false $distinct
     * @param string $sort
     * @return string
     */
    protected function db_get_sql_helper($table, array $conditions, array $fieldlist, $distinct = false, $sort = "") {
        $fields = [];
        foreach ($fieldlist as $index => $field) {
            if (is_int($index)) {
                $fields[] = $field;
            } else {
                $fields[] = "{$index} AS {$field}";
            }
        }
        $fields = !empty($fields) ? implode(',', $fields) : "*";
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key=>$value) {
                $value = $this->db_encode($this->db_addslashes($value));

                $where[] = "$key = '$value'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return $sql;
    }
}