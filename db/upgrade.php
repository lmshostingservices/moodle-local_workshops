<?php
// This file is part of Moodle - http://moodle.org/
defined('MOODLE_INTERNAL') || die();

function xmldb_local_workshops_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026061000001) {
        upgrade_plugin_savepoint(true, 2026061000001, 'local', 'workshops');
    }

    if ($oldversion < 2026061000002) {
        // Fix: admin category identifier changed from 'workshops' to 'local_workshops'
        // to prevent duplicate identifier conflict with mod_workshop.
        // Fix: fk_courseid changed from foreign-unique to foreign
        // to allow multiple workshops per course.
        upgrade_plugin_savepoint(true, 2026061000002, 'local', 'workshops');
    }

    if ($oldversion < 2026061000004) {
        // Fix (v1.0.3): admin_category identifier renamed from the bare component
        // name 'local_workshops' to 'local_workshops_category', and moved from the
        // 'root' node to the standard 'localplugins' node. The bare-component-name
        // identifier collided with the section name Moodle reserves for the plugin's
        // auto-created settings page, corrupting the admin tree and collapsing the
        // primary and secondary navigation on every admin page. No DB schema changes.
        upgrade_plugin_savepoint(true, 2026061000004, 'local', 'workshops');
    }

    if ($oldversion < 2026061000005) {
        // Fix (v1.0.4): the REAL cause of the navigation collapse. The plugin's admin
        // pages (manage.php, edit.php, view.php, import.php) rendered with the 'admin'
        // page layout but set it manually via $PAGE->set_pagelayout('admin') WITHOUT
        // calling admin_externalpage_setup(). A page using the admin layout that is not
        // located in the admin tree makes Moodle collapse BOTH the primary and secondary
        // navigation on that page. All four pages now call admin_externalpage_setup()
        // to anchor themselves in the admin tree (matching the working local_rtocompliance
        // pattern). No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061000005, 'local', 'workshops');
    }

    if ($oldversion < 2026061000006) {
        // Fix (v1.0.5): restore non-admin role access that the v1.0.4 admin_externalpage_setup()
        // change inadvertently removed. (1) settings.php registered the admin menu nodes only
        // when $hassiteconfig, so managers/coursecreators/editingteachers (who hold
        // local/workshops:view per db/access.php) hit a "section not found" error on every
        // Workshops page — the node did not exist in the admin tree built for them. Registration
        // is now gated on local/workshops:view (settings PAGE stays admin-only). (2) edit.php in
        // EDIT mode anchored to the create-only node, locking out :edit-capable non-managers; it
        // now anchors edit mode to the Manage node and keeps create mode on the Create node.
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061000006, 'local', 'workshops');
    }

    if ($oldversion < 2026061100009) {
        // Fix (v1.0.8): Three hardening changes to eliminate the persistent nav collapse.
        // (1) settings.php — removed the custom admin_category entirely; external pages are
        //     now placed directly under 'localplugins' (flat structure), matching every other
        //     working local plugin in this codebase. Added explicit $settings = null to prevent
        //     Moodle's post-include auto-add from inserting a duplicate node.
        // (2) lib.php — removed the two empty extend_navigation / extend_settings_navigation
        //     callback stubs. They were no-ops but caused Moodle to auto-include lib.php on
        //     every page to call them, adding unnecessary overhead and potential edge-case risk.
        // (3) AMD — removed unused core/ajax and core/notification dependencies (neither was
        //     ever called; only jquery was needed). This eliminates any JS loading chain failure
        //     from those modules.
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061100009, 'local', 'workshops');
    }

    if ($oldversion < 2026061100010) {
        // Fix (v1.0.9): Refactor settings.php to use standard Moodle $settings variable
        // (replacing the $wspage workaround and removing $settings = null). Added
        // error_log diagnostics to confirm admin tree registration order.
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061100010, 'local', 'workshops');
    }

    if ($oldversion < 2026061100011) {
        // Fix (v1.0.10): Replace error_log() diagnostics with $PAGE->requires->js_init_code()
        // so diagnostic messages appear in the browser JS console instead of the server log.
        // Falls back to error_log() in CLI/non-page contexts.
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061100011, 'local', 'workshops');
    }

    if ($oldversion < 2026061100012) {
        // Fix (v1.0.11): Reliable JS console diagnostics via $_SESSION + extend_navigation.
        // settings.php writes messages to $_SESSION['workshops_debug']; lib.php flushes
        // them as console.log() on the next fully-rendered page via extend_navigation().
        // This bypasses the admin-tree cache and page-redirect problems that made
        // $PAGE->requires->js_init_code() in settings.php unreliable.
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061100012, 'local', 'workshops');
    }

    if ($oldversion < 2026061200012) {
        // Fix (v1.0.12): Removed local_workshops_extend_navigation() from lib.php and
        // removed $_SESSION diagnostic code from settings.php.
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061200012, 'local', 'workshops');
    }

    if ($oldversion < 2026061200013) {
        // Fix (v1.0.13): Two corrections.
        // (1) settings.php: added $settings = null immediately after the manual
        //     $ADMIN->add('localplugins', $settings) call. Without this, Moodle's
        //     post-include auto-add routine tries to insert the same node a second
        //     time, throwing a coding_exception that corrupts the admin tree and
        //     triggers the broken first.js navigation module, collapsing both navbars.
        //     This was present in v1.0.8 and accidentally removed in v1.0.9.
        // (2) manage.php / view.php / edit.php / import.php: removed redundant
        //     $PAGE->requires->css('/local/workshops/styles.css') calls. Moodle
        //     automatically loads styles.css from the plugin root; the manual call
        //     injected a duplicate stylesheet on every workshop page.
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061200013, 'local', 'workshops');
    }

    if ($oldversion < 2026061200014) {
        // Maintenance (v1.0.14): Full file-by-file audit of all 18 plugin files
        // following persistent nav-collapse investigation. Audit confirmed no
        // additional code defects: CSS is 100% .lw-* scoped, AMD is correct
        // define() format, lib.php has no navigation hooks and is not auto-loaded,
        // all admin pages call admin_externalpage_setup() correctly, no output
        // before $OUTPUT->header(), no duplicate admin node IDs. BUILD_INFO.json
        // created (was absent). Seven stale ZIPs removed from public/downloads/.
        // No code changes to plugin PHP/JS/CSS; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061200014, 'local', 'workshops');
    }

    if ($oldversion < 2026061200015) {
        // v1.0.15: Added PHP-to-browser-console debug bridge (set_error_handler +
        // register_shutdown_function + local_workshops_before_footer hook).
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061200015, 'local', 'workshops');
    }

    if ($oldversion < 2026061200017) {
        // v1.0.17: Added capacity (INT) column to local_workshops table.
        $table = new xmldb_table('local_workshops');
        $field = new xmldb_field('capacity', XMLDB_TYPE_INTEGER, '5', null, null, null, null, 'notes');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026061200017, 'local', 'workshops');
    }

    if ($oldversion < 2026061200019) {
        // v1.0.19: Added webinarplatform, webinarmeetingid, webinarpasscode columns.
        $table = new xmldb_table('local_workshops');
        $field1 = new xmldb_field('webinarplatform',  XMLDB_TYPE_CHAR, '100', null, null, null, null, 'teamslink');
        $field2 = new xmldb_field('webinarmeetingid', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'webinarplatform');
        $field3 = new xmldb_field('webinarpasscode',  XMLDB_TYPE_CHAR, '100', null, null, null, null, 'webinarmeetingid');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }
        upgrade_plugin_savepoint(true, 2026061200019, 'local', 'workshops');
    }

    if ($oldversion < 2026061200016) {
        // ROOT CAUSE FIX (v1.0.16):
        //
        // Problem: Moodle's primary and secondary navigation menus disappeared
        // on ALL admin pages whenever this plugin was installed.
        //
        // Root cause (confirmed via browser console diagnostic):
        //   require(['jquery'], cb) returned the Workshops module object
        //   {init, initView} instead of the real jQuery function. This caused
        //   core/first.js to crash with:
        //     "Uncaught TypeError: (0, _jquery.default) is not a function"
        //   which aborted Moodle's entire AMD bootstrap and prevented the
        //   MoreMenu navigation from becoming visible.
        //
        //   The cause: amd/build/workshops.js and workshops.min.js used an
        //   ANONYMOUS define() call — define(['jquery'], function($) {...}).
        //   When Moodle's combo-loader concatenates multiple AMD modules into
        //   a single HTTP response, anonymous define() calls have no explicit
        //   module name. RequireJS must infer the name from the script URL,
        //   but in a combo response there is only ONE URL for N modules.
        //   If the loader processes our anonymous define() while it expects
        //   the 'jquery' definition, it registers our workshops object as the
        //   'jquery' module — corrupting the entire AMD registry site-wide.
        //   A stale localcache entry locked in this bad assignment across
        //   cache purges.
        //
        // Fix applied:
        //   Changed anonymous define() to named define():
        //     define('local_workshops/workshops', ['jquery'], function($) {
        //   This is the standard Moodle amd/build convention. With an explicit
        //   name, RequireJS always registers the module correctly regardless of
        //   how many modules are bundled in the same combo response.
        //
        // Also removed in this version:
        //   The v1.0.15 lib.php debug bridge (set_error_handler +
        //   register_shutdown_function + local_workshops_before_footer).
        //   These were replacing Moodle's global PHP error handler on every
        //   page and injecting <script> tags site-wide — against Moodle local
        //   plugin standards. Removed per Moodle coding best practices.
        //
        // No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061200016, 'local', 'workshops');
    }

    if ($oldversion < 2026061200020) {
        // v1.0.20 - MAINTENANCE: Documentation testing guide clarified — Step 10 check
        //   'No venue address fields appear' reworded to 'Venue, room, and address fields are
        //   hidden (they only appear when Delivery type is Face-to-Face or Hybrid)' to avoid
        //   testers misreading the conditional hideIf behaviour as a missing-field bug.
        //   BUILD_INFO.json updated (was stale at v1.0.15). Stale ZIPs for v1.0.16 and v1.0.17
        //   removed from public/downloads/. No code or DB schema changes to the plugin.
        //   No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061200020, 'local', 'workshops');
    }

    if ($oldversion < 2026061200021) {
        // v1.0.21 - FIX-DELIVERY-TYPE-FIELD-VISIBILITY — Moodle's hideIf() does not reliably
        //   fire on admin-layout pages (the AMD form module that processes data-hide-if
        //   attributes is not bootstrapped on admin external pages). Replaced the PHP-only
        //   approach with an inline <script> injected immediately after $mform->display() so
        //   the DOM exists when the script runs.
        //   Rules enforced:
        //     Face-to-Face → Location & Logistics section visible, Webinar section hidden.
        //     Webinar       → Webinar section visible, Location section hidden.
        //     Hybrid        → both sections visible.
        //   Targets both the fieldset element (id_webinarinfo / id_locationinfo) and each
        //   individual fitem_id_<field> wrapper div for belt-and-suspenders coverage across
        //   all Moodle themes. No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061200021, 'local', 'workshops');
    }

    if ($oldversion < 2026061200022) {
        // v1.0.22 — VIEW-PAGE-CONDITIONAL-FIELDS + EDIT-JS-HARDENING
        //   (1) view.php: the Info tab previously rendered all fields unconditionally.
        //       Now uses PHP-level conditionals so only the relevant fields appear:
        //         Face-to-Face → Venue / Room / Address shown; webinar fields hidden.
        //         Webinar       → Teams Link / Platform / Meeting ID / Passcode shown;
        //                          location fields hidden.
        //         Hybrid        → all fields shown.
        //   (2) edit.php inline JS: added legend-text fallback for themes where
        //       id="id_<headerName>" may not be present, and .closest() fallback for
        //       field wrappers that don't use fitem_id_<name> IDs.
        //   No DB schema changes; savepoint is a marker only.
        upgrade_plugin_savepoint(true, 2026061200022, 'local', 'workshops');
    }

    if ($oldversion < 2026061900024) {
        // v1.0.24: Multiple trainers — add local_workshop_trainers junction table
        // and migrate existing single trainerid values into it.
        $table = new xmldb_table('local_workshop_trainers');
        $table->add_field('id',         XMLDB_TYPE_INTEGER, '10',  true,  true,  true);
        $table->add_field('workshopid', XMLDB_TYPE_INTEGER, '10',  true,  false, false);
        $table->add_field('userid',     XMLDB_TYPE_INTEGER, '10',  true,  false, false);
        $table->add_key('primary',          XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fk_workshopid',    XMLDB_KEY_FOREIGN, ['workshopid'], 'local_workshops', ['id']);
        $table->add_key('fk_userid',        XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('uq_workshop_user', XMLDB_KEY_UNIQUE,  ['workshopid', 'userid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Migrate existing single-trainer assignments into the junction table.
        $existing = $DB->get_records_select('local_workshops', 'trainerid IS NOT NULL', [], '', 'id, trainerid');
        foreach ($existing as $w) {
            if (!$DB->record_exists('local_workshop_trainers', ['workshopid' => $w->id, 'userid' => $w->trainerid])) {
                $DB->insert_record('local_workshop_trainers', ['workshopid' => $w->id, 'userid' => $w->trainerid]);
            }
        }

        upgrade_plugin_savepoint(true, 2026061900024, 'local', 'workshops');
    }

    if ($oldversion < 2026061900025) {
        // No DB changes — added Settings quick-link button to manage.php and view.php.
        upgrade_plugin_savepoint(true, 2026061900025, 'local', 'workshops');
    }

    if ($oldversion < 2026061900026) {
        // No DB changes — fixed Attendance Sessions card showing "not installed"
        // when the plugin IS installed but has 0 sessions for the course.
        upgrade_plugin_savepoint(true, 2026061900026, 'local', 'workshops');
    }

    if ($oldversion < 2026061900027) {
        // No DB changes — added "Enrol from Group" feature on Participants tab:
        // AJAX get_groups + enrol_group, group chooser modal, auto course enrolment.
        upgrade_plugin_savepoint(true, 2026061900027, 'local', 'workshops');
    }

    if ($oldversion < 2026062200028) {
        // FIX-WORKSHOPS-FK-INDEX-COLLISION (v1.0.28): Removed redundant idx_workshopid
        // index from local_workshop_trainers table creation (v1.0.24 block). Moodle's
        // XMLDB rejects table creation when a non-unique index and a foreign key target
        // the same column — the FK already implies an index. The add_index() call in the
        // v1.0.24 block caused a "Key fk_workshopid collides with indexidx_workshopid"
        // coding error, blocking upgrades to any version >= 1.0.24. No DB schema changes.
        upgrade_plugin_savepoint(true, 2026062200028, 'local', 'workshops');
    }

    if ($oldversion < 2026062200029) {
        // Domain update: lms-labs.com → lms-labs.com
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'lib.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) { opcache_invalidate($_full, true); }
            }
        } elseif (function_exists('opcache_reset')) { opcache_reset(); }
        upgrade_plugin_savepoint(true, 2026062200029, 'local', 'workshops');
    }

    if ($oldversion < 2026072400030) {
        // v1.0.30: Bidirectional course completion sync.
        // (1) Moodle → Workshop: db/events.php registers an observer on
        //     \core\event\course_completed. When a user completes the linked
        //     Moodle course their workshop booking status is automatically set
        //     to "Attended" (unless already Cancelled or No Show).
        // (2) Workshop → Moodle: ajax.php save_participant now calls
        //     local_workshops_mark_course_complete() when bookingstatus is set
        //     to "Attended", pushing a course_completed record to Moodle for
        //     the linked course. Best-effort — silently skipped if the course
        //     has no completion tracking enabled.
        // No DB schema changes — purely PHP/observer additions.
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'lib.php', 'ajax.php', 'db/upgrade.php', 'db/events.php', 'classes/observers.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) {
                    opcache_invalidate($_full, true);
                }
            }
        } elseif (function_exists('opcache_reset')) {
            opcache_reset();
        }
        upgrade_plugin_savepoint(true, 2026072400030, 'local', 'workshops');
    }

    if ($oldversion < 2026072400031) {
        // v1.0.31: Added get_workshops AJAX action to ajax.php.
        // This endpoint returns the full workshop list as JSON, used by the
        // availability_workshopattendance condition plugin to populate its
        // workshop dropdown inside the Moodle "Restrict Access" editor.
        // No DB schema changes.
        if (function_exists('opcache_invalidate')) {
            $_pluginDir = realpath(__DIR__ . '/..');
            foreach (['version.php', 'ajax.php', 'db/upgrade.php'] as $_f) {
                $_full = $_pluginDir . '/' . $_f;
                if (file_exists($_full)) {
                    opcache_invalidate($_full, true);
                }
            }
        } elseif (function_exists('opcache_reset')) {
            opcache_reset();
        }
        upgrade_plugin_savepoint(true, 2026072400031, 'local', 'workshops');
    }

    return true;
}