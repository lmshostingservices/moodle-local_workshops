<?php
// This file is part of Moodle - http://moodle.org/
require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/workshops/lib.php');

$id     = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

// CRITICAL: anchor this page in the admin tree. Without admin_externalpage_setup() a page
// using the 'admin' layout is unlocated in the tree and Moodle collapses BOTH the primary
// and secondary navigation. This also runs require_login() and enforces the node capability.
//
// ROLE-ACCESS (v1.0.5): pick the node by mode. EDIT mode (id present) anchors to the
// 'Manage' node (cap local/workshops:view) so editingteacher / coursecreator — who hold
// :view + :edit but NOT :create — can reach it; the explicit require_capability(':edit')
// below still enforces edit rights. CREATE mode anchors to the 'Create' node
// (cap local/workshops:create, manager-only).
admin_externalpage_setup($id ? 'local_workshops_manage' : 'local_workshops_create');
$context = context_system::instance();

if ($id) {
    require_capability('local/workshops:edit', $context);
    $workshop = $DB->get_record('local_workshops', ['id' => $id], '*', MUST_EXIST);
} else {
    require_capability('local/workshops:create', $context);
    $workshop = null;
}

// Handle delete.
if ($delete && $id && confirm_sesskey()) {
    require_capability('local/workshops:delete', $context);
    $DB->delete_records('local_workshop_tasks',        ['workshopid' => $id]);
    $DB->delete_records('local_workshop_participants', ['workshopid' => $id]);
    // Delete stored files for documents.
    $docs = $DB->get_records('local_workshop_documents', ['workshopid' => $id]);
    $fs = get_file_storage();
    foreach ($docs as $doc) {
        $files = $fs->get_area_files($context->id, 'local_workshops', 'documents', $id);
        foreach ($files as $f) {
            $f->delete();
        }
    }
    $DB->delete_records('local_workshop_documents', ['workshopid' => $id]);
    $DB->delete_records('local_workshops', ['id' => $id]);
    redirect(new moodle_url('/local/workshops/manage.php'),
        get_string('workshopdeleted', 'local_workshops'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// =====================
// Form definition
// =====================
class local_workshops_edit_form extends moodleform {
    protected function definition() {
        global $DB;
        $mform = $this->_form;
        $workshop = $this->_customdata['workshop'];

        $mform->addElement('header', 'workshopinfo', get_string('workshopinformation', 'local_workshops'));

        // Workshop Name
        $mform->addElement('text', 'workshopname', get_string('workshopname', 'local_workshops'), ['size' => 60]);
        $mform->setType('workshopname', PARAM_TEXT);
        $mform->addRule('workshopname', null, 'required', null, 'client');

        // Program
        $programs = local_workshops_get_programs();
        $programopts = ['' => get_string('allprograms', 'local_workshops')];
        foreach ($programs as $p) {
            $programopts[$p] = $p;
        }
        $mform->addElement('select', 'program', get_string('program', 'local_workshops'), $programopts);
        $mform->setType('program', PARAM_TEXT);

        // Linked Moodle Course
        $courses = $DB->get_records_menu('course', null, 'fullname ASC', 'id, fullname');
        $courseopts = [0 => get_string('nocourse', 'local_workshops')] + $courses;
        $mform->addElement('select', 'courseid', get_string('linkedcourse', 'local_workshops'), $courseopts);
        $mform->setType('courseid', PARAM_INT);

        // Trainers (multi-select)
        $trainers = $DB->get_records_sql(
            "SELECT id, firstname, lastname FROM {user} WHERE deleted=0 AND suspended=0 ORDER BY lastname, firstname",
            [], 0, 500
        );
        $traineropts = [];
        foreach ($trainers as $t) {
            $traineropts[$t->id] = fullname($t);
        }
        $mform->addElement('autocomplete', 'trainerids', get_string('trainers', 'local_workshops'), $traineropts,
            ['multiple' => true, 'noselectionstring' => get_string('alltrainers', 'local_workshops')]);
        $mform->setType('trainerids', PARAM_INT);

        // Delivery Type
        $deliveries = local_workshops_get_delivery_types();
        $deliveryopts = array_combine($deliveries, $deliveries);
        $mform->addElement('select', 'deliverytype', get_string('deliverytype', 'local_workshops'), $deliveryopts);
        $mform->setType('deliverytype', PARAM_TEXT);
        $mform->setDefault('deliverytype', 'Face-to-Face');

        // Status
        $statuses = local_workshops_get_statuses();
        $statusopts = array_combine($statuses, $statuses);
        $mform->addElement('select', 'status', get_string('status', 'local_workshops'), $statusopts);
        $mform->setType('status', PARAM_TEXT);
        $mform->setDefault('status', 'Draft');

        // Dates
        $mform->addElement('date_time_selector', 'startdatetime', get_string('startdatetime', 'local_workshops'), ['optional' => true]);
        $mform->addElement('date_time_selector', 'enddatetime',   get_string('enddatetime',   'local_workshops'), ['optional' => true]);

        // Webinar Connection Details section — hidden for Face-to-Face
        $mform->addElement('header', 'webinarinfo', 'Webinar Connection Details');
        $mform->addElement('text', 'teamslink', get_string('teamslink', 'local_workshops'), ['size' => 80]);
        $mform->setType('teamslink', PARAM_URL);
        $mform->addElement('text', 'webinarplatform', get_string('webinarplatform', 'local_workshops'), ['size' => 60]);
        $mform->setType('webinarplatform', PARAM_TEXT);
        $mform->addElement('text', 'webinarmeetingid', get_string('webinarmeetingid', 'local_workshops'), ['size' => 40]);
        $mform->setType('webinarmeetingid', PARAM_TEXT);
        $mform->addElement('text', 'webinarpasscode', get_string('webinarpasscode', 'local_workshops'), ['size' => 40]);
        $mform->setType('webinarpasscode', PARAM_TEXT);
        $mform->addElement('static', 'webinarhelp', '', 'Enter the meeting URL, platform (e.g. Zoom, Teams, Webex), meeting ID and passcode.');
        // Hide the entire Webinar section when Face-to-Face is selected
        $mform->hideIf('webinarinfo',      'deliverytype', 'eq', 'Face-to-Face');
        $mform->hideIf('teamslink',        'deliverytype', 'eq', 'Face-to-Face');
        $mform->hideIf('webinarplatform',  'deliverytype', 'eq', 'Face-to-Face');
        $mform->hideIf('webinarmeetingid', 'deliverytype', 'eq', 'Face-to-Face');
        $mform->hideIf('webinarpasscode',  'deliverytype', 'eq', 'Face-to-Face');
        $mform->hideIf('webinarhelp',      'deliverytype', 'eq', 'Face-to-Face');

        // Location & Logistics section — hidden for Webinar
        $mform->addElement('header', 'locationinfo', 'Location & Logistics');
        $mform->addElement('text', 'venue',   get_string('venue', 'local_workshops'),   ['size' => 60]);
        $mform->setType('venue', PARAM_TEXT);
        $mform->addElement('text', 'room',    get_string('room', 'local_workshops'),    ['size' => 40]);
        $mform->setType('room', PARAM_TEXT);
        $mform->addElement('textarea', 'address', get_string('address', 'local_workshops'), ['rows' => 3, 'cols' => 60]);
        $mform->setType('address', PARAM_TEXT);
        // Hide the entire Location section for Webinar-only delivery
        $mform->hideIf('locationinfo', 'deliverytype', 'eq', 'Webinar');
        $mform->hideIf('venue',        'deliverytype', 'eq', 'Webinar');
        $mform->hideIf('room',         'deliverytype', 'eq', 'Webinar');
        $mform->hideIf('address',      'deliverytype', 'eq', 'Webinar');

        // Catering
        $mform->addElement('checkbox', 'cateringrequired', get_string('cateringrequired', 'local_workshops'));
        $mform->addElement('textarea', 'cateringnotes', get_string('cateringnotes', 'local_workshops'), ['rows' => 3, 'cols' => 60]);
        $mform->setType('cateringnotes', PARAM_TEXT);
        $mform->disabledIf('cateringnotes', 'cateringrequired');

        // Customer
        $mform->addElement('header', 'customerinfo', 'Customer Information');
        $mform->addElement('text', 'customername',    get_string('customername', 'local_workshops'),    ['size' => 60]);
        $mform->setType('customername', PARAM_TEXT);
        $mform->addElement('text', 'customercontact', get_string('customercontact', 'local_workshops'), ['size' => 60]);
        $mform->setType('customercontact', PARAM_TEXT);

        // Capacity
        $mform->addElement('text', 'capacity', get_string('capacity', 'local_workshops'), ['size' => 8]);
        $mform->setType('capacity', PARAM_INT);

        // Notes
        $mform->addElement('header', 'otherinfo', 'Other Information');
        $mform->addElement('textarea', 'specialrequirements', get_string('specialrequirements', 'local_workshops'), ['rows' => 4, 'cols' => 60]);
        $mform->setType('specialrequirements', PARAM_TEXT);
        $mform->addElement('textarea', 'notes', get_string('notes', 'local_workshops'), ['rows' => 4, 'cols' => 60]);
        $mform->setType('notes', PARAM_TEXT);

        // Hidden id
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('save', 'local_workshops'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['workshopname'])) {
            $errors['workshopname'] = get_string('required');
        }
        return $errors;
    }
}

$mform = new local_workshops_edit_form(null, ['workshop' => $workshop]);

// Set existing data.
if ($workshop) {
    $formdata = (array)$workshop;
    // Load trainer IDs from junction table.
    $formdata['trainerids'] = $DB->get_fieldset_select(
        'local_workshop_trainers', 'userid', 'workshopid = :wid', ['wid' => $workshop->id]
    );
    $mform->set_data($formdata);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/workshops/manage.php'));
} else if ($data = $mform->get_data()) {
    $now = time();
    $isNew = empty($data->id);

    $record = new stdClass();
    $record->workshopname       = $data->workshopname;
    $record->program            = $data->program;
    $record->courseid           = $data->courseid > 0 ? $data->courseid : null;
    $record->status             = $data->status;
    $record->deliverytype       = $data->deliverytype;
    $record->startdatetime      = !empty($data->startdatetime) ? $data->startdatetime : null;
    $record->enddatetime        = !empty($data->enddatetime) ? $data->enddatetime : null;
    $record->venue              = $data->venue;
    $record->room               = $data->room;
    $record->address            = $data->address;
    $record->teamslink          = $data->teamslink;
    $record->webinarplatform    = $data->webinarplatform;
    $record->webinarmeetingid   = $data->webinarmeetingid;
    $record->webinarpasscode    = $data->webinarpasscode;
    $record->cateringrequired   = !empty($data->cateringrequired) ? 1 : 0;
    $record->cateringnotes      = $data->cateringnotes;
    $record->customername       = $data->customername;
    $record->customercontact    = $data->customercontact;
    $record->specialrequirements = $data->specialrequirements;
    $record->notes              = $data->notes;
    $record->capacity           = !empty($data->capacity) ? (int)$data->capacity : null;
    $record->timemodified       = $now;

    if ($isNew) {
        $record->workshopcode  = local_workshops_generate_code();
        $record->timecreated   = $now;
        $record->id = $DB->insert_record('local_workshops', $record);
        local_workshops_create_default_tasks($record->id);
    } else {
        $record->id = $data->id;
        $DB->update_record('local_workshops', $record);
    }

    // Save trainers to junction table.
    $DB->delete_records('local_workshop_trainers', ['workshopid' => $record->id]);
    if (!empty($data->trainerids)) {
        foreach ((array)$data->trainerids as $tid) {
            $tid = (int)$tid;
            if ($tid > 0) {
                $DB->insert_record('local_workshop_trainers', ['workshopid' => $record->id, 'userid' => $tid]);
            }
        }
    }

    redirect(
        new moodle_url('/local/workshops/view.php', ['id' => $record->id]),
        get_string('workshopsaved', 'local_workshops'), null, \core\output\notification::NOTIFY_SUCCESS
    );
}

// admin_externalpage_setup() anchored this page in the admin tree (the 'Manage' node in
// edit mode, the 'Create' node in create mode) and set the 'admin' layout. Override
// URL/title/heading so an EDIT (id present) reads correctly.
$PAGE->set_url('/local/workshops/edit.php', $id ? ['id' => $id] : []);
$PAGE->set_title($id ? get_string('editworkshop', 'local_workshops') : get_string('createworkshop', 'local_workshops'));
$PAGE->set_heading($id ? get_string('editworkshop', 'local_workshops') : get_string('createworkshop', 'local_workshops'));
echo $OUTPUT->header();
echo '<div class="lw-container">';
echo '<div class="lw-form-page">';
$mform->display();
// INLINE SCRIPT — must appear AFTER $mform->display() so the form DOM exists.
// Moodle's hideIf() does not reliably fire on admin-layout pages (the AMD form
// module that processes data-hide-if attributes may not be bootstrapped). We drive
// the conditional field visibility directly with a plain JS immediately-invoked
// function that (a) applies on page load and (b) re-applies on every change of the
// deliverytype select.
//
// Fieldset IDs:  id_webinarinfo   (Webinar Connection Details section)
//                id_locationinfo  (Location & Logistics section)
// Select name:   deliverytype     (Face-to-Face | Webinar | Hybrid)
//
// Rules:
//   Face-to-Face → show location section, hide webinar section
//   Webinar      → hide location section, show webinar section
//   Hybrid       → show both sections
echo '<script>
(function () {
    "use strict";

    // Target the delivery-type select by name (robust across Moodle themes).
    var sel = document.querySelector(\'select[name="deliverytype"]\');
    if (!sel) { return; }

    // ---- Section fieldsets ----
    // Primary: Moodle standard id="id_<headerName>".
    // Fallback: find a fieldset whose legend text contains the section title.
    function getSectionFieldset(id, legendText) {
        var el = document.getElementById(id);
        if (el) { return el; }
        var sets = document.querySelectorAll(\'fieldset\');
        for (var i = 0; i < sets.length; i++) {
            var lg = sets[i].querySelector(\'legend\');
            if (lg && lg.textContent.indexOf(legendText) !== -1) { return sets[i]; }
        }
        return null;
    }

    var webinarSection  = getSectionFieldset(\'id_webinarinfo\',  \'Webinar\');
    var locationSection = getSectionFieldset(\'id_locationinfo\', \'Location\');

    // ---- Individual field wrappers ----
    // Moodle Boost renders each field inside  div#fitem_id_<name>.
    // Other themes / Moodle versions use different IDs, so we fall back to
    // locating the input by name and walking up to the nearest .fitem / .form-group.
    function getFieldWrapper(name) {
        var el = document.getElementById(\'fitem_id_\' + name);
        if (el) { return el; }
        var input = document.querySelector(\'[name="\' + name + \'"]\');
        if (input && input.closest) { return input.closest(\'.fitem, .form-group\'); }
        return null;
    }

    var webinarFields  = [\'teamslink\', \'webinarplatform\', \'webinarmeetingid\', \'webinarpasscode\', \'webinarhelp\'];
    var locationFields = [\'venue\', \'room\', \'address\'];

    function setVisibility(sections, fieldNames, show) {
        sections.forEach(function (el) {
            if (el) { el.style.display = show ? \'\' : \'none\'; }
        });
        fieldNames.forEach(function (name) {
            var wrapper = getFieldWrapper(name);
            if (wrapper) { wrapper.style.display = show ? \'\' : \'none\'; }
        });
    }

    function applyRules() {
        var val       = sel.value;
        var isFtF     = (val === \'Face-to-Face\');
        var isWebinar = (val === \'Webinar\');

        // Webinar section: visible for Webinar + Hybrid, hidden for Face-to-Face.
        setVisibility([webinarSection],  webinarFields,  !isFtF);

        // Location section: visible for Face-to-Face + Hybrid, hidden for Webinar.
        setVisibility([locationSection], locationFields, !isWebinar);
    }

    sel.addEventListener(\'change\', applyRules);
    applyRules();   // apply immediately on page load
}());
</script>';
echo '</div>';
echo '</div>';
echo $OUTPUT->footer();
