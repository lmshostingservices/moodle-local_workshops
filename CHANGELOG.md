# Changelog

All notable changes to local_workshops (Workshop Scheduler) are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

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
