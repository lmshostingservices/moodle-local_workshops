<?php
// This file is part of Moodle - http://moodle.org/
defined('MOODLE_INTERNAL') || die();

/**
 * Seed default programs list on fresh install.
 */
function xmldb_local_workshops_install() {
    $defaultprograms = "ACA\nACS\nAEP\nAMHFA\nYAMHFA\nDeadly Thinking Adult\nDeadly Thinking Youth";
    set_config('programs', $defaultprograms, 'local_workshops');
    return true;
}
