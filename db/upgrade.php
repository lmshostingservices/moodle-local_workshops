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
 * Upgrade steps for local_workshops.
 *
 * @package    local_workshops
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion The version being upgraded from.
 * @return bool
 */
function xmldb_local_workshops_upgrade($oldversion) {
    if ($oldversion < 2026072400) {
        upgrade_plugin_savepoint(true, 2026072400, 'local', 'workshops');
    }

    if ($oldversion < 2026102300) {
        // v1.0.33: linked-course access settings. Set defaults now so enrolment
        // works straight after upgrade, before anyone saves the settings page.
        if (get_config('local_workshops', 'autoenrol') === false) {
            set_config('autoenrol', 1, 'local_workshops');
        }
        if (get_config('local_workshops', 'autocreateusers') === false) {
            set_config('autocreateusers', 1, 'local_workshops');
        }
        if (get_config('local_workshops', 'attendedgroupname') === false) {
            set_config('attendedgroupname', 'Attended workshop', 'local_workshops');
        }
        upgrade_plugin_savepoint(true, 2026102300, 'local', 'workshops');
    }

    if ($oldversion < 2026110901) {
        // v1.0.35: Attended email defaults (on), so it works before the settings page is saved.
        if (get_config('local_workshops', 'attendedemail') === false) {
            set_config('attendedemail', 1, 'local_workshops');
        }
        if (get_config('local_workshops', 'attendedemailsubject') === false) {
            set_config('attendedemailsubject', get_string('attendedemailsubject_default', 'local_workshops'), 'local_workshops');
        }
        if (get_config('local_workshops', 'attendedemailbody') === false) {
            set_config('attendedemailbody', get_string('attendedemailbody_default', 'local_workshops'), 'local_workshops');
        }
        upgrade_plugin_savepoint(true, 2026110901, 'local', 'workshops');
    }

    if ($oldversion < 2026110902) {
        // v1.0.37: reworded default attendance email. Replace only if the admin hasn't edited it.
        $olddefault = "Hi {firstname},\n\nThank you for attending {workshopname} on {workshopdate}.\n\n"
            . "The next step is to complete the survey and assessment for {coursename}. You can open the course here:\n\n"
            . "{courseurl}\n\nYour username is: {username}\n\n"
            . "If this is your first time logging in, your login details are emailed to you separately. "
            . "If you have forgotten your password, you can reset it here:\n{forgotpasswordurl}\n\nKind regards,\n{sitename}";
        $current = get_config('local_workshops', 'attendedemailbody');
        if ($current === false || str_replace("\r\n", "\n", trim($current)) === $olddefault) {
            set_config('attendedemailbody', get_string('attendedemailbody_default', 'local_workshops'), 'local_workshops');
        }
        upgrade_plugin_savepoint(true, 2026110902, 'local', 'workshops');
    }

    return true;
}
