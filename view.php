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
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/workshops/lib.php');

// CRITICAL: view.php is not its own admin node, but it renders with the 'admin' layout.
// Anchor it under the 'Manage Workshops' node so the admin tree can locate it; otherwise an
// unlocated admin-layout page collapses BOTH the primary and secondary navigation.
// admin_externalpage_setup() also runs require_login() and enforces local/workshops:view.
admin_externalpage_setup('local_workshops_manage');

$id  = required_param('id', PARAM_INT);
$tab = optional_param('tab', 'info', PARAM_ALPHA);

$context = context_system::instance();

$workshop = $DB->get_record('local_workshops', ['id' => $id], '*', MUST_EXIST);
$course   = $workshop->courseid  ? $DB->get_record('course', ['id' => $workshop->courseid]) : null;
// Load all trainers for this workshop from the junction table.
$trainerrecords = $DB->get_records_sql(
    "SELECT u.id, u.firstname, u.lastname
     FROM {local_workshop_trainers} wt
     JOIN {user} u ON u.id = wt.userid
     WHERE wt.workshopid = :wid
     ORDER BY u.lastname, u.firstname",
    ['wid' => $id]
);
$trainernames = array_map(function ($t) { return fullname($t); }, $trainerrecords);
$trainersdisplay = !empty($trainernames) ? implode(', ', $trainernames) : '—';

// admin_externalpage_setup() anchored this page under 'Manage Workshops' and set the
// 'admin' layout — override URL/title/heading for this specific workshop.
$PAGE->set_url('/local/workshops/view.php', ['id' => $id, 'tab' => $tab]);
$PAGE->set_title($workshop->workshopname);
$PAGE->set_heading($workshop->workshopname);
$PAGE->requires->js_call_amd('local_workshops/workshops', 'initView', [$id, sesskey()]);

$canEdit    = has_capability('local/workshops:edit',              $context);
$canManPart = has_capability('local/workshops:manageparticipants',$context);
$canManDoc  = has_capability('local/workshops:managedocuments',   $context);
$canManTask = has_capability('local/workshops:managetasks',       $context);

echo $OUTPUT->header();

// Back link
echo '<div class="lw-container">';
echo '<div class="lw-breadcrumb">';
echo '<a href="' . (new moodle_url('/local/workshops/manage.php'))->out() . '">&larr; Workshops</a>';
echo '</div>';

// Workshop header bar
echo '<div class="lw-workshop-header">';
echo '<div class="lw-workshop-title">';
echo '<span class="lw-code">' . s($workshop->workshopcode) . '</span>';
echo '<h2>' . s($workshop->workshopname) . '</h2>';
echo '</div>';
echo '<div class="lw-workshop-header-right">';
echo local_workshops_status_badge($workshop->status);
echo local_workshops_delivery_badge($workshop->deliverytype);
if ($canEdit) {
    echo '<a href="' . (new moodle_url('/local/workshops/edit.php', ['id' => $id]))->out() . '" class="btn btn-sm btn-outline-primary">Edit</a>';
}
if (is_siteadmin()) {
    echo '<a href="' . (new moodle_url('/admin/settings.php', ['section' => 'local_workshops_settings']))->out() . '"'
        . ' class="btn btn-sm btn-outline-secondary lw-settings-btn"'
        . ' title="' . get_string('settings', 'local_workshops') . '">&#9881; ' . get_string('settings', 'local_workshops') . '</a>';
}
echo '</div>';
echo '</div>';

// Tabs
$tabs = [
    'info'         => get_string('tabinfo', 'local_workshops'),
    'participants' => get_string('tabparticipants', 'local_workshops'),
    'documents'    => get_string('tabdocuments', 'local_workshops'),
    'checklist'    => get_string('tabchecklist', 'local_workshops'),
    'moodle'       => get_string('tabmoodle', 'local_workshops'),
];
echo '<div class="lw-tabs">';
foreach ($tabs as $key => $label) {
    $active = $tab === $key ? ' active' : '';
    $url = new moodle_url('/local/workshops/view.php', ['id' => $id, 'tab' => $key]);
    echo '<a href="' . $url->out() . '" class="lw-tab' . $active . '">' . $label . '</a>';
}
echo '</div>';

echo '<div class="lw-tab-content">';

// ===========================
// TAB: WORKSHOP INFORMATION
// ===========================
if ($tab === 'info') {
    echo '<div class="lw-info-grid">';

    $startdt = $workshop->startdatetime ? userdate($workshop->startdatetime, '%d %B %Y, %I:%M %p') : '—';
    $enddt   = $workshop->enddatetime   ? userdate($workshop->enddatetime,   '%d %B %Y, %I:%M %p') : '—';

    // Determine which field groups to show based on delivery type.
    // Face-to-Face → location fields only.
    // Webinar       → webinar fields only.
    // Hybrid        → both groups.
    $dt           = $workshop->deliverytype ?? 'Face-to-Face';
    $showLocation = ($dt !== 'Webinar');
    $showWebinar  = ($dt !== 'Face-to-Face');

    $fields = [
        'Workshop Code'         => s($workshop->workshopcode),
        'Workshop Name'         => s($workshop->workshopname),
        'Program'               => $workshop->program ? s($workshop->program) : '—',
        'Linked Course'         => $course ? ('<a href="' . (new moodle_url('/course/view.php', ['id' => $course->id]))->out() . '">' . s($course->fullname) . '</a>') : '—',
        'Trainer(s)'            => $trainersdisplay,
        'Status'                => local_workshops_status_badge($workshop->status),
        'Delivery Type'         => local_workshops_delivery_badge($workshop->deliverytype),
        'Start Date & Time'     => $startdt,
        'End Date & Time'       => $enddt,
    ];

    if ($showLocation) {
        $fields['Venue']   = $workshop->venue   ? s($workshop->venue)                : '—';
        $fields['Room']    = $workshop->room    ? s($workshop->room)                 : '—';
        $fields['Address'] = $workshop->address ? nl2br(s($workshop->address))       : '—';
    }

    if ($showWebinar) {
        $fields['Teams Link'] = $workshop->teamslink
            ? ('<a href="' . s($workshop->teamslink) . '" target="_blank">Join Meeting</a>')
            : '—';
        $fields['Platform']   = !empty($workshop->webinarplatform)  ? s($workshop->webinarplatform)  : '—';
        $fields['Meeting ID'] = !empty($workshop->webinarmeetingid) ? s($workshop->webinarmeetingid) : '—';
        $fields['Passcode']   = !empty($workshop->webinarpasscode)  ? s($workshop->webinarpasscode)  : '—';
    }

    $fields['Capacity']           = $workshop->capacity ? (int)$workshop->capacity . ' participants' : '—';
    $fields['Catering Required']  = $workshop->cateringrequired ? 'Yes' : 'No';
    $fields['Catering Notes']     = $workshop->cateringnotes ? nl2br(s($workshop->cateringnotes)) : '—';
    $fields['Customer Name']      = $workshop->customername    ? s($workshop->customername)    : '—';
    $fields['Customer Contact']   = $workshop->customercontact ? s($workshop->customercontact) : '—';
    $fields['Special Requirements'] = $workshop->specialrequirements ? nl2br(s($workshop->specialrequirements)) : '—';
    $fields['Notes']              = $workshop->notes ? nl2br(s($workshop->notes)) : '—';
    $fields['Created']            = userdate($workshop->timecreated,  '%d %b %Y');
    $fields['Last Modified']      = userdate($workshop->timemodified, '%d %b %Y');

    foreach ($fields as $label => $value) {
        echo '<div class="lw-info-row"><div class="lw-info-label">' . $label . '</div><div class="lw-info-value">' . $value . '</div></div>';
    }
    echo '</div>';
}

// ===========================
// TAB: PARTICIPANTS
// ===========================
if ($tab === 'participants') {
    $participants = $DB->get_records_sql(
        "SELECT p.*, u.username
         FROM {local_workshop_participants} p
         LEFT JOIN {user} u ON u.id = p.userid
         WHERE p.workshopid = :wid
         ORDER BY p.lastname, p.firstname",
        ['wid' => $id]
    );

    $wscoursecontext = null;
    $attendedmembers = [];
    if ($workshop->courseid && $DB->record_exists('course', ['id' => $workshop->courseid])) {
        $wscoursecontext = context_course::instance($workshop->courseid);
        $attgroupid = $DB->get_field('groups', 'id',
            ['courseid' => $workshop->courseid, 'idnumber' => LOCAL_WORKSHOPS_ATTENDED_IDNUMBER]);
        if ($attgroupid) {
            $attendedmembers = $DB->get_records_menu('groups_members', ['groupid' => $attgroupid], '', 'userid, id');
        }
    } else {
        $workshop->courseid = 0; // Linked course was deleted — treat as unlinked on this page.
    }


    if ($canManPart) {
        echo '<div class="lw-section-actions">';
        echo '<button type="button" class="btn btn-primary btn-sm" id="lw-btn-add-participant">+ Add Participant</button>';
        if ($workshop->courseid) {
            echo '<button type="button" class="btn btn-outline-primary btn-sm" id="lw-btn-enrol-group">Enrol from Group</button>';
        }
        echo '<a href="' . (new moodle_url('/local/workshops/import.php', ['workshopid' => $id]))->out() . '" class="btn btn-secondary btn-sm">Bulk Import CSV</a>';
        if ($workshop->courseid && !empty($participants)) {
            echo '<form method="post" action="' . (new moodle_url('/local/workshops/sync.php'))->out() . '" style="display:inline">'
               . '<input type="hidden" name="workshopid" value="' . $id . '">'
               . '<input type="hidden" name="sesskey" value="' . sesskey() . '">'
               . '<button type="submit" class="btn btn-outline-primary btn-sm" '
               . 'title="' . s(get_string('syncparticipants_help', 'local_workshops')) . '">'
               . get_string('syncparticipants', 'local_workshops') . '</button></form>';
        }
        if (!empty($participants)) {
            echo '<a href="' . (new moodle_url('/local/workshops/ajax.php', ['action' => 'export_participants', 'workshopid' => $id, 'sesskey' => sesskey()]))->out() . '" class="btn btn-outline-secondary btn-sm">Export CSV</a>';
        }
        echo '</div>';
    }

    if (empty($participants)) {
        echo '<p class="lw-empty-notice">' . get_string('noparticipants', 'local_workshops') . '</p>';
    } else {
        echo '<table class="lw-table table table-sm">';
        echo '<thead><tr>';
        echo '<th>Name</th><th>Email</th><th>Organisation</th>';
        echo '<th>Booking Status</th><th>Booking Date</th><th>Moodle User</th>';
        if ($workshop->courseid) echo '<th>Course Access</th>';
        if ($canManPart) echo '<th>Actions</th>';
        echo '</tr></thead><tbody>';

        foreach ($participants as $p) {
            $bookdate = userdate($p->bookingdate, '%d %b %Y');
            $moodlelink = $p->userid
                ? '<a href="' . (new moodle_url('/user/view.php', ['id' => $p->userid]))->out() . '" target="_blank">View</a>'
                : '<span class="text-muted">—</span>';
            echo '<tr>';
            echo '<td>' . s($p->firstname . ' ' . $p->lastname) . '</td>';
            echo '<td>' . s($p->email) . '</td>';
            echo '<td>' . ($p->organisation ? s($p->organisation) : '—') . '</td>';
            echo '<td>' . local_workshops_booking_status_badge($p->bookingstatus) . '</td>';
            echo '<td>' . $bookdate . '</td>';
            echo '<td>' . $moodlelink . '</td>';
            if ($workshop->courseid) {
                if (!$p->userid) {
                    $access = '<span class="text-muted">No account</span>';
                } else if (is_enrolled($wscoursecontext, $p->userid, '', true)) {
                    $access = '<span class="lw-badge lw-badge-bk-confirmed">Enrolled</span>';
                    if (isset($attendedmembers[$p->userid])) {
                        $access .= ' <span class="lw-badge lw-badge-bk-attended">Survey unlocked</span>';
                    }
                } else {
                    $access = '<span class="lw-badge lw-badge-bk-cancelled">Not enrolled</span>';
                }
                echo '<td>' . $access . '</td>';
            }
            if ($canManPart) {
                echo '<td>';
                echo '<button type="button" class="btn btn-xs btn-outline-secondary lw-edit-participant" '
                   . 'data-id="' . $p->id . '" data-firstname="' . s($p->firstname) . '" data-lastname="' . s($p->lastname) . '" '
                   . 'data-email="' . s($p->email) . '" data-org="' . s($p->organisation) . '" '
                   . 'data-status="' . s($p->bookingstatus) . '" data-userid="' . (int)$p->userid . '">Edit</button> ';
                echo '<button type="button" class="btn btn-xs btn-outline-danger lw-remove-participant" data-id="' . $p->id . '">Remove</button>';
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    // Add/Edit Participant Modal
    if ($canManPart):
    ?>
    <div id="lw-participant-modal" class="lw-modal" style="display:none">
        <div class="lw-modal-overlay"></div>
        <div class="lw-modal-dialog">
            <h3 id="lw-modal-title">Add Participant</h3>
            <form id="lw-participant-form">
                <input type="hidden" name="action" value="save_participant">
                <input type="hidden" name="workshopid" value="<?php echo $id; ?>">
                <input type="hidden" name="participantid" id="lw-participant-id" value="0">
                <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                <div class="lw-form-row">
                    <label>First Name *</label>
                    <input type="text" name="firstname" id="lw-p-firstname" class="form-control" required>
                </div>
                <div class="lw-form-row">
                    <label>Last Name *</label>
                    <input type="text" name="lastname" id="lw-p-lastname" class="form-control" required>
                </div>
                <div class="lw-form-row">
                    <label>Email *</label>
                    <input type="email" name="email" id="lw-p-email" class="form-control" required>
                </div>
                <div class="lw-form-row">
                    <label>Organisation</label>
                    <input type="text" name="organisation" id="lw-p-org" class="form-control">
                </div>
                <div class="lw-form-row">
                    <label>Booking Status</label>
                    <select name="bookingstatus" id="lw-p-status" class="form-control">
                        <?php foreach (local_workshops_get_booking_statuses() as $bs): ?>
                        <option value="<?php echo s($bs); ?>"><?php echo s($bs); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="lw-form-row">
                    <label>Notes</label>
                    <textarea name="notes" id="lw-p-notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="lw-modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" id="lw-modal-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($workshop->courseid): ?>
    <!-- Enrol from Group Modal -->
    <div id="lw-group-modal" class="lw-modal" style="display:none">
        <div class="lw-modal-overlay lw-group-modal-overlay"></div>
        <div class="lw-modal-dialog">
            <h3>Enrol from Group</h3>
            <p class="lw-modal-desc">Select a Moodle group from the linked course. All group members will be added as workshop participants and enrolled into the course.</p>
            <div id="lw-group-loading" style="display:none" class="lw-modal-loading">Loading groups&hellip;</div>
            <div id="lw-group-no-course" style="display:none" class="lw-empty-notice">No linked course — link a course first on the Workshop Information tab.</div>
            <div id="lw-group-no-groups" style="display:none" class="lw-empty-notice">No groups found in the linked course. Add groups via Site Admin &rarr; Course &rarr; Groups.</div>
            <form id="lw-group-form" style="display:none">
                <input type="hidden" name="action" value="enrol_group">
                <input type="hidden" name="workshopid" value="<?php echo $id; ?>">
                <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                <div class="lw-form-row">
                    <label>Group *</label>
                    <select name="groupid" id="lw-group-select" class="form-control" required></select>
                    <small id="lw-group-member-count" class="form-text text-muted"></small>
                </div>
                <div class="lw-form-row">
                    <label>Booking Status</label>
                    <select name="bookingstatus" class="form-control">
                        <?php foreach (local_workshops_get_booking_statuses() as $bs): ?>
                        <option value="<?php echo s($bs); ?>"><?php echo s($bs); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="lw-form-row lw-form-check">
                    <label>
                        <input type="checkbox" name="enrolcourse" value="1" id="lw-enrol-course-chk" checked>
                        Also enrol members into the linked Moodle course (as students)
                    </label>
                </div>
                <div class="lw-modal-footer">
                    <button type="submit" class="btn btn-primary" id="lw-group-submit">Enrol Group</button>
                    <button type="button" class="btn btn-secondary lw-group-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php endif;
}

// ===========================
// TAB: DOCUMENTS
// ===========================
if ($tab === 'documents') {
    $documents = $DB->get_records_sql(
        "SELECT d.*, u.firstname, u.lastname
         FROM {local_workshop_documents} d
         JOIN {user} u ON u.id = d.uploadedby
         WHERE d.workshopid = :wid
         ORDER BY d.timecreated DESC",
        ['wid' => $id]
    );

    if ($canManDoc) {
        echo '<div class="lw-section-actions">';
        echo '<h4 style="margin:0 1rem 0 0">Upload Document</h4>';
        echo '</div>';
        ?>
        <form id="lw-upload-form" enctype="multipart/form-data" method="post"
              action="<?php echo (new moodle_url('/local/workshops/ajax.php'))->out(false); ?>">
            <input type="hidden" name="action" value="upload_document">
            <input type="hidden" name="workshopid" value="<?php echo $id; ?>">
            <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
            <div class="lw-upload-row">
                <select name="documenttype" class="form-control" style="width:200px">
                    <?php foreach (local_workshops_get_document_types() as $dt): ?>
                    <option value="<?php echo s($dt); ?>"><?php echo s($dt); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="file" name="docfile" class="form-control" required>
                <button type="submit" class="btn btn-primary btn-sm">Upload</button>
            </div>
        </form>
        <?php
    }

    if (empty($documents)) {
        echo '<p class="lw-empty-notice">' . get_string('nodocuments', 'local_workshops') . '</p>';
    } else {
        echo '<table class="lw-table table table-sm">';
        echo '<thead><tr><th>Type</th><th>File Name</th><th>Size</th><th>Uploaded By</th><th>Date</th>';
        if ($canManDoc) echo '<th>Actions</th>';
        echo '</tr></thead><tbody>';

        foreach ($documents as $doc) {
            echo '<tr>';
            echo '<td><span class="lw-badge lw-badge-doctype">' . s($doc->documenttype) . '</span></td>';
            echo '<td>' . s($doc->filename) . '</td>';
            echo '<td>' . local_workshops_format_filesize($doc->filesize) . '</td>';
            echo '<td>' . s($doc->firstname . ' ' . $doc->lastname) . '</td>';
            echo '<td>' . userdate($doc->timecreated, '%d %b %Y') . '</td>';
            if ($canManDoc) {
                echo '<td>';
                echo '<a href="' . (new moodle_url('/local/workshops/ajax.php', [
                    'action' => 'download_document', 'docid' => $doc->id, 'sesskey' => sesskey()
                ]))->out() . '" class="btn btn-xs btn-outline-secondary">Download</a> ';
                echo '<button type="button" class="btn btn-xs btn-outline-danger lw-delete-doc" data-id="' . $doc->id . '">Delete</button>';
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}

// ===========================
// TAB: CHECKLIST
// ===========================
if ($tab === 'checklist') {
    $tasks = $DB->get_records_sql(
        "SELECT t.*, u.firstname AS assignedfirst, u.lastname AS assignedlast
         FROM {local_workshop_tasks} t
         LEFT JOIN {user} u ON u.id = t.assignedto
         WHERE t.workshopid = :wid
         ORDER BY t.sortorder, t.id",
        ['wid' => $id]
    );

    $totalTasks     = count($tasks);
    $completedTasks = 0;
    foreach ($tasks as $t) {
        if ($t->taskstatus === 'Complete') $completedTasks++;
    }

    if ($canManTask) {
        echo '<div class="lw-section-actions">';
        echo '<button type="button" class="btn btn-primary btn-sm" id="lw-btn-add-task">+ Add Task</button>';
        echo '</div>';
    }

    if ($totalTasks > 0) {
        $pct = round(($completedTasks / $totalTasks) * 100);
        echo '<div class="lw-progress-bar-wrap">';
        echo '<div class="lw-progress-bar"><div class="lw-progress-fill" style="width:' . $pct . '%"></div></div>';
        echo '<span class="lw-progress-label">' . $completedTasks . ' / ' . $totalTasks . ' tasks complete (' . $pct . '%)</span>';
        echo '</div>';
    }

    if (empty($tasks)) {
        echo '<p class="lw-empty-notice">' . get_string('notasks', 'local_workshops') . '</p>';
    } else {
        echo '<div class="lw-checklist">';
        foreach ($tasks as $t) {
            $isComplete = $t->taskstatus === 'Complete';
            $completedClass = $isComplete ? ' lw-task-complete' : '';
            $due = $t->duedate ? userdate($t->duedate, '%d %b %Y') : '';
            $assigned = ($t->assignedfirst || $t->assignedlast) ? s($t->assignedfirst . ' ' . $t->assignedlast) : '';

            echo '<div class="lw-task-item' . $completedClass . '" data-taskid="' . $t->id . '">';
            echo '<div class="lw-task-check">';
            if ($canManTask) {
                echo '<input type="checkbox" class="lw-task-toggle" data-taskid="' . $t->id . '" ' . ($isComplete ? 'checked' : '') . '>';
            } else {
                echo '<input type="checkbox" ' . ($isComplete ? 'checked' : '') . ' disabled>';
            }
            echo '</div>';
            echo '<div class="lw-task-body">';
            echo '<span class="lw-task-name">' . s($t->taskname) . '</span>';
            if ($assigned || $due) {
                echo '<span class="lw-task-meta">';
                if ($assigned) echo '<span class="lw-task-assigned">' . $assigned . '</span>';
                if ($due)      echo '<span class="lw-task-due">Due: ' . $due . '</span>';
                echo '</span>';
            }
            echo '</div>';
            echo '<div class="lw-task-status">';
            $statusBadges = ['Pending' => 'lw-badge-draft', 'In Progress' => 'lw-badge-scheduled', 'Complete' => 'lw-badge-completed'];
            $sBadge = isset($statusBadges[$t->taskstatus]) ? $statusBadges[$t->taskstatus] : 'lw-badge-draft';
            echo '<span class="lw-badge ' . $sBadge . '">' . s($t->taskstatus) . '</span>';
            echo '</div>';
            if ($canManTask) {
                echo '<div class="lw-task-actions">';
                echo '<button type="button" class="btn btn-xs btn-outline-danger lw-delete-task" data-id="' . $t->id . '">Remove</button>';
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    // Add task form
    if ($canManTask):
    ?>
    <div id="lw-task-modal" class="lw-modal" style="display:none">
        <div class="lw-modal-overlay"></div>
        <div class="lw-modal-dialog">
            <h3>Add Task</h3>
            <form id="lw-task-form">
                <input type="hidden" name="action" value="add_task">
                <input type="hidden" name="workshopid" value="<?php echo $id; ?>">
                <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                <div class="lw-form-row">
                    <label>Task Name *</label>
                    <input type="text" name="taskname" class="form-control" required>
                </div>
                <div class="lw-form-row">
                    <label>Due Date</label>
                    <input type="date" name="duedate" class="form-control">
                </div>
                <div class="lw-form-row">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="lw-modal-footer">
                    <button type="submit" class="btn btn-primary">Add Task</button>
                    <button type="button" class="btn btn-secondary lw-modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif;
}

// ===========================
// TAB: MOODLE INFORMATION
// ===========================
if ($tab === 'moodle') {
    if (!$course) {
        echo '<p class="lw-empty-notice">' . get_string('nocourse', 'local_workshops') . '</p>';
    } else {
        // Enrolments
        $enrolcount = $DB->count_records('enrol', ['courseid' => $course->id]);
        $enrolcount = $DB->count_records_sql(
            "SELECT COUNT(*) FROM {user_enrolments} ue JOIN {enrol} e ON e.id = ue.enrolid WHERE e.courseid = :cid AND ue.status = 0",
            ['cid' => $course->id]
        );

        // Completions
        $completioncount = $DB->count_records_sql(
            "SELECT COUNT(*) FROM {course_completions} WHERE course = :cid AND timecompleted > 0",
            ['cid' => $course->id]
        );

        // Attendance sessions (if plugin exists)
        $attendancesessions = 0;
        $attendanceplugininstalled = $DB->get_manager()->table_exists('attendance_sessions');
        if ($attendanceplugininstalled) {
            $attendancesessions = $DB->count_records_sql(
                "SELECT COUNT(*) FROM {attendance_sessions} s
                 JOIN {attendance} a ON a.id = s.attendanceid
                 WHERE a.course = :cid",
                ['cid' => $course->id]
            );
        }

        // Certificates (support customcert and certificate plugins)
        $certcount = 0;
        if ($DB->get_manager()->table_exists('customcert_issues')) {
            $certcount = $DB->count_records_sql(
                "SELECT COUNT(*) FROM {customcert_issues} ci
                 JOIN {customcert} c ON c.id = ci.customcertid
                 WHERE c.course = :cid",
                ['cid' => $course->id]
            );
        } else if ($DB->get_manager()->table_exists('certificate_issues')) {
            $certcount = $DB->count_records_sql(
                "SELECT COUNT(*) FROM {certificate_issues} ci
                 JOIN {certificate} c ON c.id = ci.certificateid
                 WHERE c.course = :cid",
                ['cid' => $course->id]
            );
        }

        echo '<div class="lw-moodle-info">';
        echo '<div class="lw-info-notice">This section shows read-only data pulled directly from Moodle. No data is duplicated or modified here.</div>';

        echo '<div class="lw-moodle-cards">';

        $cards = [
            ['icon' => '&#128218;', 'label' => 'Linked Course',        'value' => '<a href="' . (new moodle_url('/course/view.php', ['id' => $course->id]))->out() . '">' . s($course->fullname) . '</a>'],
            ['icon' => '&#128100;', 'label' => 'Active Enrolments',    'value' => number_format($enrolcount)],
            ['icon' => '&#9989;',   'label' => 'Course Completions',   'value' => number_format($completioncount)],
            ['icon' => '&#128197;', 'label' => 'Attendance Sessions',  'value' => $attendanceplugininstalled ? number_format($attendancesessions) : 'Attendance plugin not installed'],
            ['icon' => '&#127881;', 'label' => 'Certificates Issued',  'value' => $certcount > 0 ? number_format($certcount) : ($certcount === 0 ? '0' : 'Certificate plugin not installed')],
        ];

        foreach ($cards as $card) {
            echo '<div class="lw-moodle-card">';
            echo '<span class="lw-moodle-icon">' . $card['icon'] . '</span>';
            echo '<div class="lw-moodle-card-body">';
            echo '<div class="lw-moodle-card-label">' . $card['label'] . '</div>';
            echo '<div class="lw-moodle-card-value">' . $card['value'] . '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        echo '<p class="lw-moodle-link"><a href="' . (new moodle_url('/course/view.php', ['id' => $course->id]))->out() . '" target="_blank">Go to Course &rarr;</a></p>';
        echo '</div>';
    }
}

echo '</div>'; // .lw-tab-content
echo '</div>'; // .lw-container

echo $OUTPUT->footer();
