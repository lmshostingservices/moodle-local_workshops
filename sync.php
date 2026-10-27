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
 * Sync all bookings on a workshop to its linked Moodle course.
 *
 * Creates missing accounts (if enabled), enrols as Student, adds to the
 * workshop group, and keeps the "attended" group in step with booking status.
 * Safe to run repeatedly.
 *
 * @package    local_workshops
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/workshops/lib.php');

require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/workshops:manageparticipants', $context);

$workshopid = required_param('workshopid', PARAM_INT);
$workshop   = $DB->get_record('local_workshops', ['id' => $workshopid], '*', MUST_EXIST);

$summary = local_workshops_sync_workshop($workshop);

$problems = !empty($summary['nouser']) || !empty($summary['nopermission']) || !empty($summary['error']);

redirect(
    new moodle_url('/local/workshops/view.php', ['id' => $workshopid, 'tab' => 'participants']),
    local_workshops_sync_summary($summary),
    null,
    $problems ? \core\output\notification::NOTIFY_WARNING : \core\output\notification::NOTIFY_SUCCESS
);
