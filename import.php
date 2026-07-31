<?php
// This file is part of Moodle - http://moodle.org/
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/workshops/lib.php');

// CRITICAL: import.php is not its own admin node, but renders with the 'admin' layout.
// Anchor it under 'Manage Workshops' so the admin tree can locate it; an unlocated
// admin-layout page collapses BOTH the primary and secondary navigation.
admin_externalpage_setup('local_workshops_manage');
$context = context_system::instance();
require_capability('local/workshops:manageparticipants', $context);

$workshopid = required_param('workshopid', PARAM_INT);
$workshop   = $DB->get_record('local_workshops', ['id' => $workshopid], '*', MUST_EXIST);

// admin_externalpage_setup() set the 'admin' layout and context; override URL/title/heading.
$PAGE->set_url('/local/workshops/import.php', ['workshopid' => $workshopid]);
$PAGE->set_title('Import Participants — ' . $workshop->workshopname);
$PAGE->set_heading('Import Participants');
$results   = null;
$previewed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $doImport = optional_param('doimport', 0, PARAM_INT);

    if ($doImport) {
        // Confirm step: row data was embedded as hidden fields in the preview form,
        // so no CSV file re-upload is needed (and none will be present).
        // Use $_POST directly — optional_param_array cannot handle 2D arrays (each row is
        // an array of fields) without Moodle attempting to clean_param an array value
        // with a scalar PARAM type, which throws an exception.
        $postedRows = isset($_POST['rows']) ? $_POST['rows'] : [];
        $imported = 0;
        $skipped  = 0;
        foreach ($postedRows as $row) {
            $rowstatus = isset($row['status']) ? $row['status'] : 'invalid';
            if ($rowstatus === 'ok') {
                $email     = isset($row['email'])        ? trim($row['email'])        : '';
                $firstname = isset($row['firstname'])    ? trim($row['firstname'])    : '';
                $lastname  = isset($row['lastname'])     ? trim($row['lastname'])     : '';
                $org       = isset($row['organisation']) ? trim($row['organisation']) : '';
                // Re-check still valid and not a duplicate (race condition guard).
                if (empty($email) || !validate_email($email)) { $skipped++; continue; }
                if ($DB->record_exists('local_workshop_participants', ['workshopid' => $workshopid, 'email' => $email])) {
                    $skipped++; continue;
                }
                $moodleuser = $DB->get_record('user', ['email' => $email, 'deleted' => 0]);
                $rec = (object)[
                    'workshopid'    => $workshopid,
                    'userid'        => $moodleuser ? $moodleuser->id : null,
                    'firstname'     => $firstname,
                    'lastname'      => $lastname,
                    'email'         => $email,
                    'organisation'  => $org,
                    'bookingstatus' => 'Booked',
                    'bookingdate'   => time(),
                    'notes'         => '',
                ];
                $DB->insert_record('local_workshop_participants', $rec);
                $imported++;
            } else {
                $skipped++;
            }
        }
        redirect(
            new moodle_url('/local/workshops/view.php', ['id' => $workshopid, 'tab' => 'participants']),
            "Import complete: $imported imported, $skipped skipped.",
            null, \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        // Preview step: parse the uploaded CSV file.
        if (!isset($_FILES['csvfile']) || $_FILES['csvfile']['error'] !== UPLOAD_ERR_OK) {
            $results = ['error' => 'No file uploaded or upload error.'];
        } else {
            $tmpfile = $_FILES['csvfile']['tmp_name'];
            $rows = [];
            $fh = fopen($tmpfile, 'r');

            // Skip header row.
            $header = fgetcsv($fh);
            $header = array_map('strtolower', array_map('trim', $header));
            $colmap = [];
            foreach (['firstname', 'lastname', 'email', 'organisation'] as $col) {
                $idx = array_search($col, $header);
                $colmap[$col] = $idx !== false ? $idx : -1;
            }

            $rownum = 1;
            while (($data = fgetcsv($fh)) !== false) {
                $rownum++;
                $row = [
                    'firstname'    => $colmap['firstname']    >= 0 ? trim($data[$colmap['firstname']])    : '',
                    'lastname'     => $colmap['lastname']     >= 0 ? trim($data[$colmap['lastname']])     : '',
                    'email'        => $colmap['email']        >= 0 ? trim($data[$colmap['email']])        : '',
                    'organisation' => $colmap['organisation'] >= 0 ? trim($data[$colmap['organisation']]) : '',
                    'rownum'       => $rownum,
                    'status'       => 'ok',
                    'message'      => '',
                ];

                if (empty($row['email'])) {
                    $row['status']  = 'invalid';
                    $row['message'] = 'Missing email';
                } else if (!validate_email($row['email'])) {
                    $row['status']  = 'invalid';
                    $row['message'] = 'Invalid email';
                } else if ($DB->record_exists('local_workshop_participants', ['workshopid' => $workshopid, 'email' => $row['email']])) {
                    $row['status']  = 'duplicate';
                    $row['message'] = 'Already booked';
                }

                $rows[] = $row;
            }
            fclose($fh);
            $previewed = true;
            $results   = $rows;
        }
    }
}

echo $OUTPUT->header();
?>
<div class="lw-container">
<div class="lw-breadcrumb">
    <a href="<?php echo (new moodle_url('/local/workshops/view.php', ['id' => $workshopid, 'tab' => 'participants']))->out(); ?>">&larr; Back to Participants</a>
</div>

<h2>Import Participants — <?php echo s($workshop->workshopname); ?></h2>

<div class="lw-import-info card" style="padding:1rem;margin-bottom:1rem">
    <p><strong>CSV Format:</strong> The file must contain these columns (header row required):</p>
    <code>firstname, lastname, email, organisation</code>
    <p style="margin-top:0.5rem">Rows with duplicate emails (already booked) will be skipped. Rows with invalid emails will be skipped.</p>
</div>

<form method="post" enctype="multipart/form-data" action="<?php echo (new moodle_url('/local/workshops/import.php', ['workshopid' => $workshopid]))->out(false); ?>">
    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
    <input type="hidden" name="doimport" value="0" id="lw-doimport">
    <div class="lw-form-row">
        <label><strong>Select CSV File</strong></label>
        <input type="file" name="csvfile" accept=".csv" class="form-control" required>
    </div>
    <div style="margin-top:1rem">
        <button type="submit" class="btn btn-secondary">Preview Import</button>
    </div>
</form>

<?php if ($previewed && is_array($results)): ?>
<h3 style="margin-top:2rem">Import Preview</h3>
<?php
$okCount  = count(array_filter($results, function($r){ return $r['status'] === 'ok'; }));
$dupCount = count(array_filter($results, function($r){ return $r['status'] === 'duplicate'; }));
$badCount = count(array_filter($results, function($r){ return $r['status'] === 'invalid'; }));
?>
<p><?php echo $okCount; ?> will be imported, <?php echo $dupCount; ?> duplicates, <?php echo $badCount; ?> invalid.</p>

<table class="lw-table table table-sm">
    <thead><tr><th>Row</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Organisation</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($results as $r): ?>
    <tr class="<?php echo $r['status'] !== 'ok' ? 'table-warning' : ''; ?>">
        <td><?php echo $r['rownum']; ?></td>
        <td><?php echo s($r['firstname']); ?></td>
        <td><?php echo s($r['lastname']); ?></td>
        <td><?php echo s($r['email']); ?></td>
        <td><?php echo s($r['organisation']); ?></td>
        <td>
            <?php if ($r['status'] === 'ok'): ?>
                <span class="lw-badge lw-badge-completed">OK</span>
            <?php elseif ($r['status'] === 'duplicate'): ?>
                <span class="lw-badge lw-badge-postponed">Duplicate</span>
            <?php else: ?>
                <span class="lw-badge lw-badge-cancelled">Invalid: <?php echo s($r['message']); ?></span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php if ($okCount > 0): ?>
<form method="post" action="<?php echo (new moodle_url('/local/workshops/import.php', ['workshopid' => $workshopid]))->out(false); ?>">
    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
    <input type="hidden" name="doimport" value="1">
    <?php
    // Re-embed data so we don't need to re-upload the file.
    foreach ($results as $i => $r) {
        echo '<input type="hidden" name="rows[' . $i . '][firstname]"    value="' . s($r['firstname']) . '">';
        echo '<input type="hidden" name="rows[' . $i . '][lastname]"     value="' . s($r['lastname']) . '">';
        echo '<input type="hidden" name="rows[' . $i . '][email]"        value="' . s($r['email']) . '">';
        echo '<input type="hidden" name="rows[' . $i . '][organisation]" value="' . s($r['organisation']) . '">';
        echo '<input type="hidden" name="rows[' . $i . '][status]"       value="' . s($r['status']) . '">';
    }
    ?>
    <button type="submit" class="btn btn-primary">Confirm Import (<?php echo $okCount; ?> participants)</button>
    <a href="<?php echo (new moodle_url('/local/workshops/view.php', ['id' => $workshopid, 'tab' => 'participants']))->out(); ?>" class="btn btn-secondary">Cancel</a>
</form>
<?php else: ?>
<p>Nothing to import.</p>
<?php endif; ?>
<?php endif; ?>

</div>
<?php
echo $OUTPUT->footer();
