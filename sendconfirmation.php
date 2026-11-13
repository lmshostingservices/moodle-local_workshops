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
 * Email the joining details (date/time, meeting link or venue) to every
 * participant on a workshop whose booking is Confirmed.
 *
 * Used after the meeting link is added or changed. Sends even when the
 * automatic Confirmed email setting is off, because it is a deliberate action.
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
$returnurl  = new moodle_url('/local/workshops/view.php', ['id' => $workshopid, 'tab' => 'participants']);

if ($workshop->deliverytype !== 'Face-to-Face' && empty($workshop->teamslink)) {
    redirect($returnurl, 'No emails sent: this workshop has no meeting link yet. Edit the workshop and add the '
        . get_string('teamslink', 'local_workshops') . ' first.', null, \core\output\notification::NOTIFY_WARNING);
}

core_php_time_limit::raise(300);

$participants = $DB->get_records('local_workshop_participants',
    ['workshopid' => $workshopid, 'bookingstatus' => 'Confirmed'], 'lastname, firstname');

if (!$participants) {
    redirect($returnurl, 'No emails sent: nobody on this workshop has a Confirmed booking.',
        null, \core\output\notification::NOTIFY_WARNING);
}

$sent = 0;
$problems = [];
foreach ($participants as $p) {
    $r = local_workshops_send_confirmed_email($workshop, $p, true);
    if ($r['code'] === 'sent') {
        $sent++;
    } else if ($r['message'] !== '') {
        $problems[] = $r['message'];
    }
}

$message = 'Joining details emailed to ' . $sent . ' Confirmed participant' . ($sent !== 1 ? 's' : '') . '.';
if ($problems) {
    $message .= ' ' . implode(' ', array_slice($problems, 0, 5));
}
redirect($returnurl, $message, null,
    $problems ? \core\output\notification::NOTIFY_WARNING : \core\output\notification::NOTIFY_SUCCESS);
