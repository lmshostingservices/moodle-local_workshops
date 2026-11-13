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
 * Plugin version and other meta-data.
 *
 * @package   local_workshops
 * @copyright 2026 AI Grader
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component  = 'local_workshops';
$plugin->version    = 2026111300;
$plugin->requires   = 2022041900;
$plugin->supported  = [400, 500];
$plugin->maturity   = MATURITY_STABLE;
$plugin->release    = '1.0.40'; // v1.0.40: FIX meeting URL not saving (PARAM_URL silently blanked pasted Teams links; now normalised + validated with a visible error). NEW Confirmed email with date/time and joining details, plus 'Email joining details' resend button. No DB schema change. // v1.0.39: Release-desk fixes — GPL header/docblock in db/upgrade.php; attendance email body setting now uses PARAM_TEXT; closure spacing in view.php; BUILD_INFO.json excluded from ZIP. // v1.0.38: FIX attendance email — course/site names with '&' were sent as '&amp;' (format_string escape); now requires an active enrolment before sending. // v1.0.37: Attendance email default wording: "Here's a link to complete the survey and assessment in the course {coursename}". Upgrade replaces the body only if unedited. // v1.0.36: ATTENDED-EMAIL — when a booking changes to Attended (bulk Attendance, Edit/Add participant, Enrol from Group) the learner is emailed the linked course URL to complete the survey/assessment. Editable subject/body with placeholders in settings; on by default; tickbox in bulk modal to skip. Not re-sent on sync or for people already Attended. No DB schema change. // v1.0.35: BULK-ATTENDANCE — 'Attendance' button in the Participants Actions header opens a modal listing all non-cancelled bookings with checkboxes (select all / unselect all / filter / master checkbox). Ticked -> Attended (course sync unlocks survey); unticked -> unchanged or No Show; unticking a current Attended reverts to Confirmed. New ajax action bulk_attendance. No DB schema change. // v1.0.34: PACKAGING — ZIP root workshops/, Keep-a-Changelog CHANGELOG.md, expanded README. No code change. // v1.0.33: COURSE-ACCESS — Bookings (manual add, CSV import, group enrol) with status Booked/Confirmed/Attended now find-or-create the Moodle account, enrol as Student in the linked course and add to a per-workshop group (idnumber lw_ws_<id>; adopts an existing group with the workshop's exact name). Attended adds the learner to a course-level 'Attended workshop' group (idnumber lw_attended) for gating the survey; any other status removes it. REMOVED force course completion on Attended (it bypassed completion criteria and could release certificates early). New sync.php + 'Sync to Moodle course' button, Course Access column, 3 admin settings. No DB schema change. // FIX-WORKSHOPS-FK-INDEX-COLLISION: Removed idx_workshopid from local_workshop_trainers table creation — FK already implies the index; duplicate caused Moodle XMLDB coding error blocking all upgrades from v1.0.24+. // v1.0.19: ADD-WEBINAR-FIELDS — Added Platform Name, Meeting ID, and Passcode fields to the Webinar Connection Details section. All three fields are shown when delivery type is Webinar or Hybrid, hidden for Face-to-Face (same hideIf rules as teamslink). Fields saved to new DB columns webinarplatform, webinarmeetingid, webinarpasscode (CHAR 100, nullable). Displayed in the Info tab of the workshop view. DB schema change: 3 columns added to local_workshops table via upgrade.php savepoint 2026061200019. Lang strings added. No AMD changes. // v1.0.18: (1) CRITICAL FIX: ajax.php action param changed from PARAM_ALPHA to PARAM_ALPHANUMEXT — PARAM_ALPHA strips underscores, so actions like save_participant/toggle_task/add_task/delete_task all became saveparticipant/toggletask/etc hitting the default: throw invalidaction. All AJAX on view.php was broken. (2) import.php confirm step now reads rows from POST hidden fields instead of requiring a second file upload. (3) "Create Workshop" renamed to "Add Workshop". (4) Added "In Progress" status. (5) Delivery-type-conditional field display: venue/room/address hidden for Webinar; Teams Link hidden for Face-to-Face. (6) Added capacity field (max participants). DB schema change: capacity INT(5) added to local_workshops.
$plugin->dependencies = [];
