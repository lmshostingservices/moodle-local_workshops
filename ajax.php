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
define('AJAX_SCRIPT', true);
require_once('../../config.php');
require_once($CFG->dirroot . '/local/workshops/lib.php');

require_login();
require_sesskey();

\core\session\manager::write_close();

$context = context_system::instance();
$action  = required_param('action', PARAM_ALPHANUMEXT);

header('Content-Type: application/json');

try {
    switch ($action) {

        // ----------------------------------------
        // save_participant (add or edit)
        // ----------------------------------------
        case 'save_participant': {
            require_capability('local/workshops:manageparticipants', $context);
            $workshopid    = required_param('workshopid',    PARAM_INT);
            $participantid = optional_param('participantid', 0, PARAM_INT);
            $firstname     = required_param('firstname',  PARAM_TEXT);
            $lastname      = required_param('lastname',   PARAM_TEXT);
            $email         = required_param('email',      PARAM_EMAIL);
            $organisation  = optional_param('organisation', '', PARAM_TEXT);
            $bookingstatus = optional_param('bookingstatus', 'Booked', PARAM_TEXT);
            $notes         = optional_param('notes', '', PARAM_TEXT);

            // Validate email format
            if (!validate_email($email)) {
                throw new moodle_exception('invalidemail');
            }

            // Look up Moodle user by email
            $moodleuser = $DB->get_record('user', ['email' => $email, 'deleted' => 0]);

            if ($participantid > 0) {
                // Update existing
                $rec = $DB->get_record('local_workshop_participants', ['id' => $participantid], '*', MUST_EXIST);
                $rec->firstname     = $firstname;
                $rec->lastname      = $lastname;
                $rec->email         = $email;
                $rec->organisation  = $organisation;
                $rec->bookingstatus = $bookingstatus;
                $rec->notes         = $notes;
                $rec->userid        = $moodleuser ? $moodleuser->id : null;
                $DB->update_record('local_workshop_participants', $rec);

                // If marking as Attended, push course completion to Moodle.
                if ($bookingstatus === 'Attended' && $moodleuser) {
                    local_workshops_mark_course_complete($workshopid, $moodleuser->id);
                }

                echo json_encode(['success' => true, 'message' => 'Participant updated.']);
            } else {
                // Insert new
                $rec = (object)[
                    'workshopid'    => $workshopid,
                    'userid'        => $moodleuser ? $moodleuser->id : null,
                    'firstname'     => $firstname,
                    'lastname'      => $lastname,
                    'email'         => $email,
                    'organisation'  => $organisation,
                    'bookingstatus' => $bookingstatus,
                    'bookingdate'   => time(),
                    'notes'         => $notes,
                ];
                $newid = $DB->insert_record('local_workshop_participants', $rec);

                // If creating directly as Attended, push course completion to Moodle.
                if ($bookingstatus === 'Attended' && $moodleuser) {
                    local_workshops_mark_course_complete($workshopid, $moodleuser->id);
                }

                echo json_encode(['success' => true, 'id' => $newid, 'message' => get_string('participantadded', 'local_workshops')]);
            }
            break;
        }

        // ----------------------------------------
        // remove_participant
        // ----------------------------------------
        case 'remove_participant': {
            require_capability('local/workshops:manageparticipants', $context);
            $participantid = required_param('participantid', PARAM_INT);
            $DB->delete_records('local_workshop_participants', ['id' => $participantid]);
            echo json_encode(['success' => true, 'message' => get_string('participantremoved', 'local_workshops')]);
            break;
        }

        // ----------------------------------------
        // toggle_task
        // ----------------------------------------
        case 'toggle_task': {
            require_capability('local/workshops:managetasks', $context);
            $taskid  = required_param('taskid', PARAM_INT);
            $checked = optional_param('checked', 0, PARAM_INT);
            $task = $DB->get_record('local_workshop_tasks', ['id' => $taskid], '*', MUST_EXIST);
            $task->taskstatus    = $checked ? 'Complete' : 'Pending';
            $task->completeddate = $checked ? time() : null;
            $DB->update_record('local_workshop_tasks', $task);
            echo json_encode(['success' => true, 'status' => $task->taskstatus]);
            break;
        }

        // ----------------------------------------
        // add_task
        // ----------------------------------------
        case 'add_task': {
            require_capability('local/workshops:managetasks', $context);
            $workshopid = required_param('workshopid', PARAM_INT);
            $taskname   = required_param('taskname', PARAM_TEXT);
            $duedate    = optional_param('duedate', '', PARAM_TEXT);
            $notes      = optional_param('notes', '', PARAM_TEXT);

            $maxorder = $DB->get_field_sql(
                'SELECT MAX(sortorder) FROM {local_workshop_tasks} WHERE workshopid = ?', [$workshopid]
            );
            $rec = (object)[
                'workshopid'    => $workshopid,
                'taskname'      => $taskname,
                'taskstatus'    => 'Pending',
                'assignedto'    => null,
                'duedate'       => $duedate ? strtotime($duedate) : null,
                'completeddate' => null,
                'notes'         => $notes,
                'sortorder'     => ($maxorder ?: 0) + 10,
            ];
            $newid = $DB->insert_record('local_workshop_tasks', $rec);
            echo json_encode(['success' => true, 'id' => $newid]);
            break;
        }

        // ----------------------------------------
        // delete_task
        // ----------------------------------------
        case 'delete_task': {
            require_capability('local/workshops:managetasks', $context);
            $taskid = required_param('taskid', PARAM_INT);
            $DB->delete_records('local_workshop_tasks', ['id' => $taskid]);
            echo json_encode(['success' => true]);
            break;
        }

        // ----------------------------------------
        // upload_document (non-AJAX, form POST)
        // ----------------------------------------
        case 'upload_document': {
            require_capability('local/workshops:managedocuments', $context);
            $workshopid   = required_param('workshopid', PARAM_INT);
            $documenttype = required_param('documenttype', PARAM_TEXT);

            if (!isset($_FILES['docfile']) || $_FILES['docfile']['error'] !== UPLOAD_ERR_OK) {
                throw new moodle_exception('errorupload', 'local_workshops');
            }

            $file     = $_FILES['docfile'];
            $filename = clean_filename($file['name']);
            $filesize = $file['size'];
            $tmppath  = $file['tmp_name'];

            // Store in Moodle file system.
            $fs = get_file_storage();
            $fileinfo = [
                'contextid' => $context->id,
                'component' => 'local_workshops',
                'filearea'  => 'documents',
                'itemid'    => $workshopid,
                'filepath'  => '/',
                'filename'  => $filename,
            ];
            // Remove old file with same name if exists.
            $existingfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
            if ($existingfile) {
                $existingfile->delete();
            }
            $storedfile = $fs->create_file_from_pathname($fileinfo, $tmppath);
            $fileurl = moodle_url::make_pluginfile_url(
                $context->id, 'local_workshops', 'documents', $workshopid, '/', $filename
            )->out();

            $rec = (object)[
                'workshopid'   => $workshopid,
                'filename'     => $filename,
                'filepath'     => $fileurl,
                'filesize'     => $filesize,
                'documenttype' => $documenttype,
                'uploadedby'   => $USER->id,
                'timecreated'  => time(),
            ];
            $DB->insert_record('local_workshop_documents', $rec);

            // Return JSON — cannot call redirect() from an AJAX_SCRIPT context.
            echo json_encode(['success' => true, 'message' => get_string('documentuploaded', 'local_workshops')]);
            break;
        }

        // ----------------------------------------
        // delete_document
        // ----------------------------------------
        case 'delete_document': {
            require_capability('local/workshops:managedocuments', $context);
            $docid = required_param('docid', PARAM_INT);
            $doc   = $DB->get_record('local_workshop_documents', ['id' => $docid], '*', MUST_EXIST);

            // Delete from file system.
            $fs = get_file_storage();
            $stored = $fs->get_file($context->id, 'local_workshops', 'documents', $doc->workshopid, '/', $doc->filename);
            if ($stored) $stored->delete();

            $DB->delete_records('local_workshop_documents', ['id' => $docid]);
            echo json_encode(['success' => true, 'message' => get_string('documentdeleted', 'local_workshops')]);
            break;
        }

        // ----------------------------------------
        // download_document
        // ----------------------------------------
        case 'download_document': {
            require_capability('local/workshops:view', $context);
            $docid = required_param('docid', PARAM_INT);
            $doc   = $DB->get_record('local_workshop_documents', ['id' => $docid], '*', MUST_EXIST);

            $fs     = get_file_storage();
            $stored = $fs->get_file($context->id, 'local_workshops', 'documents', $doc->workshopid, '/', $doc->filename);
            if (!$stored) {
                throw new moodle_exception('filenotfound');
            }
            send_stored_file($stored, 86400, 0, true);
            break;
        }

        // ----------------------------------------
        // export_participants
        // ----------------------------------------
        case 'export_participants': {
            require_capability('local/workshops:manageparticipants', $context);
            $workshopid = required_param('workshopid', PARAM_INT);
            $workshop   = $DB->get_record('local_workshops', ['id' => $workshopid], '*', MUST_EXIST);

            $participants = $DB->get_records('local_workshop_participants', ['workshopid' => $workshopid], 'lastname, firstname');

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="participants_' . $workshop->workshopcode . '.csv"');
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['firstname', 'lastname', 'email', 'organisation', 'bookingstatus', 'bookingdate']);
            foreach ($participants as $p) {
                fputcsv($fh, [
                    $p->firstname,
                    $p->lastname,
                    $p->email,
                    $p->organisation,
                    $p->bookingstatus,
                    date('Y-m-d', $p->bookingdate),
                ]);
            }
            fclose($fh);
            exit;
        }

        // ----------------------------------------
        // get_groups — return Moodle groups for the workshop's linked course
        // ----------------------------------------
        case 'get_groups': {
            require_capability('local/workshops:manageparticipants', $context);
            $workshopid = required_param('workshopid', PARAM_INT);
            $workshop   = $DB->get_record('local_workshops', ['id' => $workshopid], '*', MUST_EXIST);

            if (!$workshop->courseid) {
                echo json_encode(['success' => true, 'groups' => [], 'haslinkedcourse' => false]);
                break;
            }

            $groups = $DB->get_records('groups', ['courseid' => $workshop->courseid], 'name ASC', 'id, name');
            $result = [];
            foreach ($groups as $g) {
                $membercount = $DB->count_records('groups_members', ['groupid' => $g->id]);
                $result[] = ['id' => (int)$g->id, 'name' => $g->name, 'count' => $membercount];
            }
            echo json_encode(['success' => true, 'groups' => $result, 'haslinkedcourse' => true]);
            break;
        }

        // ----------------------------------------
        // enrol_group — enrol all members of a Moodle group as workshop participants
        // ----------------------------------------
        case 'enrol_group': {
            require_capability('local/workshops:manageparticipants', $context);
            $workshopid    = required_param('workshopid', PARAM_INT);
            $groupid       = required_param('groupid', PARAM_INT);
            $bookingstatus = optional_param('bookingstatus', 'Booked', PARAM_TEXT);
            $enrolcourse   = optional_param('enrolcourse', 1, PARAM_INT);

            $workshop = $DB->get_record('local_workshops', ['id' => $workshopid], '*', MUST_EXIST);
            $group    = $DB->get_record('groups', ['id' => $groupid], '*', MUST_EXIST);

            // Verify group belongs to the workshop's linked course
            if ($workshop->courseid && (int)$group->courseid !== (int)$workshop->courseid) {
                throw new moodle_exception('invalidgroup', 'local_workshops');
            }

            // Get group members (active, non-deleted, non-suspended)
            $members = $DB->get_records_sql(
                "SELECT u.id, u.firstname, u.lastname, u.email
                 FROM {user} u
                 JOIN {groups_members} gm ON gm.userid = u.id
                 WHERE gm.groupid = :gid AND u.deleted = 0 AND u.suspended = 0",
                ['gid' => $groupid]
            );

            $added  = 0;
            $skipped = 0;

            // Prepare course enrolment via manual enrolment plugin
            $enrolplugin    = null;
            $manualinstance = null;
            $studentroleid  = null;
            if ($enrolcourse && $workshop->courseid) {
                $enrolplugin = enrol_get_plugin('manual');
                $instances   = enrol_get_instances($workshop->courseid, true);
                foreach ($instances as $inst) {
                    if ($inst->enrol === 'manual') {
                        $manualinstance = $inst;
                        break;
                    }
                }
                $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
            }

            foreach ($members as $u) {
                // Skip if already a participant (by userid or by email)
                $existsbyuser = $DB->record_exists('local_workshop_participants',
                    ['workshopid' => $workshopid, 'userid' => $u->id]);
                $existsbyemail = !$existsbyuser && $DB->record_exists_sql(
                    "SELECT 1 FROM {local_workshop_participants} WHERE workshopid = :wid AND email = :email",
                    ['wid' => $workshopid, 'email' => $u->email]
                );
                if ($existsbyuser || $existsbyemail) {
                    $skipped++;
                    continue;
                }

                // Add as workshop participant
                $DB->insert_record('local_workshop_participants', (object)[
                    'workshopid'    => $workshopid,
                    'userid'        => $u->id,
                    'firstname'     => $u->firstname,
                    'lastname'      => $u->lastname,
                    'email'         => $u->email,
                    'organisation'  => '',
                    'bookingstatus' => $bookingstatus,
                    'bookingdate'   => time(),
                    'notes'         => 'Auto-enrolled from group: ' . $group->name,
                ]);
                $added++;

                // Enrol into linked Moodle course if not already enrolled
                if ($manualinstance && $enrolplugin && $studentroleid) {
                    $isenrolled = $DB->record_exists_sql(
                        "SELECT 1 FROM {user_enrolments} ue
                         JOIN {enrol} e ON e.id = ue.enrolid
                         WHERE e.courseid = :cid AND ue.userid = :uid",
                        ['cid' => $workshop->courseid, 'uid' => $u->id]
                    );
                    if (!$isenrolled) {
                        $enrolplugin->enrol_user($manualinstance, $u->id, $studentroleid);
                    }
                }
            }

            $msg = $added . ' participant' . ($added !== 1 ? 's' : '') . ' added';
            if ($skipped > 0) {
                $msg .= ', ' . $skipped . ' already registered (skipped)';
            }
            echo json_encode(['success' => true, 'added' => $added, 'skipped' => $skipped, 'message' => $msg . '.']);
            break;
        }

        // ----------------------------------------
        // get_workshops — return all workshops for the availability condition form
        // ----------------------------------------
        case 'get_workshops': {
            require_capability('local/workshops:view', $context);
            $workshops = $DB->get_records_sql(
                "SELECT id, workshopname, workshopcode, status, startdatetime
                   FROM {local_workshops}
                  ORDER BY workshopname ASC",
                []
            );
            $result = [];
            foreach ($workshops as $w) {
                $result[] = [
                    'id'           => (int) $w->id,
                    'workshopname' => $w->workshopname,
                    'workshopcode' => $w->workshopcode,
                    'status'       => $w->status,
                    'startdate'    => $w->startdatetime ? date('d M Y', $w->startdatetime) : '',
                ];
            }
            echo json_encode(['success' => true, 'workshops' => $result]);
            break;
        }

        default:
            throw new moodle_exception('invalidaction', 'local_workshops');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
