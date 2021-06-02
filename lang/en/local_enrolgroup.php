<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_enrolgroup
 * @category    string
 * @copyright   2021 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Enrol database with groups';
$string['groupmemberscourseidnumber'] = 'Course idnumber field for group membership';
$string['groupmemberscourseidnumber_desc'] = 'The name of the database column which contains the idnumber of the course that owns the group to synchronise';
$string['groupmembersgroupidnumber'] = 'Group idnumber field for group membership';
$string['groupmembersgroupidnumber_desc'] = 'The name of the database column which contains the idnumber of the group to synchronise';
$string['groupmembersuseridnumber'] = 'User idnumber field for group membership';
$string['groupmembersuseridnumber_desc'] = 'The name of the database column which contains the idnumber of the user to place into the group';
$string['groupscourseidnumber'] = 'Course idnumber field for groups';
$string['groupscourseidnumber_desc'] = 'The name of the database column which contains the idnumber of the course that owns the group to synchronise';
$string['groupsgroupidnumber'] = 'Group idnumber field';
$string['groupsgroupidnumber_desc'] = 'The name of the database column which contains the idnumber of the group to synchronise';
$string['groupsgroupname'] = 'Group name field';
$string['groupsgroupname_desc'] = 'The database column in the remote database which contains the name of the group to synchronise';
$string['usermembersuseridnumber_desc'] = 'The name of the database column which contains the idnumber of the user to be placed into a group';
$string['remotegroupstable'] = 'Remote group table';
$string['remotegroupstable_desc'] = 'Specify a table which contains a list of groups to create and manage within the course. If not specified then groups will not be synchronised.';
$string['remotegroupmemberstable'] = 'Remote group membership table';
$string['remotegroupmemberstable_desc'] = 'Specify the name of the table that map users to their group membership within a course. If not specified then group membership will not be synchronised.';
$string['removegroupsaction'] = 'Remote group removal action';
$string['removegroupsaction_remove'] = 'Remove unused groups';
$string['removegroupsaction_keep'] = 'Keep unused groups';
$string['removegroupsaction_desc'] = 'How to handle the case where a group has been removed from the remote system. Removal of groups should be used with caution as many activities store content against a specific group. Removal of groups is not reversible and can lead to inaccessible data.';
$string['settingsheadergroupsync'] = 'Remote group sync';
$string['settingsheadergroupsync_desc'] = 'You can synchronise a list of groups to be created within a course, and the membership of each group.';
$string['syncgroupstask'] = 'Sync enrolment groups';