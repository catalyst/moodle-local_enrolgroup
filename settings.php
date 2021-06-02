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
 * These are just a backport of the new enrolment database settings, so we're storing them in enrol_database.
 *
 * @package     local_enrolgroup
 * @category    admin
 * @copyright   2021 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) { // Needs this condition or there is error on login page.
    $settings = new admin_settingpage('local_enrolgroup', new lang_string('pluginname', 'local_enrolgroup'));
    $ADMIN->add('localplugins', $settings);
    // Settings relating to group synchronisation.
    // This includes:
    // - groupstable - the name of the table that describes the groups to create
    // -- groupscourseidnumber - the course idnumber which owns a group
    // -- groupsgroupidnumber - the idnumber for this group (unique within the course)
    // -- groupsname - the name of the group
    // -- removegroupsaction - how to handle group removal
    // - groupmemberstable - the name of the table that describes the mapping of users to groups
    // -- groupmemberscourseidnumber - the course idnumber which owns a group
    // -- groupmembersgroupidnumber - the idnumber for this group (unique within the course)
    // -- groupmembersuseridnumber - the idnumber of the user to put into this group
    $settings->add(
        new admin_setting_heading(
            'enrol_database_groupsync',
            get_string('settingsheadergroupsync', 'local_enrolgroup'),
            get_string('settingsheadergroupsync_desc', 'local_enrolgroup')
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_database/remotegroupstable',
            get_string('remotegroupstable', 'local_enrolgroup'),
            get_string('remotegroupstable_desc', 'local_enrolgroup'),
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_database/groupscourseidnumber',
            get_string('groupscourseidnumber', 'local_enrolgroup'),
            get_string('groupscourseidnumber_desc', 'local_enrolgroup'),
            'courseidnumber'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_database/groupsgroupidnumber',
            get_string('groupsgroupidnumber', 'local_enrolgroup'),
            get_string('groupsgroupidnumber_desc', 'local_enrolgroup'),
            'groupidnumber'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_database/groupsgroupname',
            get_string('groupsgroupname', 'local_enrolgroup'),
            get_string('groupsgroupname_desc', 'local_enrolgroup'),
            'groupname'
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'enrol_database/removegroupsaction',
            get_string('removegroupsaction', 'local_enrolgroup'),
            get_string('removegroupsaction_desc', 'local_enrolgroup'),
            // 1 = Keep unused groups.
            1,
            [
                0 => get_string('removegroupsaction_remove', 'local_enrolgroup'),
                1 => get_string('removegroupsaction_keep', 'local_enrolgroup'),
            ]
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_database/remotegroupmemberstable',
            get_string('remotegroupmemberstable', 'local_enrolgroup'),
            get_string('remotegroupmemberstable_desc', 'local_enrolgroup'),
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_database/groupmemberscourseidnumber',
            get_string('groupmemberscourseidnumber', 'local_enrolgroup'),
            get_string('groupmemberscourseidnumber_desc', 'local_enrolgroup'),
            'courseidnumber'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_database/groupmembersgroupidnumber',
            get_string('groupmembersgroupidnumber', 'local_enrolgroup'),
            get_string('groupmembersgroupidnumber_desc', 'local_enrolgroup'),
            'groupidnumber'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_database/groupmembersuseridnumber',
            get_string('groupmembersuseridnumber', 'local_enrolgroup'),
            get_string('groupmembersuseridnumber_desc', 'local_enrolgroup'),
            'useridnumber'
        )
    );

}
