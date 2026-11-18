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
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Workshop Scheduler';
$string['workshops:view'] = 'View workshops';
$string['workshops:create'] = 'Create workshops';
$string['workshops:edit'] = 'Edit workshops';
$string['workshops:delete'] = 'Delete workshops';
$string['workshops:manageparticipants'] = 'Manage workshop participants';
$string['workshops:managedocuments'] = 'Manage workshop documents';
$string['workshops:managetasks'] = 'Manage workshop tasks';

// Navigation
$string['manageworkshops'] = 'Manage Workshops';
$string['createworkshop'] = 'Add Workshop';
$string['editworkshop'] = 'Edit Workshop';
$string['viewworkshop'] = 'View Workshop';
$string['workshopdetail'] = 'Workshop Detail';
$string['workshops'] = 'Workshops';

// Workshop fields
$string['workshopcode'] = 'Workshop Code';
$string['workshopname'] = 'Workshop Name';
$string['program'] = 'Program';
$string['linkedcourse'] = 'Linked Moodle Course';
$string['trainer'] = 'Trainer';
$string['trainers'] = 'Trainer(s)';
$string['status'] = 'Status';
$string['deliverytype'] = 'Delivery Type';
$string['startdatetime'] = 'Start Date & Time';
$string['enddatetime'] = 'End Date & Time';
$string['venue'] = 'Venue';
$string['room'] = 'Room';
$string['address'] = 'Address';
$string['teamslink'] = 'Webinar/Meeting URL';
$string['webinarplatform'] = 'Platform Name';
$string['webinarmeetingid'] = 'Meeting ID';
$string['webinarpasscode'] = 'Passcode';
$string['cateringrequired'] = 'Catering Required';
$string['cateringnotes'] = 'Catering Notes';
$string['customername'] = 'Customer Name';
$string['customercontact'] = 'Customer Contact';
$string['specialrequirements'] = 'Special Requirements';
$string['notes'] = 'Notes';
$string['capacity'] = 'Capacity (max participants)';

// Status values
$string['status_draft'] = 'Draft';
$string['status_planned'] = 'Planned';
$string['status_scheduled'] = 'Scheduled';
$string['status_confirmed'] = 'Confirmed';
$string['status_inprogress'] = 'In Progress';
$string['status_waitlistopen'] = 'Waitlist Open';
$string['status_completed'] = 'Completed';
$string['status_cancelled'] = 'Cancelled';
$string['status_postponed'] = 'Postponed';

// Delivery types
$string['delivery_f2f'] = 'Face-to-Face';
$string['delivery_webinar'] = 'Webinar';
$string['delivery_hybrid'] = 'Hybrid';

// Participants
$string['participants'] = 'Participants';
$string['addparticipant'] = 'Add Participant';
$string['removeparticipant'] = 'Remove Participant';
$string['bulkimport'] = 'Bulk Import';
$string['exportparticipants'] = 'Export';
$string['participantname'] = 'Name';
$string['participantemail'] = 'Email';
$string['participantorg'] = 'Organisation';
$string['bookingstatus'] = 'Booking Status';
$string['bookingdate'] = 'Booking Date';
$string['addexternalparticipant'] = 'Add External Participant';
$string['addmoodleuser'] = 'Add Existing Moodle User';
$string['createmoodleuser'] = 'Create Moodle User';
$string['enrolintocourse'] = 'Enrol Into Linked Course';
$string['searchmoodleusers'] = 'Search Moodle Users';
$string['firstname'] = 'First Name';
$string['lastname'] = 'Last Name';
$string['email'] = 'Email';
$string['organisation'] = 'Organisation';
$string['phone'] = 'Phone';
$string['participantnotes'] = 'Notes';
$string['noparticipants'] = 'No participants booked yet.';

// Booking statuses
$string['booking_booked'] = 'Booked';
$string['booking_confirmed'] = 'Confirmed';
$string['booking_waitlisted'] = 'Waitlisted';
$string['booking_cancelled'] = 'Cancelled';
$string['booking_attended'] = 'Attended';
$string['booking_noshow'] = 'No Show';

// Documents
$string['documents'] = 'Documents';
$string['uploaddocument'] = 'Upload Document';
$string['documenttype'] = 'Document Type';
$string['documentname'] = 'File Name';
$string['uploadedby'] = 'Uploaded By';
$string['uploaddate'] = 'Upload Date';
$string['filesize'] = 'File Size';
$string['nodocuments'] = 'No documents uploaded yet.';
$string['doctype_venuebooking'] = 'Venue Booking';
$string['doctype_cateringorder'] = 'Catering Order';
$string['doctype_trainerbrief'] = 'Trainer Brief';
$string['doctype_clientdoc'] = 'Client Documentation';
$string['doctype_resources'] = 'Workshop Resources';
$string['doctype_other'] = 'Other';

// Tasks / Checklist
$string['checklist'] = 'Checklist';
$string['taskname'] = 'Task';
$string['taskstatus'] = 'Status';
$string['assignedto'] = 'Assigned To';
$string['duedate'] = 'Due Date';
$string['completeddate'] = 'Completed';
$string['tasknotes'] = 'Notes';
$string['addtask'] = 'Add Task';
$string['notasks'] = 'No tasks defined yet.';
$string['task_pending'] = 'Pending';
$string['task_inprogress'] = 'In Progress';
$string['task_complete'] = 'Complete';

// Moodle Information section
$string['moodleinfo'] = 'Moodle Information';
$string['linkedcourseinfo'] = 'Linked Course';
$string['enrolmentcount'] = 'Enrolment Count';
$string['attendancesessions'] = 'Attendance Sessions';
$string['completioncount'] = 'Completion Count';
$string['certificatecount'] = 'Certificates Issued';
$string['nocourse'] = 'No Moodle course linked.';

// Filters
$string['filterprogram'] = 'Program';
$string['filtertrainer'] = 'Trainer';
$string['filterstatus'] = 'Status';
$string['filterdatefrom'] = 'Date From';
$string['filterdateto'] = 'Date To';
$string['filterdelivery'] = 'Delivery Type';
$string['filtercustomer'] = 'Customer';
$string['filtersearch'] = 'Search';
$string['allprograms'] = 'All Programs';
$string['alltrainers'] = 'All Trainers';
$string['allstatuses'] = 'All Statuses';
$string['alldeliverytypes'] = 'All Delivery Types';
$string['clearfilters'] = 'Clear Filters';
$string['applyfilters'] = 'Apply Filters';

// Table columns
$string['participantcount'] = 'Participants';
$string['actions'] = 'Actions';
$string['date'] = 'Date';

// Actions
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['view'] = 'View';
$string['save'] = 'Save';
$string['cancel'] = 'Cancel';
$string['confirm'] = 'Confirm';
$string['back'] = 'Back';
$string['download'] = 'Download';

// Messages
$string['workshopsaved'] = 'Workshop saved successfully.';
$string['workshopdeleted'] = 'Workshop deleted.';
$string['participantadded'] = 'Participant added.';
$string['participantremoved'] = 'Participant removed.';
$string['documentuploaded'] = 'Document uploaded.';
$string['documentdeleted'] = 'Document deleted.';
$string['tasksaved'] = 'Task updated.';
$string['taskdeleted'] = 'Task deleted.';
$string['confirmdeleteworkshop'] = 'Are you sure you want to delete this workshop? All participants, documents and tasks will also be deleted.';
$string['confirmdelete'] = 'Are you sure?';
$string['importcomplete'] = 'Import complete: {$a->imported} imported, {$a->skipped} skipped.';
$string['importerror'] = 'Import error on row {$a->row}: {$a->error}';
$string['nopermission'] = 'You do not have permission to do this.';
$string['workshopnotfound'] = 'Workshop not found.';

// Settings
$string['settings'] = 'Settings';
$string['programs_setting'] = 'Programs';
$string['programs_setting_desc'] = 'One program per line. These appear in the Program dropdown when creating or editing a workshop.';

// Bulk import
$string['importparticipants'] = 'Import Participants';
$string['importcsv'] = 'CSV File';
$string['importcsv_help'] = 'Upload a CSV file with columns: firstname, lastname, email, organisation';
$string['importpreview'] = 'Import Preview';
$string['importrow'] = 'Row';
$string['importstatus'] = 'Status';
$string['importok'] = 'OK';
$string['importduplicate'] = 'Duplicate';
$string['importinvalid'] = 'Invalid';
$string['doImport'] = 'Import';

// No workshops
$string['noworkshops'] = 'No workshops found. {$a}';
$string['createfirst'] = 'Create your first workshop.';
$string['workshopinformation'] = 'Workshop Information';
$string['participantcountlabel'] = '{$a} participant(s)';

// Tabs
$string['tabinfo'] = 'Workshop Information';
$string['tabparticipants'] = 'Participants';
$string['tabdocuments'] = 'Documents';
$string['tabchecklist'] = 'Checklist';
$string['tabmoodle'] = 'Moodle Info';

// Course sync (v1.0.33)
$string['coursesync_heading'] = 'Linked course access';
$string['coursesync_heading_desc'] = 'How workshop bookings give learners access to the linked Moodle (Program) course. Each workshop gets its own group in the course; learners marked Attended are also added to a course-level "attended" group. Restrict the survey (and the assessment/certificate that follow it) to that group.';
$string['autoenrol'] = 'Enrol bookings into the linked course';
$string['autoenrol_desc'] = 'When a participant is Booked, Confirmed or Attended, enrol them as a Student in the workshop\'s linked course and add them to the workshop group. Cancelled, Waitlisted and No Show bookings are not enrolled. Learners are never unenrolled automatically.';
$string['autocreateusers'] = 'Create Moodle accounts for new participants';
$string['autocreateusers_desc'] = 'If no Moodle account exists for the participant\'s email, create one (manual authentication). Moodle emails the login details via its scheduled task and forces a password change on first login. Requires the moodle/user:create capability.';
$string['attendedgroupname'] = 'Attended group name';
$string['attendedgroupname_desc'] = 'Name of the group created in each linked course for learners whose booking is marked Attended. Used only when the group is first created.';
$string['attendedgroupname_default'] = 'Attended workshop';
$string['syncparticipants'] = 'Sync to Moodle course';
$string['syncparticipants_help'] = 'Create missing accounts, enrol all active bookings into the linked course, and update the workshop and attended groups. Safe to run again.';
$string['lw_alreadybooked'] = 'This email address is already booked on this workshop.';
$string['lw_manualenroldisabled'] = 'The Manual enrolments plugin is disabled on this site, so learners cannot be enrolled automatically.';

$string['privacy:metadata'] = 'The local_workshops plugin does not store any personal data.';

// v1.0.35: Attended email.
$string['attendedemail_heading'] = 'Attendance email';
$string['attendedemail_heading_desc'] = 'When a booking changes to Attended (bulk Attendance, Edit participant, or Enrol from Group), the learner is emailed a link to the linked course so they can complete the survey and assessment. Sent only once per change to Attended, not when re-syncing. Requires a linked course and an enrolled Moodle account.';
$string['attendedemail'] = 'Email learners when marked Attended';
$string['attendedemail_desc'] = 'Send the email below when a participant is marked Attended.';
$string['attendedemailsubject'] = 'Email subject';
$string['attendedemailbody'] = 'Email body';
$string['attendedemail_placeholders'] = 'Placeholders: {firstname}, {lastname}, {fullname}, {username}, {workshopname}, {workshopdate}, {coursename}, {courseurl}, {loginurl}, {forgotpasswordurl}, {sitename}. Plain text; links become clickable.';
$string['attendedemailsubject_default'] = '{coursename}: complete your assessment';
$string['attendedemailbody_default'] = 'Hi {firstname},

Thank you for attending {workshopname} on {workshopdate}.

Here\'s a link to complete the survey and assessment in the course {coursename}:

{courseurl}

Your username is: {username}

If this is your first time logging in, your login details are emailed to you separately. If you have forgotten your password, you can reset it here:
{forgotpasswordurl}

Kind regards,
{sitename}';
$string['sendattendedemail'] = 'Email newly attended participants the course link';

// v1.0.40: Confirmed email + meeting URL validation.
$string['invalidmeetingurl'] = 'Enter a full web address starting with https:// (for example the Teams "Join the meeting" link).';
$string['confirmedemail_heading'] = 'Booking confirmation email';
$string['confirmedemail_heading_desc'] = 'When a booking changes to Confirmed (Add/Edit participant or Enrol from Group), the participant is emailed the workshop date/time and joining details (meeting link, platform, meeting ID and passcode; or venue for Face-to-Face). Not sent when an Attended booking is reverted to Confirmed. For Webinar and Hybrid workshops it is only sent once the workshop has a meeting link. Use "Email joining details" on the Participants tab to (re)send to everyone Confirmed, e.g. after the link changes.';
$string['confirmedemail'] = 'Email participants when Confirmed';
$string['confirmedemail_desc'] = 'Send the email below when a booking is changed to Confirmed.';
$string['confirmedemailsubject'] = 'Email subject';
$string['confirmedemailbody'] = 'Email body';
$string['confirmedemail_placeholders'] = 'Placeholders: {firstname}, {lastname}, {fullname}, {username}, {workshopname}, {workshopdate}, {workshopwhen} (date, start-end time and time zone; for multi-session workshops the date range), {joiningdetails} (link, platform, meeting ID, passcode and/or venue; for multi-session workshops a block per session with its date and time), {calendarnote} (a line about the attached calendar file, blank if none), {meetingurl}, {platform}, {meetingid}, {passcode}, {coursename}, {courseurl}, {loginurl}, {sitename}. Plain text; links become clickable.';
$string['confirmedemailsubject_default'] = 'Confirmed: {workshopname} - joining details';
$string['confirmedemailbody_default'] = 'Hi {firstname},

Your place in {workshopname} is confirmed.

When: {workshopwhen}

{joiningdetails}

{calendarnote}

Please join a few minutes before the start time.

Kind regards,
{sitename}';
$string['sendjoiningdetails'] = 'Email joining details';
$string['sendjoiningdetails_help'] = 'Email the date/time and meeting link (or venue) to every participant whose booking is Confirmed. Use after adding or changing the meeting link.';
$string['sendjoiningdetails_confirm'] = 'Email the joining details to every Confirmed participant now?';

// v1.0.41: Multi-session webinars + calendar attachment.
$string['webinarhelp'] = 'Session 1 uses the Start and End Date & Time above. Enter the meeting URL, platform (e.g. Zoom, Teams, Webex), meeting ID and passcode.';
$string['additionalsessions'] = 'Additional Sessions (multi-day webinars)';
$string['additionalsessions_help'] = 'For workshops run as more than one webinar (e.g. three mornings), add Session 2 and Session 3 here. Leave the meeting URL blank to use the Session 1 link, meeting ID and passcode (e.g. a recurring Teams meeting). Untick a session\'s start date to remove it.';
$string['sessionn'] = 'Session {$a}';
$string['sessionstart'] = 'Start Date & Time';
$string['sessionend'] = 'End Date & Time';
$string['sessionneedsstart'] = 'Set a start date and time for this session, or clear its other details.';
$string['session1needsstart'] = 'Set the Start Date & Time (Session 1) when adding further sessions.';
$string['sessionorder'] = 'This session must start after the previous session.';
$string['endbeforestart'] = 'The end must be after the start.';
$string['confirmedemailics'] = 'Attach a calendar file';
$string['confirmedemailics_desc'] = 'Attach an .ics calendar file with one entry per session (date, time, meeting link, meeting ID, passcode and a 15-minute reminder). Participants open it to add the session(s) to Outlook, Google or Apple Calendar. Re-sending with "Email joining details" updates the existing entries in most calendars.';

// v1.0.42: Booked (welcome) email.
$string['bookedemail_heading'] = 'Booking welcome email';
$string['bookedemail_heading_desc'] = 'When a booking changes to Booked (Add/Edit participant, CSV import, or Enrol from Group with course enrolment), the participant is emailed the workshop date, course link and their username. Not sent when a Confirmed or Attended booking is moved back to Booked. Needs a linked course and a Moodle account. <strong>Turn off Moodle\'s own welcome message in the linked course</strong> (Participants > Enrolment methods > Manual enrolments > Edit > Send course welcome message: No), otherwise participants receive two welcome emails. New accounts still receive Moodle\'s separate login details email.';
$string['bookedemail'] = 'Email participants when Booked';
$string['bookedemail_desc'] = 'Send the welcome email below when a booking is changed to Booked.';
$string['bookedemailsubject'] = 'Email subject';
$string['bookedemailbody'] = 'Email body';
$string['bookedemail_placeholders'] = 'Placeholders: {firstname}, {lastname}, {fullname}, {username}, {email}, {workshopname}, {workshopdate} (date, or each date for multi-session workshops), {workshopwhen} (date, time and time zone), {coursename}, {courseurl}, {loginurl}, {forgotpasswordurl}, {sitename}. Plain text; links become clickable.';
$string['bookedemailsubject_default'] = 'Welcome to your {coursename} course';
$string['bookedemailbody_default'] = 'Hi {firstname},

We\'re looking forward to welcoming you to the {coursename} course, scheduled for {workshopdate}.

Your course resources are available through {sitename}. These include your Learner Guide, Textbook and supporting documentation.

Access your course resources here:
{courseurl}

Your username is: {username}

If this is your first time logging in, your login details are emailed to you separately. If you have forgotten your password, you can reset it here:
{forgotpasswordurl}

Kind regards,
{sitename}';
