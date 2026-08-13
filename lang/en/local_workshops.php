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

$string['privacy:metadata'] = 'The local_workshops plugin does not store any personal data.';
