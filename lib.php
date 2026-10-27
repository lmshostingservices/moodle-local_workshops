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

/** Group idnumber prefix for per-workshop cohort groups. */
define('LOCAL_WORKSHOPS_GROUP_PREFIX', 'lw_ws_');

/** Group idnumber for the course-level "attended" group used to gate survey/assessment. */
define('LOCAL_WORKSHOPS_ATTENDED_IDNUMBER', 'lw_attended');

/**
 * Booking statuses that give a learner access to the linked Moodle course.
 *
 * @return string[]
 */
function local_workshops_enrolling_statuses(): array {
    return ['Booked', 'Confirmed', 'Attended'];
}

/**
 * Find an active local Moodle user by email (case-insensitive).
 *
 * Returns the oldest non-suspended match first, so duplicate-email sites behave
 * predictably instead of throwing (get_record() fails on multiple matches).
 *
 * @param string $email
 * @return stdClass|null
 */
function local_workshops_find_user_by_email(string $email): ?stdClass {
    global $CFG, $DB;
    $email = trim($email);
    if ($email === '') {
        return null;
    }
    $select = $DB->sql_equal('email', ':email', false) . ' AND deleted = 0 AND mnethostid = :mnethostid';
    $users = $DB->get_records_select('user', $select,
        ['email' => $email, 'mnethostid' => $CFG->mnet_localhost_id], 'suspended ASC, id ASC', '*', 0, 1);
    return $users ? reset($users) : null;
}

/**
 * Create a manual-auth Moodle account for a workshop participant.
 *
 * The password is generated and emailed by Moodle's "send new user passwords"
 * scheduled task (same mechanism as Upload users → "Create password if needed
 * and send via email"), and the user is forced to change it on first login.
 *
 * @param string $firstname
 * @param string $lastname
 * @param string $email
 * @return stdClass The new user record.
 */
function local_workshops_create_user(string $firstname, string $lastname, string $email): stdClass {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/user/lib.php');

    $base = clean_param(core_text::strtolower(trim($email)), PARAM_USERNAME);
    if ($base === '') {
        $base = 'workshopuser';
    }
    $username = $base;
    $i = 1;
    while ($DB->record_exists('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
        $username = $base . $i;
        $i++;
    }

    $user = new stdClass();
    $user->auth        = 'manual';
    $user->confirmed   = 1;
    $user->mnethostid  = $CFG->mnet_localhost_id;
    $user->username    = $username;
    $user->firstname   = $firstname;
    $user->lastname    = $lastname;
    $user->email       = trim($email);
    $user->lang        = $CFG->lang;
    $user->password    = 'to be generated';

    $user->id = user_create_user($user, false, true);
    set_user_preference('create_password', 1, $user->id);
    set_user_preference('auth_forcepasswordchange', 1, $user->id);

    return $DB->get_record('user', ['id' => $user->id], '*', MUST_EXIST);
}

/**
 * Make sure a user has an active enrolment in a course, using the manual
 * enrolment method (created on the course if it has none).
 *
 * Existing active enrolments (any method) and existing roles are left untouched.
 *
 * @param int $courseid
 * @param int $userid
 * @return bool True if a new/reactivated enrolment was made, false if already active.
 */
function local_workshops_ensure_enrolled(int $courseid, int $userid): bool {
    global $DB;

    $coursecontext = context_course::instance($courseid);
    if (is_enrolled($coursecontext, $userid, '', true)) {
        return false;
    }

    $plugin = enrol_get_plugin('manual');
    if (!$plugin) {
        throw new moodle_exception('lw_manualenroldisabled', 'local_workshops');
    }

    $instance = null;
    foreach (enrol_get_instances($courseid, false) as $inst) {
        if ($inst->enrol === 'manual') {
            $instance = $inst;
            break;
        }
    }
    if (!$instance) {
        $course = get_course($courseid);
        $instanceid = $plugin->add_default_instance($course);
        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
    }
    if ((int)$instance->status !== ENROL_INSTANCE_ENABLED) {
        $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
        $instance->status = ENROL_INSTANCE_ENABLED;
    }

    $roleid = (int)$DB->get_field('role', 'id', ['shortname' => 'student']);
    if (!$roleid) {
        $roles = get_archetype_roles('student');
        $roleid = $roles ? (int)reset($roles)->id : 0;
    }

    // enrol_user() also reactivates an existing suspended enrolment on this instance.
    $plugin->enrol_user($instance, $userid, $roleid ?: null, 0, 0, ENROL_USER_ACTIVE);
    return true;
}

/**
 * Get (or create) the Moodle group that represents this workshop's cohort.
 *
 * An existing group in the course with exactly the workshop's name is adopted
 * (so groups created by hand before this version are reused, not duplicated).
 *
 * @param stdClass $workshop local_workshops record.
 * @return int Group id.
 */
function local_workshops_get_workshop_group(stdClass $workshop): int {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/group/lib.php');

    $courseid = (int)$workshop->courseid;
    $idnumber = LOCAL_WORKSHOPS_GROUP_PREFIX . $workshop->id;

    if ($groupid = $DB->get_field('groups', 'id', ['courseid' => $courseid, 'idnumber' => $idnumber])) {
        return (int)$groupid;
    }

    // Adopt a hand-made group with the same name.
    $existing = $DB->get_records('groups', ['courseid' => $courseid, 'name' => $workshop->workshopname], 'id ASC');
    foreach ($existing as $g) {
        if (empty($g->idnumber)) {
            $g->idnumber = $idnumber;
            groups_update_group($g);
            return (int)$g->id;
        }
    }

    $name = $workshop->workshopname . ' (' . $workshop->workshopcode . ')';
    return (int)groups_create_group((object)[
        'courseid'          => $courseid,
        'name'              => core_text::substr($name, 0, 254),
        'idnumber'          => $idnumber,
        'description'       => 'Created by Workshop Scheduler for workshop ' . $workshop->workshopcode . '.',
        'descriptionformat' => FORMAT_HTML,
    ]);
}

/**
 * Get (or create) the course-level "attended" group.
 *
 * Course designers restrict the survey (and therefore the assessment and
 * certificate after it) to members of this group, once per Program course.
 *
 * @param int $courseid
 * @return int Group id.
 */
function local_workshops_get_attended_group(int $courseid): int {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/group/lib.php');

    if ($groupid = $DB->get_field('groups', 'id',
            ['courseid' => $courseid, 'idnumber' => LOCAL_WORKSHOPS_ATTENDED_IDNUMBER])) {
        return (int)$groupid;
    }

    $name = trim((string)get_config('local_workshops', 'attendedgroupname'));
    if ($name === '') {
        $name = get_string('attendedgroupname_default', 'local_workshops');
    }
    return (int)groups_create_group((object)[
        'courseid'          => $courseid,
        'name'              => $name,
        'idnumber'          => LOCAL_WORKSHOPS_ATTENDED_IDNUMBER,
        'description'       => 'Managed by Workshop Scheduler. Learners are added when their workshop booking is marked '
            . 'Attended. Restrict the survey to this group.',
        'descriptionformat' => FORMAT_HTML,
    ]);
}

/**
 * Remove a user from the course's attended group, unless they are still marked
 * Attended on another workshop linked to the same course.
 *
 * @param int $courseid
 * @param int $userid
 */
function local_workshops_remove_attended(int $courseid, int $userid): void {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/group/lib.php');

    $groupid = $DB->get_field('groups', 'id',
        ['courseid' => $courseid, 'idnumber' => LOCAL_WORKSHOPS_ATTENDED_IDNUMBER]);
    if (!$groupid || !groups_is_member($groupid, $userid)) {
        return;
    }
    $stillattended = $DB->record_exists_sql(
        "SELECT 1
           FROM {local_workshop_participants} p
           JOIN {local_workshops} w ON w.id = p.workshopid
          WHERE w.courseid = :courseid AND p.userid = :userid AND p.bookingstatus = :status",
        ['courseid' => $courseid, 'userid' => $userid, 'status' => 'Attended']
    );
    if (!$stillattended) {
        groups_remove_member($groupid, $userid);
    }
}

/**
 * Sync one workshop booking to the linked Moodle course.
 *
 * - Booked / Confirmed / Attended: find or create the Moodle account, enrol as
 *   Student, add to the workshop's cohort group.
 * - Attended: also add to the course "attended" group (unlocks the survey).
 * - Any other status: remove from the attended group (never unenrols).
 *
 * Never throws: the booking itself is always saved; the result says what happened.
 *
 * @param stdClass $workshop    local_workshops record.
 * @param stdClass $participant local_workshop_participants record (userid is updated in place).
 * @return array ['code' => string, 'message' => string]
 *               code: nocourse | skipped | nouser | nopermission | enrolled | created | ok | error
 */
function local_workshops_sync_participant(stdClass $workshop, stdClass $participant): array {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/group/lib.php');

    $name = trim($participant->firstname . ' ' . $participant->lastname);

    if (empty($workshop->courseid) || !$DB->record_exists('course', ['id' => $workshop->courseid])) {
        return ['code' => 'nocourse', 'message' => 'No linked Moodle course.'];
    }
    if (!get_config('local_workshops', 'autoenrol')) {
        return ['code' => 'disabled', 'message' => 'Automatic course enrolment is turned off in Workshop Scheduler settings.'];
    }
    $courseid = (int)$workshop->courseid;

    try {
        // Resolve the Moodle account.
        $user = null;
        if (!empty($participant->userid)) {
            $user = $DB->get_record('user', ['id' => $participant->userid, 'deleted' => 0]);
        }
        if (!$user) {
            $user = local_workshops_find_user_by_email($participant->email);
        }

        $enrolling = in_array($participant->bookingstatus, local_workshops_enrolling_statuses(), true);

        if (!$enrolling) {
            if ($user) {
                local_workshops_remove_attended($courseid, (int)$user->id);
                if ((int)$participant->userid !== (int)$user->id) {
                    $DB->set_field('local_workshop_participants', 'userid', $user->id, ['id' => $participant->id]);
                    $participant->userid = $user->id;
                }
            }
            return ['code' => 'skipped', 'message' => "$name: status {$participant->bookingstatus}, not enrolled."];
        }

        $created = false;
        if (!$user) {
            if (!get_config('local_workshops', 'autocreateusers')) {
                return ['code' => 'nouser', 'message' => "$name: no Moodle account with this email."];
            }
            if (!has_capability('moodle/user:create', context_system::instance())) {
                return ['code' => 'nopermission', 'message' => "$name: you don't have permission to create Moodle accounts."];
            }
            $user = local_workshops_create_user($participant->firstname, $participant->lastname, $participant->email);
            $created = true;
        }

        if ((int)$participant->userid !== (int)$user->id) {
            $DB->set_field('local_workshop_participants', 'userid', $user->id, ['id' => $participant->id]);
            $participant->userid = $user->id;
        }

        $coursecontext = context_course::instance($courseid);
        if (!is_enrolled($coursecontext, $user->id, '', true)
                && !has_capability('enrol/manual:enrol', $coursecontext)) {
            return ['code' => 'nopermission', 'message' => "$name: you don't have permission to enrol users in this course."];
        }

        $newenrol = local_workshops_ensure_enrolled($courseid, (int)$user->id);

        groups_add_member(local_workshops_get_workshop_group($workshop), $user->id);

        if ($participant->bookingstatus === 'Attended') {
            groups_add_member(local_workshops_get_attended_group($courseid), $user->id);
        } else {
            local_workshops_remove_attended($courseid, (int)$user->id);
        }

        if ($created) {
            return ['code' => 'created', 'message' => "$name: Moodle account created and enrolled (login details emailed)."];
        }
        if ($newenrol) {
            return ['code' => 'enrolled', 'message' => "$name: enrolled in the course."];
        }
        return ['code' => 'ok', 'message' => "$name: course access up to date."];

    } catch (Throwable $e) {
        debugging('local_workshops sync failed for participant ' . $participant->id . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
        return ['code' => 'error', 'message' => "$name: course sync failed (" . $e->getMessage() . ')'];
    }
}

/**
 * Sync every booking on a workshop to its linked course.
 *
 * @param stdClass $workshop
 * @return array Counts keyed by result code, plus 'messages' => string[] for problems.
 */
function local_workshops_sync_workshop(stdClass $workshop): array {
    global $DB;
    $summary = ['messages' => []];
    $participants = $DB->get_records('local_workshop_participants', ['workshopid' => $workshop->id], 'lastname, firstname');
    foreach ($participants as $p) {
        $r = local_workshops_sync_participant($workshop, $p);
        $summary[$r['code']] = ($summary[$r['code']] ?? 0) + 1;
        if (in_array($r['code'], ['nouser', 'nopermission', 'error'], true)) {
            $summary['messages'][] = $r['message'];
        }
    }
    return $summary;
}

/**
 * One-line, human readable summary of a sync result set.
 *
 * @param array $summary From local_workshops_sync_workshop() or built the same way.
 * @return string
 */
function local_workshops_sync_summary(array $summary): string {
    $parts = [];
    $labels = [
        'created'      => 'new accounts created',
        'enrolled'     => 'enrolled',
        'ok'           => 'already enrolled',
        'skipped'      => 'not enrolled (cancelled/waitlisted/no show)',
        'nouser'       => 'no Moodle account',
        'nopermission' => 'permission denied',
        'error'        => 'failed',
    ];
    foreach ($labels as $code => $label) {
        if (!empty($summary[$code])) {
            $parts[] = $summary[$code] . ' ' . $label;
        }
    }
    if (!empty($summary['nocourse'])) {
        return 'No linked Moodle course — nothing to sync.';
    }
    if (!empty($summary['disabled'])) {
        return 'Automatic course enrolment is turned off in Workshop Scheduler settings.';
    }
    $out = $parts ? 'Course sync: ' . implode(', ', $parts) . '.' : 'Course sync: nothing to do.';
    if (!empty($summary['messages'])) {
        $out .= ' ' . implode(' ', array_slice($summary['messages'], 0, 5));
    }
    return $out;
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
