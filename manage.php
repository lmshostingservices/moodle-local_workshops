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
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/workshops/lib.php');

// CRITICAL: this page is an admin_externalpage node ('local_workshops_manage', registered
// in settings.php) and renders with the 'admin' page layout. It MUST anchor itself in the
// admin tree via admin_externalpage_setup(). Calling set_pagelayout('admin') WITHOUT this
// leaves the page unlocated in the admin tree, which makes Moodle collapse BOTH the primary
// and secondary navigation. admin_externalpage_setup() also performs require_login() and
// enforces the node capability (local/workshops:view).
admin_externalpage_setup('local_workshops_manage');

$context = context_system::instance();

// Filters
$filterprogram  = optional_param('filterprogram', '', PARAM_TEXT);
$filtertrainer  = optional_param('filtertrainer', 0, PARAM_INT);
$filterstatus   = optional_param('filterstatus', '', PARAM_TEXT);
$filterdatefrom = optional_param('filterdatefrom', '', PARAM_TEXT);
$filterdateto   = optional_param('filterdateto', '', PARAM_TEXT);
$filterdelivery = optional_param('filterdelivery', '', PARAM_TEXT);
$filtercustomer = optional_param('filtercustomer', '', PARAM_TEXT);
$filtersearch   = optional_param('filtersearch', '', PARAM_TEXT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = 25;

// admin_externalpage_setup() already set the context, URL, title, heading and the 'admin'
// page layout from the registered node — only the page-specific assets remain.
$PAGE->requires->js_call_amd('local_workshops/workshops', 'init');

echo $OUTPUT->header();

// Can create?
$cancreate = has_capability('local/workshops:create', $context);

// Build query
$params = [];
$where = ['1=1'];

if ($filterprogram !== '') {
    $where[] = 'w.program = :program';
    $params['program'] = $filterprogram;
}
if ($filtertrainer > 0) {
    $where[] = 'EXISTS (SELECT 1 FROM {local_workshop_trainers} wt WHERE wt.workshopid = w.id AND wt.userid = :trainerid)';
    $params['trainerid'] = $filtertrainer;
}
if ($filterstatus !== '') {
    $where[] = 'w.status = :status';
    $params['status'] = $filterstatus;
}
if ($filterdatefrom !== '') {
    $ts = strtotime($filterdatefrom);
    if ($ts) {
        $where[] = 'w.startdatetime >= :datefrom';
        $params['datefrom'] = $ts;
    }
}
if ($filterdateto !== '') {
    $ts = strtotime($filterdateto . ' 23:59:59');
    if ($ts) {
        $where[] = 'w.startdatetime <= :dateto';
        $params['dateto'] = $ts;
    }
}
if ($filterdelivery !== '') {
    $where[] = 'w.deliverytype = :deliverytype';
    $params['deliverytype'] = $filterdelivery;
}
if ($filtercustomer !== '') {
    $where[] = $DB->sql_like('w.customername', ':customer', false);
    $params['customer'] = '%' . $DB->sql_like_escape($filtercustomer) . '%';
}
if ($filtersearch !== '') {
    $where[] = '(' . $DB->sql_like('w.workshopname', ':s1', false) . ' OR ' . $DB->sql_like('w.workshopcode', ':s2', false) . ' OR ' . $DB->sql_like('w.customername', ':s3', false) . ')';
    $esc = '%' . $DB->sql_like_escape($filtersearch) . '%';
    $params['s1'] = $esc;
    $params['s2'] = $esc;
    $params['s3'] = $esc;
}

$wheresql = implode(' AND ', $where);

$total = $DB->count_records_sql(
    "SELECT COUNT(*) FROM {local_workshops} w WHERE $wheresql", $params
);

$workshops = $DB->get_records_sql(
    "SELECT w.*, c.fullname AS coursefullname
     FROM {local_workshops} w
     LEFT JOIN {course} c ON c.id = w.courseid
     WHERE $wheresql
     ORDER BY w.startdatetime DESC, w.id DESC",
    $params, $page * $perpage, $perpage
);

// Get trainers for filter dropdown (all users assigned to any workshop).
$trainerrows = $DB->get_records_sql(
    "SELECT DISTINCT u.id, u.firstname, u.lastname
     FROM {local_workshop_trainers} wt
     JOIN {user} u ON u.id = wt.userid
     ORDER BY u.lastname, u.firstname"
);

// Get participant counts and trainer names per workshop in bulk queries.
$workshopids = array_keys($workshops);
$participantcounts = [];
$workshoptrainers  = [];
if (!empty($workshopids)) {
    list($insql, $inparams) = $DB->get_in_or_equal($workshopids, SQL_PARAMS_NAMED, 'wid');

    $rows = $DB->get_records_sql(
        "SELECT workshopid, COUNT(*) AS cnt FROM {local_workshop_participants} WHERE workshopid $insql GROUP BY workshopid",
        $inparams
    );
    foreach ($rows as $r) {
        $participantcounts[$r->workshopid] = $r->cnt;
    }

    list($tinsql, $tinparams) = $DB->get_in_or_equal($workshopids, SQL_PARAMS_NAMED, 'tw');
    $trainerrows_all = $DB->get_records_sql(
        "SELECT wt.workshopid, u.firstname, u.lastname
         FROM {local_workshop_trainers} wt
         JOIN {user} u ON u.id = wt.userid
         WHERE wt.workshopid $tinsql
         ORDER BY u.lastname, u.firstname",
        $tinparams
    );
    foreach ($trainerrows_all as $tr) {
        $workshoptrainers[$tr->workshopid][] = fullname($tr);
    }
}

$programs   = local_workshops_get_programs();
$statuses   = local_workshops_get_statuses();
$deliveries = local_workshops_get_delivery_types();

// Current filter URL params for pagination links.
$filterparams = [
    'filterprogram'  => $filterprogram,
    'filtertrainer'  => $filtertrainer,
    'filterstatus'   => $filterstatus,
    'filterdatefrom' => $filterdatefrom,
    'filterdateto'   => $filterdateto,
    'filterdelivery' => $filterdelivery,
    'filtercustomer' => $filtercustomer,
    'filtersearch'   => $filtersearch,
];

?>
<div class="lw-container">

<!-- Page header -->
<div class="lw-page-header">
    <h2><?php echo get_string('manageworkshops', 'local_workshops'); ?></h2>
    <div class="lw-page-header-actions">
        <?php if ($cancreate): ?>
        <a href="<?php echo (new moodle_url('/local/workshops/edit.php'))->out(); ?>" class="btn btn-primary">
            + <?php echo get_string('createworkshop', 'local_workshops'); ?>
        </a>
        <?php endif; ?>
        <?php if (is_siteadmin()): ?>
        <a href="<?php echo (new moodle_url('/admin/settings.php', ['section' => 'local_workshops_settings']))->out(); ?>"
           class="btn btn-outline-secondary lw-settings-btn" title="<?php echo get_string('settings', 'local_workshops'); ?>">
            &#9881; <?php echo get_string('settings', 'local_workshops'); ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="lw-filters card">
    <form method="get" action="<?php echo (new moodle_url('/local/workshops/manage.php'))->out(false); ?>" id="lw-filter-form">
    <div class="lw-filter-grid">

        <div class="lw-filter-field">
            <label for="filtersearch"><?php echo get_string('filtersearch', 'local_workshops'); ?></label>
            <input type="text" id="filtersearch" name="filtersearch" class="form-control"
                   placeholder="Name, code, customer..." value="<?php echo s($filtersearch); ?>">
        </div>

        <div class="lw-filter-field">
            <label for="filterprogram"><?php echo get_string('filterprogram', 'local_workshops'); ?></label>
            <select id="filterprogram" name="filterprogram" class="form-control">
                <option value=""><?php echo get_string('allprograms', 'local_workshops'); ?></option>
                <?php foreach ($programs as $prog): ?>
                <option value="<?php echo s($prog); ?>" <?php echo $filterprogram === $prog ? 'selected' : ''; ?>>
                    <?php echo s($prog); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lw-filter-field">
            <label for="filterstatus"><?php echo get_string('filterstatus', 'local_workshops'); ?></label>
            <select id="filterstatus" name="filterstatus" class="form-control">
                <option value=""><?php echo get_string('allstatuses', 'local_workshops'); ?></option>
                <?php foreach ($statuses as $st): ?>
                <option value="<?php echo s($st); ?>" <?php echo $filterstatus === $st ? 'selected' : ''; ?>>
                    <?php echo s($st); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lw-filter-field">
            <label for="filterdelivery"><?php echo get_string('filterdelivery', 'local_workshops'); ?></label>
            <select id="filterdelivery" name="filterdelivery" class="form-control">
                <option value=""><?php echo get_string('alldeliverytypes', 'local_workshops'); ?></option>
                <?php foreach ($deliveries as $dt): ?>
                <option value="<?php echo s($dt); ?>" <?php echo $filterdelivery === $dt ? 'selected' : ''; ?>>
                    <?php echo s($dt); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lw-filter-field">
            <label for="filtertrainer"><?php echo get_string('filtertrainer', 'local_workshops'); ?></label>
            <select id="filtertrainer" name="filtertrainer" class="form-control">
                <option value="0"><?php echo get_string('alltrainers', 'local_workshops'); ?></option>
                <?php foreach ($trainerrows as $tr): ?>
                <option value="<?php echo $tr->id; ?>" <?php echo $filtertrainer == $tr->id ? 'selected' : ''; ?>>
                    <?php echo s(fullname($tr)); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lw-filter-field">
            <label for="filterdatefrom"><?php echo get_string('filterdatefrom', 'local_workshops'); ?></label>
            <input type="date" id="filterdatefrom" name="filterdatefrom" class="form-control"
                   value="<?php echo s($filterdatefrom); ?>">
        </div>

        <div class="lw-filter-field">
            <label for="filterdateto"><?php echo get_string('filterdateto', 'local_workshops'); ?></label>
            <input type="date" id="filterdateto" name="filterdateto" class="form-control"
                   value="<?php echo s($filterdateto); ?>">
        </div>

        <div class="lw-filter-field">
            <label for="filtercustomer"><?php echo get_string('filtercustomer', 'local_workshops'); ?></label>
            <input type="text" id="filtercustomer" name="filtercustomer" class="form-control"
                   placeholder="Customer name..." value="<?php echo s($filtercustomer); ?>">
        </div>

    </div>
    <div class="lw-filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">
            <?php echo get_string('applyfilters', 'local_workshops'); ?>
        </button>
        <a href="<?php echo (new moodle_url('/local/workshops/manage.php'))->out(); ?>" class="btn btn-secondary btn-sm">
            <?php echo get_string('clearfilters', 'local_workshops'); ?>
        </a>
    </div>
    </form>
</div>

<!-- Results count -->
<p class="lw-results-count">
    <?php echo $total; ?> workshop<?php echo $total !== 1 ? 's' : ''; ?> found
</p>

<!-- Table -->
<?php if (empty($workshops)): ?>
<div class="lw-empty">
    <p><?php echo get_string('noworkshops', 'local_workshops',
        $cancreate ? '<a href="' . (new moodle_url('/local/workshops/edit.php'))->out() . '">' . get_string('createfirst', 'local_workshops') . '</a>' : ''); ?></p>
</div>
<?php else: ?>
<div class="lw-table-wrap">
<table class="lw-table table table-hover">
    <thead>
        <tr>
            <th><?php echo get_string('workshopcode', 'local_workshops'); ?></th>
            <th><?php echo get_string('workshopname', 'local_workshops'); ?></th>
            <th><?php echo get_string('program', 'local_workshops'); ?></th>
            <th><?php echo get_string('linkedcourse', 'local_workshops'); ?></th>
            <th><?php echo get_string('trainer', 'local_workshops'); ?></th>
            <th><?php echo get_string('date', 'local_workshops'); ?></th>
            <th><?php echo get_string('status', 'local_workshops'); ?></th>
            <th><?php echo get_string('deliverytype', 'local_workshops'); ?></th>
            <th><?php echo get_string('participantcount', 'local_workshops'); ?></th>
            <th><?php echo get_string('actions', 'local_workshops'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($workshops as $w): ?>
        <?php
        $cnt   = isset($participantcounts[$w->id]) ? $participantcounts[$w->id] : 0;
        $start = $w->startdatetime ? userdate($w->startdatetime, '%d %b %Y') : '—';
        $trainer = !empty($workshoptrainers[$w->id])
            ? htmlspecialchars(implode(', ', $workshoptrainers[$w->id]))
            : '—';
        $canEdit = has_capability('local/workshops:edit', $context);
        $canDel  = has_capability('local/workshops:delete', $context);
        ?>
        <tr>
            <td><span class="lw-code"><?php echo s($w->workshopcode); ?></span></td>
            <td>
                <a href="<?php echo (new moodle_url('/local/workshops/view.php', ['id' => $w->id]))->out(); ?>">
                    <?php echo s($w->workshopname); ?>
                </a>
            </td>
            <td><?php echo $w->program ? s($w->program) : '—'; ?></td>
            <td><?php echo $w->coursefullname ? '<span title="' . s($w->coursefullname) . '">' . s(shorten_text($w->coursefullname, 30)) . '</span>' : '—'; ?></td>
            <td><?php echo $trainer; ?></td>
            <td><?php echo $start; ?></td>
            <td><?php echo local_workshops_status_badge($w->status); ?></td>
            <td><?php echo local_workshops_delivery_badge($w->deliverytype); ?></td>
            <td><span class="badge badge-secondary"><?php echo $cnt; ?></span></td>
            <td class="lw-actions">
                <a href="<?php echo (new moodle_url('/local/workshops/view.php', ['id' => $w->id]))->out(); ?>"
                   class="btn btn-xs btn-outline-secondary" title="View">View</a>
                <?php if ($canEdit): ?>
                <a href="<?php echo (new moodle_url('/local/workshops/edit.php', ['id' => $w->id]))->out(); ?>"
                   class="btn btn-xs btn-outline-primary" title="Edit">Edit</a>
                <?php endif; ?>
                <?php if ($canDel): ?>
                <a href="<?php echo (new moodle_url('/local/workshops/edit.php', ['id' => $w->id, 'delete' => 1, 'sesskey' => sesskey()]))->out(); ?>"
                   class="btn btn-xs btn-outline-danger lw-confirm-delete" title="Delete"
                   data-confirm="<?php echo s(get_string('confirmdeleteworkshop', 'local_workshops')); ?>">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php
// Pagination
if ($total > $perpage) {
    $purl = new moodle_url('/local/workshops/manage.php', $filterparams);
    echo $OUTPUT->paging_bar($total, $page, $perpage, $purl);
}
?>
<?php endif; ?>

</div>
<?php
echo $OUTPUT->footer();
