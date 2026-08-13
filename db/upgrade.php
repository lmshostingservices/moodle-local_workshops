<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_workshops_upgrade($oldversion) {
    if ($oldversion < 2026072400) {
        upgrade_plugin_savepoint(true, 2026072400, 'local', 'workshops');
    }
    return true;
}
