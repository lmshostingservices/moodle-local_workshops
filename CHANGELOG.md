# Changelog

All notable changes to local_workshops (Workshop Scheduler) are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [v1.0.40] - 2026-11-13

### Fixed
- **Webinar/Meeting URL not saving.** The field used Moodle's PARAM_URL cleaner, which silently blanks any value it rejects, so a Teams link pasted with a trailing space, non-breaking or zero-width space was saved as empty with no error. The field now strips those characters, accepts any http(s) link (including long Teams "meetup-join" links), and shows an error on the form if the value isn't a web address. Existing workshops whose link was lost need it re-entered once.
- Info tab label now reads "Webinar/Meeting URL" (was "Teams Link") to match the form.

### Added
- **Booking confirmation email.** When a booking changes to **Confirmed** (Add/Edit participant, or Enrol from Group with status Confirmed), the participant is emailed the workshop date/time and joining details: meeting link, platform, meeting ID and passcode for Webinar/Hybrid; venue, room and address for Face-to-Face/Hybrid.
  - Not sent when an Attended booking is reverted to Confirmed, or when the status was already Confirmed.
  - Webinar/Hybrid workshops without a meeting link: email held back and the on-screen message says so.
  - Works without a linked course or Moodle account (sent to the booking's email address).
  - New settings under "Booking confirmation email": on/off (default on), subject and body with placeholders including {workshopwhen}, {joiningdetails}, {meetingurl}, {platform}, {meetingid}, {passcode}. Defaults set on upgrade.
- **Email joining details** button on the Participants tab: (re)sends the confirmation email to every Confirmed participant, e.g. after the meeting link is added or changed.
- No database schema change.

## [v1.0.39] - 2026-11-09

### Fixed
- Release-desk compliance: GPL header and @package/@copyright/@license tags added to db/upgrade.php; attendance email body setting uses PARAM_TEXT; `function (` spacing in view.php; BUILD_INFO.json no longer shipped in the ZIP.

## [v1.0.38] - 2026-11-09

### Fixed
- Attendance email: course and site names containing "&" were sent as "&amp;" in the subject and text, and "&amp;amp;" in the HTML version. Names are now unescaped for text and escaped once for HTML.
- Attendance email is only sent when the learner's course enrolment is active (a suspended enrolment can't open the link); the on-screen message reports it as not enrolled.

## [v1.0.37] - 2026-11-09

### Changed
- Attendance email default body now reads "Here's a link to complete the survey and assessment in the course {coursename}:" followed by the course URL. On upgrade the saved body is replaced only if it still matches the v1.0.36 default.

## [v1.0.36] - 2026-11-09

### Added
- **Attendance email.** When a booking changes to **Attended** (bulk Attendance, Edit/Add participant, or Enrol from Group with status Attended), the learner is emailed a link to the linked Moodle course so they can complete the survey and assessment.
  - Sent from the site support user via Moodle's email_to_user, after the course sync, so new accounts are already created and enrolled.
  - Only sent on a change to Attended. Not re-sent by "Sync to Moodle course" or for people already Attended. Unticking and re-ticking someone sends it again.
  - Skipped (and reported in the on-screen message) when there's no linked course, no active Moodle account, or the learner isn't enrolled.
  - New settings under Attendance email: on/off (default on), subject and body with placeholders {firstname}, {lastname}, {fullname}, {username}, {workshopname}, {workshopdate}, {coursename}, {courseurl}, {loginurl}, {forgotpasswordurl}, {sitename}. Defaults are set on upgrade.
  - Bulk Attendance modal has an "Email newly attended participants the course link" tickbox (on by default) to skip emails for a batch.
- No database schema change.

## [v1.0.35] - 2026-11-09

### Added
- **Bulk attendance.** An "Attendance" button in the Actions column header of the Participants tab opens a modal listing every non-cancelled booking with a checkbox.
  - Select all, Unselect all, a header checkbox, click-anywhere-on-row toggling, a live "x of y selected" count, and a filter box (name, email, organisation). Select/unselect act on the rows currently shown by the filter.
  - Current Attended bookings open pre-ticked, so the modal works as an attendance register you can re-open and correct.
  - Save: ticked participants become **Attended** (and, with a linked course, are added to the "Attended workshop" group, unlocking the survey). Unticked participants are either left as they are or marked **No Show** (with a confirmation). Unticking someone currently Attended reverts them to Confirmed (or No Show) and withdraws their survey unlock.
  - Only changed bookings are updated and re-synced to the course. Cancelled bookings are never touched.
- New AJAX action `bulk_attendance` (capability `local/workshops:manageparticipants`). No database schema change.

## [v1.0.34] - 2026-10-27

### Changed
- Release packaging: ZIP root folder `workshops/`, CHANGELOG in Keep-a-Changelog format, expanded README. No code changes from v1.0.33.

## [v1.0.33] - 2026-10-23

### Added
- Bookings (manual add, CSV import, Enrol from Group) with status Booked, Confirmed or Attended now give access to the linked Moodle course:
  - finds the Moodle account by email (case-insensitive), or creates one (manual auth; password emailed by Moodle's scheduled task; forced change on first login);
  - enrols the learner as Student via the manual enrolment method (adds the method to the course if missing);
  - adds the learner to a per-workshop group (idnumber `lw_ws_<id>`). An existing group with the workshop's exact name is adopted rather than duplicated.
- Marking a booking **Attended** adds the learner to a course-level "Attended workshop" group (idnumber `lw_attended`), used to restrict access to the survey. Any other status removes them, unless they are Attended on another workshop linked to the same course.
- "Sync to Moodle course" button on the Participants tab (`sync.php`) to bring existing bookings up to date. Safe to run repeatedly.
- "Course Access" column on the Participants tab (Enrolled / Survey unlocked / Not enrolled / No account).
- Admin settings: enrol bookings into the linked course, create Moodle accounts for new participants, attended group name. Defaults are set on upgrade.

### Changed
- Removed forced course completion when a booking is marked Attended. It bypassed the course's completion criteria and could release certificates before the survey and assessment were done.
- Duplicate bookings for the same email on one workshop are rejected (case-insensitive).
- Email lookups no longer fail on sites with duplicate-email accounts (oldest active account is used).
- Removing a participant withdraws their "Attended" unlock but keeps their course enrolment and progress.
- Learners are never unenrolled automatically. No database schema change.

## [v1.0.32] - 2026-07-24

### Fixed
- XMLDB coding error from a duplicate `idx_workshopid` index on `local_workshop_trainers` that blocked upgrades from 1.0.24+.

## [v1.0.19] - 2026-06-12

### Added
- Platform Name, Meeting ID and Passcode webinar fields (shown for Webinar/Hybrid).

## [v1.0.18] - 2026-06-12

### Added
- "In Progress" workshop status, capacity field, and delivery-type-conditional fields.

### Changed
- Renamed "Create Workshop" to "Add Workshop".
- CSV import confirm step no longer requires re-uploading the file.

### Fixed
- AJAX action parameter cleaning (all participant and task AJAX was broken).
