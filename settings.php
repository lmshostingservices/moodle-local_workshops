<?php
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
