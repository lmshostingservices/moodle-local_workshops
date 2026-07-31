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
 * Event observer class for local_workshops.
 *
 * @package   local_workshops
 * @copyright 2026 AI Grader
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_workshops;

defined('MOODLE_INTERNAL') || die();

/**
 * Listens for Moodle core events and syncs Workshop Scheduler booking statuses.
 */
class observers {

    /**
     * Fired when a user completes a Moodle course.
     *
     * Finds every active workshop linked to that course where the user is a
     * participant, then moves their booking status to "Attended" — unless it
     * is already "Cancelled" or "No Show" (those are deliberate final states
     * and should not be overwritten by completion).
     *
     * @param \core\event\course_completed $event
     */
    public static function course_completed(\core\event\course_completed $event): void {
        global $DB;

        $courseid = (int) $event->courseid;
        $userid   = (int) $event->userid;

        if (!$courseid || !$userid) {
            return;
        }

        // Statuses we must never overwrite — they are deliberate admin decisions.
        $locked = ['Cancelled', 'No Show'];

        // Find all workshops linked to the completed course.
        $workshops = $DB->get_records('local_workshops', ['courseid' => $courseid], '', 'id');

        foreach ($workshops as $workshop) {
            // Match participants by Moodle userid (linked accounts only).
            $participants = $DB->get_records(
                'local_workshop_participants',
                ['workshopid' => $workshop->id, 'userid' => $userid]
            );

            foreach ($participants as $p) {
                if (in_array($p->bookingstatus, $locked, true)) {
                    continue;
                }
                $p->bookingstatus = 'Attended';
                $DB->update_record('local_workshop_participants', $p);
            }
        }
    }
}
