# Workshop Scheduler (local_workshops)

Plan and run training workshops in Moodle, and give attendees access to the linked Moodle course for their survey, assessment and certificate.

## What it does

- **Workshops**: name, program, linked Moodle course, trainers, dates, delivery type (Face-to-Face, Webinar, Hybrid), venue or webinar details, customer, catering, capacity and notes. Each workshop gets a code such as `WS-20261022-001`.
- **Participants**: add one at a time, import a CSV (`firstname, lastname, email, organisation`), or add everyone from a Moodle group. Booking statuses: Booked, Confirmed, Waitlisted, Cancelled, Attended, No Show.
- **Course access**: Booked, Confirmed and Attended participants are enrolled as Students in the linked course and added to a group for that workshop. If they have no Moodle account, one is created and the login details are emailed.
- **Attendance**: marking a participant Attended adds them to the course's "Attended workshop" group. Restrict the survey to this group so it opens after the workshop.
- **Checklist, documents and Moodle info**: an operational checklist per workshop, workshop documents, and course enrolment, completion and certificate counts.

## Learner journey

1. Add the participant to the workshop. They get a login, are enrolled in the Program course and placed in the workshop group.
2. After the session, mark them Attended. The survey unlocks.
3. They complete the survey, then the assessment.
4. The course completes and the certificate is issued (Custom certificate activity restricted to course completion).

## Set up each Program course once

1. Enable completion tracking in the course.
2. In the survey, add the access restriction **Group: Attended workshop**. The group is created the first time a participant in that course is marked Attended or synced.
3. Restrict the assessment to completion of the survey.
4. Set course completion to the assessment, and restrict the certificate to course completion.

## Settings

Site administration > Plugins > Local plugins > Workshop Scheduler

- **Programs**: one per line, shown in the Program dropdown.
- **Enrol bookings into the linked course**: on by default.
- **Create Moodle accounts for new participants**: on by default. Needs the `moodle/user:create` capability.
- **Attended group name**: default "Attended workshop".

## Existing bookings

After upgrading, open each workshop's Participants tab and select **Sync to Moodle course**. It can be run again at any time.

## Requirements

- Moodle 4.0 to 5.x.
- Manual enrolments enabled.

## Licence

GNU GPL v3 or later. See LICENSE.
