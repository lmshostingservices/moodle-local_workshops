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

// This file is part of Moodle - http://moodle.org/
defined('MOODLE_INTERNAL') || die();

/**
 * Generate a unique workshop code in WS-YYYYMMDD-NNN format.
 * @package    local_workshops
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */
function local_workshops_generate_code() {
    global $DB;
    $datePart = date('Ymd');
    $prefix = 'WS-' . $datePart . '-';
    $count = $DB->count_records_select('local_workshops', "workshopcode LIKE ?", [$prefix . '%']);
    $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    $code = $prefix . $seq;
    while ($DB->record_exists('local_workshops', ['workshopcode' => $code])) {
        $seq = str_pad((int)$seq + 1, 3, '0', STR_PAD_LEFT);
        $code = $prefix . $seq;
    }
    return $code;
}

/**
 * Create default tasks for a newly created workshop.
 */
function local_workshops_create_default_tasks($workshopid) {
    global $DB;
    $now = time();
    $tasks = [
        ['taskname' => 'Trainer Assigned',         'sortorder' => 10],
        ['taskname' => 'Venue Booked',              'sortorder' => 20],
        ['taskname' => 'Catering Ordered',          'sortorder' => 30],
        ['taskname' => 'Resources Uploaded',        'sortorder' => 40],
        ['taskname' => 'Participants Confirmed',    'sortorder' => 50],
        ['taskname' => 'Reminder Sent',             'sortorder' => 60],
        ['taskname' => 'Attendance Marked',         'sortorder' => 70],
        ['taskname' => 'Survey Sent',               'sortorder' => 80],
        ['taskname' => 'Certificates Issued',       'sortorder' => 90],
        ['taskname' => 'Client Report Generated',   'sortorder' => 100],
        ['taskname' => 'Invoice Sent',              'sortorder' => 110],
    ];
    foreach ($tasks as $task) {
        $record = (object)[
            'workshopid'    => $workshopid,
            'taskname'      => $task['taskname'],
            'taskstatus'    => 'Pending',
            'assignedto'    => null,
            'duedate'       => null,
            'completeddate' => null,
            'notes'         => '',
            'sortorder'     => $task['sortorder'],
        ];
        $DB->insert_record('local_workshop_tasks', $record);
    }
}

/**
 * Get participant count for a workshop.
 */
function local_workshops_participant_count($workshopid) {
    global $DB;
    return $DB->count_records('local_workshop_participants', ['workshopid' => $workshopid]);
}

/**
 * Get list of programs from settings.
 */
function local_workshops_get_programs() {
    $raw = get_config('local_workshops', 'programs');
    if (empty($raw)) {
        return ['ACA', 'ACS', 'AEP', 'AMHFA', 'YAMHFA', 'Deadly Thinking Adult', 'Deadly Thinking Youth'];
    }
    $programs = array_filter(array_map('trim', explode("\n", $raw)));
    return array_values($programs);
}

/**
 * Get workshop status options.
 */
function local_workshops_get_statuses() {
    return ['Draft', 'Planned', 'Scheduled', 'In Progress', 'Confirmed', 'Waitlist Open', 'Completed', 'Cancelled', 'Postponed'];
}

/**
 * Get delivery type options.
 */
function local_workshops_get_delivery_types() {
    return ['Face-to-Face', 'Webinar', 'Hybrid'];
}

/**
 * Get booking status options.
 */
function local_workshops_get_booking_statuses() {
    return ['Booked', 'Confirmed', 'Waitlisted', 'Cancelled', 'Attended', 'No Show'];
}

/**
 * Get document type options.
 */
function local_workshops_get_document_types() {
    return ['Venue Booking', 'Catering Order', 'Trainer Brief', 'Client Documentation', 'Workshop Resources', 'Other'];
}

/**
 * Get booking status badge CSS class.
 */
function local_workshops_booking_status_badge($status) {
    $classes = [
        'Booked'     => 'lw-badge lw-badge-bk-booked',
        'Confirmed'  => 'lw-badge lw-badge-bk-confirmed',
        'Waitlisted' => 'lw-badge lw-badge-bk-waitlisted',
        'Cancelled'  => 'lw-badge lw-badge-bk-cancelled',
        'Attended'   => 'lw-badge lw-badge-bk-attended',
        'No Show'    => 'lw-badge lw-badge-bk-noshow',
    ];
    $cls = isset($classes[$status]) ? $classes[$status] : 'lw-badge lw-badge-bk-booked';
    return '<span class="' . $cls . '">' . htmlspecialchars($status) . '</span>';
}

/**
 * Format file size for display.
 */
function local_workshops_format_filesize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

/**
 * Require the user to have view capability at system context.
 */
function local_workshops_require_view() {
    $context = context_system::instance();
    require_capability('local/workshops:view', $context);
}

/**
 * Require the user to have edit capability at system context.
 */
function local_workshops_require_edit() {
    $context = context_system::instance();
    require_capability('local/workshops:edit', $context);
}

/**
 * Get status badge HTML.
 */
function local_workshops_status_badge($status) {
    $classes = [
        'Draft'        => 'lw-badge lw-badge-draft',
        'Planned'      => 'lw-badge lw-badge-planned',
        'Scheduled'    => 'lw-badge lw-badge-scheduled',
        'In Progress'  => 'lw-badge lw-badge-inprogress',
        'Confirmed'    => 'lw-badge lw-badge-confirmed',
        'Waitlist Open'=> 'lw-badge lw-badge-waitlistopen',
        'Completed'    => 'lw-badge lw-badge-completed',
        'Cancelled'    => 'lw-badge lw-badge-cancelled',
        'Postponed'    => 'lw-badge lw-badge-postponed',
    ];
    $cls = isset($classes[$status]) ? $classes[$status] : 'lw-badge';
    return '<span class="' . $cls . '">' . htmlspecialchars($status) . '</span>';
}

/**
 * Mark a Moodle course as complete for a user when their workshop booking
 * status is set to "Attended".
 *
 * This is a best-effort call: if the course has no completion tracking enabled,
 * or the user is not enrolled, the function returns silently without error so
 * the AJAX save_participant response is never blocked.
 *
 * @param int $workshopid  ID of the local_workshops row.
 * @param int $userid      Moodle user ID of the participant.
 */
function local_workshops_mark_course_complete(int $workshopid, int $userid): void {
    global $CFG, $DB;

    $workshop = $DB->get_record('local_workshops', ['id' => $workshopid], 'id, courseid');
    if (!$workshop || empty($workshop->courseid)) {
        return; // No linked course — nothing to do.
    }

    $courseid = (int) $workshop->courseid;
    $course   = $DB->get_record('course', ['id' => $courseid]);
    if (!$course) {
        return;
    }

    require_once($CFG->libdir . '/completionlib.php');

    $completion = new \completion_info($course);
    if (!$completion->is_enabled()) {
        return; // Completion tracking not enabled on this course.
    }

    // Mark the course-level completion for this user.
    // completion_completion::mark_complete() is idempotent — safe to call
    // even if the user is already marked complete.
    $ccompletion = new \completion_completion(['userid' => $userid, 'course' => $courseid]);
    $ccompletion->mark_complete();
}

/**
 * Get delivery type badge HTML.
 */
function local_workshops_delivery_badge($type) {
    $classes = [
        'Face-to-Face' => 'lw-badge lw-badge-f2f',
        'Webinar'      => 'lw-badge lw-badge-webinar',
        'Hybrid'       => 'lw-badge lw-badge-hybrid',
    ];
    $cls = isset($classes[$type]) ? $classes[$type] : 'lw-badge';
    return '<span class="' . $cls . '">' . htmlspecialchars($type) . '</span>';
}
