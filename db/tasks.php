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
 * Task definition for local_enrolgroup
 * @package   local_enrolgroup
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\local_enrolgroup\task\sync_groups',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => 'R',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 1
    ],
    [
        'classname' => '\local_enrolgroup\task\sync_courses',
        'blocking' => 0,
        'minute' => '10',
        'hour' => '1',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 1
    ],
    [
       'classname' => '\local_enrolgroup\task\sync_enrolments',
        'blocking' => 0,
        'minute' => '50',
        'hour' => '1',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 1
    ]
];
