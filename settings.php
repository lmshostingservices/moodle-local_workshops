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
 * local_workshops file.
 *
 * @package    local_workshops
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

// This file is part of Moodle - http://moodle.org/
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings = new admin_settingpage(
        'local_workshops_settings',
        get_string('pluginname', 'local_workshops')
    );

    $settings->add(new admin_setting_heading(
        'local_workshops/general',
        get_string('settings', 'local_workshops'),
        ''
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_workshops/programs',
        get_string('programs_setting', 'local_workshops'),
        get_string('programs_setting_desc', 'local_workshops'),
        "ACA\nACS\nAEP\nAMHFA\nYAMHFA\nDeadly Thinking Adult\nDeadly Thinking Youth",
        PARAM_TEXT
    ));

    $ADMIN->add('localplugins', $settings);
    $settings = null; // Prevent Moodle's post-include auto-add from inserting a duplicate node.

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_workshops_manage',
        get_string('manageworkshops', 'local_workshops'),
        new moodle_url('/local/workshops/manage.php'),
        'local/workshops:view'
    ));

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_workshops_create',
        get_string('createworkshop', 'local_workshops'),
        new moodle_url('/local/workshops/edit.php'),
        'local/workshops:create'
    ));

}
