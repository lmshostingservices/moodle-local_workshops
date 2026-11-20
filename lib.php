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
 * Email a participant who has just been marked Attended, with the link to the
 * linked course so they can complete the survey and assessment.
 *
 * Call only when the status has CHANGED to Attended, after
 * local_workshops_sync_participant() (which links/creates the account and enrols).
 *
 * @param stdClass $workshop    local_workshops record.
 * @param stdClass $participant local_workshop_participants record.
 * @return array ['code' => sent|disabled|nocourse|nouser|notenrolled|failed, 'message' => string]
 */
function local_workshops_send_attended_email(stdClass $workshop, stdClass $participant): array {
    global $DB, $SITE, $CFG;

    $name = trim($participant->firstname . ' ' . $participant->lastname);

    if (!get_config('local_workshops', 'attendedemail')) {
        return ['code' => 'disabled', 'message' => ''];
    }
    if (empty($workshop->courseid) || !($course = $DB->get_record('course', ['id' => $workshop->courseid]))) {
        return ['code' => 'nocourse', 'message' => "$name: no linked course, attendance email not sent."];
    }
    $user = empty($participant->userid) ? false
        : $DB->get_record('user', ['id' => $participant->userid, 'deleted' => 0, 'suspended' => 0]);
    if (!$user) {
        return ['code' => 'nouser', 'message' => "$name: no active Moodle account, attendance email not sent."];
    }
    $coursecontext = context_course::instance($course->id);
    if (!is_enrolled($coursecontext, $user->id, '', true)) { // Active enrolment only: a suspended one can't open the link.
        return ['code' => 'notenrolled', 'message' => "$name: not enrolled in the course, attendance email not sent."];
    }

    $courseurl = (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false);
    $date = $workshop->startdatetime
        ? userdate($workshop->startdatetime, get_string('strftimedate', 'langconfig'), core_date::get_user_timezone($user))
        : '';
    $replace = [
        '{firstname}'    => $user->firstname,
        '{lastname}'     => $user->lastname,
        '{fullname}'     => fullname($user),
        '{username}'     => $user->username,
        '{workshopname}' => $workshop->workshopname,
        '{workshopdate}' => $date,
        // escape => false: plain text for the subject/text body; s() below escapes for HTML.
        '{coursename}'   => format_string($course->fullname, true, ['context' => $coursecontext, 'escape' => false]),
        '{courseurl}'    => $courseurl,
        '{loginurl}'     => (new moodle_url('/login/index.php'))->out(false),
        '{forgotpasswordurl}' => (new moodle_url('/login/forgot_password.php'))->out(false),
        '{sitename}'     => format_string($SITE->fullname, true, ['context' => context_system::instance(), 'escape' => false]),
    ];

    $subject = trim((string)get_config('local_workshops', 'attendedemailsubject'));
    if ($subject === '') {
        $subject = get_string('attendedemailsubject_default', 'local_workshops');
    }
    $body = trim((string)get_config('local_workshops', 'attendedemailbody'));
    if ($body === '') {
        $body = get_string('attendedemailbody_default', 'local_workshops');
    }
    $subject = strtr($subject, $replace);
    $text    = strtr($body, $replace);

    // HTML version: escape the text, then make the course/login links clickable.
    $html = nl2br(s($text));
    $html = preg_replace('~(https?://[^\s<]+)~', '<a href="$1">$1</a>', $html);

    try {
        $from = core_user::get_support_user();
        $ok = email_to_user($user, $from, $subject, $text, $html);
    } catch (Throwable $e) {
        debugging('local_workshops attended email failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        $ok = false;
    }
    if (!$ok) {
        return ['code' => 'failed', 'message' => "$name: attendance email could not be sent."];
    }
    return ['code' => 'sent', 'message' => "$name: course link emailed."];
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

/**
 * Normalise a webinar/meeting URL typed or pasted into the workshop form.
 *
 * Strips the invisible characters and wrappers that come along when a link is
 * copied from Teams/Outlook (non-breaking or zero-width spaces, surrounding
 * whitespace, <angle brackets> or quotes), then checks it is an http(s) URL.
 *
 * @param string $url Raw input.
 * @return string|null '' when empty, the cleaned URL when valid, null when invalid.
 */
function local_workshops_normalise_meeting_url(string $url): ?string {
    $url = preg_replace('/[\x{00A0}\x{200B}-\x{200D}\x{2060}\x{FEFF}]/u', '', $url);
    $url = trim((string)$url);
    $url = trim($url, "<>\"' \t\r\n");
    if ($url === '') {
        return '';
    }
    if (!preg_match('~^https?://[^\s"\'<>]+$~i', $url)) {
        return null;
    }
    if (clean_param($url, PARAM_URL) === $url || filter_var($url, FILTER_VALIDATE_URL) !== false) {
        return $url;
    }
    return null;
}

/**
 * Who to email for a booking: the linked active Moodle account, or (when the
 * participant has no account yet) a stand-in user built from the booking's
 * name and email address.
 *
 * @param stdClass $participant local_workshop_participants record.
 * @return stdClass|null
 */
function local_workshops_email_recipient(stdClass $participant): ?stdClass {
    global $DB;
    if (!empty($participant->userid)) {
        $user = $DB->get_record('user', ['id' => $participant->userid, 'deleted' => 0, 'suspended' => 0]);
        if ($user) {
            return $user;
        }
    }
    if (empty($participant->email) || !validate_email($participant->email)) {
        return null;
    }
    $user = clone(core_user::get_noreply_user());
    $user->email      = $participant->email;
    $user->firstname  = (string)$participant->firstname;
    $user->lastname   = (string)$participant->lastname;
    $user->username   = '';
    $user->mailformat = 1;
    $user->emailstop  = 0;
    return $user;
}

/** Maximum number of sessions per workshop (session 1 + extras). */
define('LOCAL_WORKSHOPS_MAX_SESSIONS', 3);

/**
 * All sessions for a workshop, in order. Session 1 is the workshop's own
 * start/end and connection details; sessions 2-3 come from
 * local_workshop_sessions (Webinar/Hybrid only). A blank link, platform,
 * meeting ID or passcode on session 2/3 falls back to session 1's.
 *
 * @param stdClass $workshop
 * @return stdClass[] Each: sessionno, startdatetime, enddatetime, meetingurl, platform, meetingid, passcode.
 */
function local_workshops_get_sessions(stdClass $workshop): array {
    global $DB;
    $first = (object)[
        'sessionno'     => 1,
        'startdatetime' => (int)$workshop->startdatetime,
        'enddatetime'   => (int)$workshop->enddatetime,
        'meetingurl'    => (string)$workshop->teamslink,
        'platform'      => (string)$workshop->webinarplatform,
        'meetingid'     => (string)$workshop->webinarmeetingid,
        'passcode'      => (string)$workshop->webinarpasscode,
    ];
    $sessions = [$first];
    if (empty($workshop->id) || $workshop->deliverytype === 'Face-to-Face') {
        return $sessions;
    }
    $extras = $DB->get_records('local_workshop_sessions', ['workshopid' => $workshop->id], 'sessionno ASC');
    foreach ($extras as $x) {
        $sameconnection = trim((string)$x->meetingurl) === '';
        $sessions[] = (object)[
            'sessionno'     => (int)$x->sessionno,
            'startdatetime' => (int)$x->startdatetime,
            'enddatetime'   => (int)$x->enddatetime,
            'meetingurl'    => $sameconnection ? $first->meetingurl : (string)$x->meetingurl,
            // With a different link, show only that session's own ID/passcode.
            'platform'      => trim((string)$x->platform) !== '' ? (string)$x->platform : $first->platform,
            'meetingid'     => trim((string)$x->meetingid) !== '' ? (string)$x->meetingid
                : ($sameconnection ? $first->meetingid : ''),
            'passcode'      => trim((string)$x->passcode) !== '' ? (string)$x->passcode
                : ($sameconnection ? $first->passcode : ''),
        ];
    }
    return $sessions;
}

/**
 * "Monday, 16 November 2026, 10:00 AM - 12:00 PM" (end shows the date too if
 * it is on a different day). Optionally followed by the time zone.
 *
 * @param int $start
 * @param int $end
 * @param string|float|int $tz
 * @param bool $withtz
 * @return string
 */
function local_workshops_format_when(int $start, int $end, $tz, bool $withtz = true): string {
    if (!$start) {
        return '';
    }
    $out = userdate($start, get_string('strftimedaydatetime', 'langconfig'), $tz);
    if ($end) {
        $sameday = userdate($start, '%Y%m%d', $tz) === userdate($end, '%Y%m%d', $tz);
        $out .= ' - ' . userdate($end, get_string($sameday ? 'strftimetime' : 'strftimedaydatetime', 'langconfig'), $tz);
    }
    if ($withtz) {
        $out .= ' (' . core_date::get_localised_timezone($tz) . ')';
    }
    return $out;
}

/**
 * Plain-text connection lines for one session (link, platform, ID, passcode).
 *
 * @param stdClass $session From local_workshops_get_sessions().
 * @return string[]
 */
function local_workshops_connection_lines(stdClass $session): array {
    $lines = [];
    if ($session->meetingurl !== '') {
        $lines[] = 'Join the meeting:';
        $lines[] = $session->meetingurl;
    }
    if ($session->platform !== '') {
        $lines[] = 'Platform: ' . $session->platform;
    }
    if ($session->meetingid !== '') {
        $lines[] = 'Meeting ID: ' . $session->meetingid;
    }
    if ($session->passcode !== '') {
        $lines[] = 'Passcode: ' . $session->passcode;
    }
    return $lines;
}

/**
 * Plain-text venue lines (Face-to-Face / Hybrid).
 *
 * @param stdClass $workshop
 * @return string[]
 */
function local_workshops_venue_lines(stdClass $workshop): array {
    $lines = [];
    if (!empty($workshop->venue)) {
        $lines[] = 'Venue: ' . $workshop->venue;
    }
    if (!empty($workshop->room)) {
        $lines[] = 'Room: ' . $workshop->room;
    }
    if (!empty($workshop->address)) {
        $lines[] = 'Address: ' . str_replace(["\r\n", "\r", "\n"], ', ', trim($workshop->address));
    }
    return $lines;
}

/**
 * Plain-text joining details for a workshop. One session: link, platform,
 * meeting ID and passcode. Several sessions: a block per session with its
 * date/time and connection details. Venue/room/address are added for
 * Face-to-Face and Hybrid. Empty fields are left out.
 *
 * @param stdClass $workshop
 * @param string|float|int $tz Time zone for multi-session times.
 * @return string
 */
function local_workshops_joining_details(stdClass $workshop, $tz = 99): string {
    $type   = (string)$workshop->deliverytype;
    $blocks = [];
    if ($type !== 'Face-to-Face') {
        $sessions = local_workshops_get_sessions($workshop);
        if (count($sessions) === 1) {
            $blocks[] = implode("\n", local_workshops_connection_lines($sessions[0]));
        } else {
            $total = count($sessions);
            foreach ($sessions as $i => $sess) {
                $lines = ['Session ' . ($i + 1) . ' of ' . $total . ': '
                    . local_workshops_format_when($sess->startdatetime, $sess->enddatetime, $tz, false)];
                $blocks[] = implode("\n", array_merge($lines, local_workshops_connection_lines($sess)));
            }
        }
    }
    if ($type !== 'Webinar') {
        $blocks[] = implode("\n", local_workshops_venue_lines($workshop));
    }
    return trim(implode("\n\n", array_filter($blocks, function ($b) {
        return trim($b) !== '';
    })));
}

/**
 * Escape a value for an iCalendar TEXT property.
 *
 * @param string $value
 * @return string
 */
function local_workshops_ics_escape(string $value): string {
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\;', '\\,', '\\n'], $value);
}

/**
 * Fold an iCalendar content line at 75 octets (RFC 5545 3.1), UTF-8 safe.
 *
 * @param string $line
 * @return string
 */
function local_workshops_ics_fold(string $line): string {
    $out = '';
    $current = '';
    $limit = 75;
    foreach (preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY) as $char) {
        if (strlen($current) + strlen($char) > $limit) {
            $out .= $current . "\r\n ";
            $current = '';
            $limit = 74; // Continuation lines start with a space.
        }
        $current .= $char;
    }
    return $out . $current . "\r\n";
}

/**
 * Build an .ics calendar file with one event per dated session.
 *
 * UIDs are stable per workshop/session and SEQUENCE rises each time the
 * workshop is saved, so re-sending updates the existing calendar entry rather
 * than adding a duplicate (in clients that honour UID/SEQUENCE).
 *
 * @param stdClass $workshop
 * @return string ICS content, or '' if no session has a start date.
 */
function local_workshops_build_ics(stdClass $workshop): string {
    global $CFG;

    $host = parse_url($CFG->wwwroot, PHP_URL_HOST) ?: 'moodle';
    $now  = gmdate('Ymd\THis\Z');
    $seq  = max(0, (int)$workshop->timemodified - (int)$workshop->timecreated);
    $sessions = array_values(array_filter(local_workshops_get_sessions($workshop), function ($s) {
        return $s->startdatetime > 0;
    }));
    if (!$sessions) {
        return '';
    }
    $total  = count($sessions);
    $venue  = $workshop->deliverytype !== 'Webinar' ? local_workshops_venue_lines($workshop) : [];
    $online = $workshop->deliverytype !== 'Face-to-Face';

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//LMS Hosting Services//Workshop Scheduler//EN',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
    ];
    foreach ($sessions as $i => $sess) {
        $start = $sess->startdatetime;
        $end   = $sess->enddatetime > $start ? $sess->enddatetime : $start + HOURSECS;
        $summary = $workshop->workshopname . ($total > 1 ? ' (Session ' . ($i + 1) . ' of ' . $total . ')' : '');

        $desc = $online ? local_workshops_connection_lines($sess) : [];
        if ($venue) {
            $desc = array_merge($desc, $desc ? [''] : [], $venue);
        }
        $location = ($online && $sess->meetingurl !== '')
            ? (($sess->platform !== '' ? $sess->platform . ': ' : '') . $sess->meetingurl)
            : implode(', ', array_map(function ($l) {
                return preg_replace('/^[A-Za-z]+: /', '', $l);
            }, $venue));

        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:lw-' . (int)$workshop->id . '-s' . (int)$sess->sessionno . '@' . $host;
        $lines[] = 'DTSTAMP:' . $now;
        $lines[] = 'SEQUENCE:' . $seq;
        $lines[] = 'DTSTART:' . gmdate('Ymd\THis\Z', $start);
        $lines[] = 'DTEND:' . gmdate('Ymd\THis\Z', $end);
        $lines[] = 'SUMMARY:' . local_workshops_ics_escape($summary);
        if ($location !== '') {
            $lines[] = 'LOCATION:' . local_workshops_ics_escape($location);
        }
        if ($desc) {
            $lines[] = 'DESCRIPTION:' . local_workshops_ics_escape(implode("\n", $desc));
        }
        if ($online && $sess->meetingurl !== '') {
            $lines[] = 'URL:' . $sess->meetingurl;
        }
        $lines[] = 'STATUS:CONFIRMED';
        $lines[] = 'TRANSP:OPAQUE';
        $lines[] = 'BEGIN:VALARM';
        $lines[] = 'ACTION:DISPLAY';
        $lines[] = 'DESCRIPTION:' . local_workshops_ics_escape($summary);
        $lines[] = 'TRIGGER:-PT15M';
        $lines[] = 'END:VALARM';
        $lines[] = 'END:VEVENT';
    }
    $lines[] = 'END:VCALENDAR';

    return implode('', array_map('local_workshops_ics_fold', $lines));
}

/**
 * Email a participant whose booking has just been Confirmed, with the date/time
 * and joining details (meeting link, platform, meeting ID, passcode; or venue).
 *
 * Call only when the status has CHANGED to Confirmed (not when an Attended
 * booking is reverted to Confirmed), after local_workshops_sync_participant().
 * Does not need a linked course. Not sent for Webinar/Hybrid workshops that
 * have no meeting link yet - use "Email joining details" once it is added.
 *
 * @param stdClass $workshop    local_workshops record.
 * @param stdClass $participant local_workshop_participants record.
 * @param bool     $force       Send even if the setting is off (manual resend).
 * @return array ['code' => sent|disabled|nolink|nouser|failed, 'message' => string]
 */
function local_workshops_send_confirmed_email(stdClass $workshop, stdClass $participant, bool $force = false): array {
    global $SITE;

    $name = trim($participant->firstname . ' ' . $participant->lastname);

    if (!$force && !get_config('local_workshops', 'confirmedemail')) {
        return ['code' => 'disabled', 'message' => ''];
    }
    if ($workshop->deliverytype !== 'Face-to-Face' && empty($workshop->teamslink)) {
        return ['code' => 'nolink',
            'message' => "$name: confirmation email not sent - the workshop has no meeting link yet. "
                . "Add it, then use 'Email joining details'."];
    }
    $user = local_workshops_email_recipient($participant);
    if (!$user) {
        return ['code' => 'nouser', 'message' => "$name: no valid email address, confirmation email not sent."];
    }

    $tz = core_date::get_user_timezone($user);
    $sessions = local_workshops_get_sessions($workshop);
    $when = local_workshops_when_summary($workshop, $tz);

    // Calendar: a download link (works even when mail filters strip attachments)
    // plus the same .ics attached to the email.
    $icspath = '';
    $icsname = '';
    $calendarlink = '';
    $calendarnote = '';
    if (get_config('local_workshops', 'confirmedemailics') !== '0') {
        $ics = local_workshops_build_ics($workshop);
        if ($ics !== '') {
            $calendarlink = local_workshops_calendar_url($workshop)->out(false);
            $icspath = local_workshops_write_ics_tempfile($ics);
            if ($icspath !== '') {
                $icsname = clean_filename(($workshop->workshopcode ?: 'workshop') . '.ics');
            }
            $n = count(array_filter($sessions, function ($x) {
                return $x->startdatetime > 0;
            }));
            $what = $n > 2 ? 'all ' . $n . ' sessions' : ($n === 2 ? 'both sessions' : 'this session');
            $calendarnote = 'Add ' . $what . ' to your calendar:' . "\n" . $calendarlink
                . ($icspath !== '' ? "\n" . '(The same calendar file is also attached to this email.)' : '');
        }
    }

    $coursename = '';
    $courseurl  = '';
    global $DB;
    if (!empty($workshop->courseid) && ($course = $DB->get_record('course', ['id' => $workshop->courseid]))) {
        $coursename = format_string($course->fullname, true,
            ['context' => context_course::instance($course->id), 'escape' => false]);
        $courseurl = (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false);
    }

    $replace = [
        '{firstname}'      => $user->firstname,
        '{lastname}'       => $user->lastname,
        '{fullname}'       => trim($user->firstname . ' ' . $user->lastname),
        '{username}'       => (string)$user->username,
        '{workshopname}'   => $workshop->workshopname,
        '{workshopdate}'   => $workshop->startdatetime
            ? userdate($workshop->startdatetime, get_string('strftimedate', 'langconfig'), $tz) : '',
        '{workshopwhen}'   => $when,
        '{joiningdetails}' => local_workshops_joining_details($workshop, $tz),
        '{calendarnote}'   => $calendarnote,
        '{calendarlink}'   => $calendarlink,
        '{meetingurl}'     => (string)$workshop->teamslink,
        '{platform}'       => (string)$workshop->webinarplatform,
        '{meetingid}'      => (string)$workshop->webinarmeetingid,
        '{passcode}'       => (string)$workshop->webinarpasscode,
        '{coursename}'     => $coursename,
        '{courseurl}'      => $courseurl,
        '{loginurl}'       => (new moodle_url('/login/index.php'))->out(false),
        '{sitename}'       => format_string($SITE->fullname, true,
            ['context' => context_system::instance(), 'escape' => false]),
    ];

    $subject = trim((string)get_config('local_workshops', 'confirmedemailsubject'));
    if ($subject === '') {
        $subject = get_string('confirmedemailsubject_default', 'local_workshops');
    }
    $body = trim((string)get_config('local_workshops', 'confirmedemailbody'));
    if ($body === '') {
        $body = get_string('confirmedemailbody_default', 'local_workshops');
    }
    $subject = strtr($subject, $replace);
    $text    = strtr(str_replace("\r\n", "\n", $body), $replace);
    $text    = preg_replace("/\n{3,}/", "\n\n", $text); // Tidy gaps left by empty placeholders.

    $html = nl2br(s($text));
    $html = preg_replace('~(https?://[^\s<]+)~', '<a href="$1">$1</a>', $html);

    try {
        $from = core_user::get_support_user();
        $ok = email_to_user($user, $from, $subject, $text, $html, $icspath, $icsname);
    } catch (Throwable $e) {
        debugging('local_workshops confirmed email failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        $ok = false;
    }
    // The temp file is not deleted here: if mail is queued it is read after this
    // call. local_workshops_write_ics_tempfile() clears files older than a day.
    if (!$ok) {
        return ['code' => 'failed', 'message' => "$name: confirmation email could not be sent."];
    }
    return ['code' => 'sent', 'message' => "$name: joining details emailed."];
}

/**
 * When a workshop runs, for emails. One session: "Monday, 16 November 2026,
 * 10:00 AM - 12:00 PM (Australia/Perth)". Several: "3 sessions, 16 November
 * 2026 to 18 November 2026 (times in Australia/Perth, see below)".
 *
 * @param stdClass $workshop
 * @param string|float|int $tz
 * @return string
 */
function local_workshops_when_summary(stdClass $workshop, $tz): string {
    $sessions = local_workshops_get_sessions($workshop);
    if (count($sessions) > 1 && $sessions[0]->startdatetime) {
        $last = end($sessions);
        return count($sessions) . ' sessions, '
            . userdate($sessions[0]->startdatetime, get_string('strftimedate', 'langconfig'), $tz) . ' to '
            . userdate($last->startdatetime, get_string('strftimedate', 'langconfig'), $tz)
            . ' (times in ' . core_date::get_localised_timezone($tz) . ', see below)';
    }
    return local_workshops_format_when((int)$workshop->startdatetime, (int)$workshop->enddatetime, $tz);
}

/**
 * Workshop date(s) only, for emails: "16 November 2026", or for several
 * sessions "16 November 2026, 17 November 2026 and 18 November 2026".
 *
 * @param stdClass $workshop
 * @param string|float|int $tz
 * @return string
 */
function local_workshops_date_summary(stdClass $workshop, $tz): string {
    $dates = [];
    foreach (local_workshops_get_sessions($workshop) as $sess) {
        if ($sess->startdatetime) {
            $dates[] = userdate($sess->startdatetime, get_string('strftimedate', 'langconfig'), $tz);
        }
    }
    $dates = array_values(array_unique($dates));
    if (count($dates) <= 1) {
        return $dates[0] ?? '';
    }
    $last = array_pop($dates);
    return implode(', ', $dates) . ' and ' . $last;
}

/**
 * Welcome email when a participant first gets a place (see
 * local_workshops_is_welcome_transition()): workshop date, course link,
 * username and password-reset link. Replaces Moodle's manual-enrolment
 * course welcome message (turn that off in the course to avoid two emails).
 *
 * Needs a linked course and a Moodle account (created/linked by
 * local_workshops_sync_participant(), which must run first).
 *
 * @param stdClass $workshop    local_workshops record.
 * @param stdClass $participant local_workshop_participants record (after sync).
 * @param bool     $force       Send even if the setting is off (manual resend).
 * @return array ['code' => sent|disabled|nocourse|nouser|failed, 'message' => string]
 */
function local_workshops_send_booked_email(stdClass $workshop, stdClass $participant, bool $force = false): array {
    global $DB, $SITE;

    $name = trim($participant->firstname . ' ' . $participant->lastname);

    if (!$force && !get_config('local_workshops', 'bookedemail')) {
        return ['code' => 'disabled', 'message' => ''];
    }
    $course = !empty($workshop->courseid) ? $DB->get_record('course', ['id' => $workshop->courseid]) : null;
    if (!$course) {
        return ['code' => 'nocourse', 'message' => "$name: welcome email not sent - the workshop has no linked course."];
    }
    $user = !empty($participant->userid)
        ? $DB->get_record('user', ['id' => $participant->userid, 'deleted' => 0, 'suspended' => 0]) : null;
    if (!$user) {
        return ['code' => 'nouser', 'message' => "$name: welcome email not sent - no Moodle account linked."];
    }

    $tz = core_date::get_user_timezone($user);
    $replace = [
        '{firstname}'         => $user->firstname,
        '{lastname}'          => $user->lastname,
        '{fullname}'          => trim($user->firstname . ' ' . $user->lastname),
        '{username}'          => $user->username,
        '{email}'             => $user->email,
        '{workshopname}'      => $workshop->workshopname,
        '{workshopdate}'      => local_workshops_date_summary($workshop, $tz),
        '{workshopwhen}'      => local_workshops_when_summary($workshop, $tz),
        '{coursename}'        => format_string($course->fullname, true,
            ['context' => context_course::instance($course->id), 'escape' => false]),
        '{courseurl}'         => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
        '{loginurl}'          => (new moodle_url('/login/index.php'))->out(false),
        '{forgotpasswordurl}' => (new moodle_url('/login/forgot_password.php'))->out(false),
        '{sitename}'          => format_string($SITE->fullname, true,
            ['context' => context_system::instance(), 'escape' => false]),
    ];

    $subject = trim((string)get_config('local_workshops', 'bookedemailsubject'));
    if ($subject === '') {
        $subject = get_string('bookedemailsubject_default', 'local_workshops');
    }
    $body = trim((string)get_config('local_workshops', 'bookedemailbody'));
    if ($body === '') {
        $body = get_string('bookedemailbody_default', 'local_workshops');
    }
    $subject = strtr($subject, $replace);
    $text    = strtr(str_replace("\r\n", "\n", $body), $replace);
    $text    = preg_replace("/\n{3,}/", "\n\n", $text);

    $html = nl2br(s($text));
    $html = preg_replace('~(https?://[^\s<]+)~', '<a href="$1">$1</a>', $html);

    try {
        $ok = email_to_user($user, core_user::get_support_user(), $subject, $text, $html);
    } catch (Throwable $e) {
        debugging('local_workshops booked email failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        $ok = false;
    }
    if (!$ok) {
        return ['code' => 'failed', 'message' => "$name: welcome email could not be sent."];
    }
    return ['code' => 'sent', 'message' => "$name: welcome email sent."];
}

/**
 * Write an .ics file to Moodle's temp directory for attaching to an email.
 * Files are kept for a day (so queued mail can still read them), then
 * removed on a later call.
 *
 * @param string $ics
 * @return string Absolute path, or '' on failure.
 */
function local_workshops_write_ics_tempfile(string $ics): string {
    $dir = make_temp_directory('local_workshops/ics');
    foreach (glob($dir . '/*.ics') ?: [] as $old) {
        if (@filemtime($old) < time() - DAYSECS) {
            @unlink($old);
        }
    }
    $path = $dir . '/invite_' . random_string(20) . '.ics';
    return file_put_contents($path, $ics) === false ? '' : $path;
}

/**
 * Secret used to sign public calendar links (created on first use).
 *
 * @return string
 */
function local_workshops_calendar_secret(): string {
    $secret = (string)get_config('local_workshops', 'calendarsecret');
    if (strlen($secret) < 32) {
        $secret = random_string(48);
        set_config('calendarsecret', $secret, 'local_workshops');
    }
    return $secret;
}

/**
 * Token for a workshop's public calendar link.
 *
 * @param int $workshopid
 * @return string
 */
function local_workshops_calendar_token(int $workshopid): string {
    return substr(hash_hmac('sha256', 'ics:' . $workshopid, local_workshops_calendar_secret()), 0, 32);
}

/**
 * Public "Add to calendar" link (no login needed) that downloads the
 * workshop's current .ics, so it always reflects the latest sessions.
 * Served by local_workshops_pluginfile() (filearea "calendar").
 *
 * @param stdClass $workshop
 * @return moodle_url
 */
function local_workshops_calendar_url(stdClass $workshop): moodle_url {
    return moodle_url::make_pluginfile_url(
        context_system::instance()->id,
        'local_workshops',
        'calendar',
        (int)$workshop->id,
        '/' . local_workshops_calendar_token((int)$workshop->id) . '/',
        clean_filename(($workshop->workshopcode ?: 'workshop') . '.ics')
    );
}

/**
 * Serve plugin files.
 *
 * Filearea "calendar": the workshop's sessions as an .ics file, for the
 * "Add to calendar" link in the Confirmed email. Participants may not have
 * logged in yet, so access is by a signed per-workshop token instead of a
 * login. The file holds the same details as the email and is built fresh on
 * each request. URL args: {workshopid}/{token}/{filename}.ics
 *
 * @param stdClass $course
 * @param stdClass|null $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool false if the file is not found; otherwise the file is sent and the script ends.
 */
function local_workshops_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB;

    if ($context->contextlevel != CONTEXT_SYSTEM || $filearea !== 'calendar' || count($args) < 3) {
        return false;
    }
    $workshopid = (int)array_shift($args);
    $token = clean_param((string)array_shift($args), PARAM_ALPHANUM);

    $workshop = $DB->get_record('local_workshops', ['id' => $workshopid]);
    if (!$workshop || !hash_equals(local_workshops_calendar_token($workshopid), $token)) {
        return false;
    }
    $ics = local_workshops_build_ics($workshop);
    if ($ics === '') {
        return false;
    }
    $filename = clean_filename(($workshop->workshopcode ?: 'workshop') . '.ics');
    send_file($ics, $filename, 0, 0, true, true, 'text/calendar', false, ['cacheability' => 'private']);
    return true;
}

/**
 * Should a status change trigger the welcome (Booked) email? True when the
 * participant newly gets a place: from nothing, Waitlisted or Cancelled to
 * Booked, Confirmed or Attended.
 *
 * @param string $from Previous status ('' for a new participant).
 * @param string $to   New status.
 * @return bool
 */
function local_workshops_is_welcome_transition(string $from, string $to): bool {
    return in_array($to, ['Booked', 'Confirmed', 'Attended'], true)
        && in_array($from, ['', 'Waitlisted', 'Cancelled'], true);
}
