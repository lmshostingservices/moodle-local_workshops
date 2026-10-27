<?php
defined('MOODLE_INTERNAL') || die();

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

    return true;
}
