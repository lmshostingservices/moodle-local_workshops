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
    global $DB;
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

    if ($oldversion < 2026111300) {
        // v1.0.40: Confirmed email defaults (on), so it works before the settings page is saved.
        if (get_config('local_workshops', 'confirmedemail') === false) {
            set_config('confirmedemail', 1, 'local_workshops');
        }
        if (get_config('local_workshops', 'confirmedemailsubject') === false) {
            set_config('confirmedemailsubject', get_string('confirmedemailsubject_default', 'local_workshops'), 'local_workshops');
        }
        if (get_config('local_workshops', 'confirmedemailbody') === false) {
            set_config('confirmedemailbody', get_string('confirmedemailbody_default', 'local_workshops'), 'local_workshops');
        }
        upgrade_plugin_savepoint(true, 2026111300, 'local', 'workshops');
    }

    if ($oldversion < 2026111800) {
        // v1.0.41: extra sessions (2 and 3) for multi-day webinars.
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_workshop_sessions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('workshopid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sessionno', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startdatetime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enddatetime', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('meetingurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('platform', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('meetingid', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('passcode', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fk_workshopid', XMLDB_KEY_FOREIGN, ['workshopid'], 'local_workshops', ['id']);
        $table->add_index('uix_workshop_session', XMLDB_INDEX_UNIQUE, ['workshopid', 'sessionno']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Calendar attachment on by default.
        if (get_config('local_workshops', 'confirmedemailics') === false) {
            set_config('confirmedemailics', 1, 'local_workshops');
        }
        // New default body adds {calendarnote}. Replace only if the admin hasn't edited it.
        $olddefault = "Hi {firstname},\n\nYour place in {workshopname} is confirmed.\n\nWhen: {workshopwhen}\n\n"
            . "{joiningdetails}\n\nPlease join a few minutes before the start time.\n\nKind regards,\n{sitename}";
        $current = get_config('local_workshops', 'confirmedemailbody');
        if ($current === false || str_replace("\r\n", "\n", trim($current)) === $olddefault) {
            set_config('confirmedemailbody', get_string('confirmedemailbody_default', 'local_workshops'), 'local_workshops');
        }
        upgrade_plugin_savepoint(true, 2026111800, 'local', 'workshops');
    }

    return true;
}
