<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Schedule.php';
require_once '../models/Engineer.php';

if (!isLoggedIn() || !hasRole('admin')) redirect('/auth/login.php');

$db = (new Database())->getConnection();
$scheduleModel = new Schedule($db);
$engineerModel = new Engineer($db);

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id   = intval($_POST['id'] ?? 0);
        $data = [
            'engineer_id'  => intval($_POST['engineer_id']),
            'date'         => $_POST['date'],
            'start_time'   => $_POST['start_time'],
            'end_time'     => $_POST['end_time'],
            'slot_type'    => $_POST['slot_type'] ?? 'morning',
            'is_available' => intval($_POST['is_available'] ?? 1),
            'notes'        => sanitize($_POST['notes'] ?? ''),
        ];
        if ($id) { $scheduleModel->update($id, $data); $success = 'Slot updated.'; }
        else     { $scheduleModel->create($data);       $success = 'Slot added.'; }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) { $scheduleModel->delete($id); $success = 'Slot deleted.'; }
    }
}

$engineers = $engineerModel->getAll();
$filterEng = intval($_GET['engineer_id'] ?? 0);
$slots     = $filterEng ? $scheduleModel->getByEngineerId($filterEng) : $scheduleModel->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Schedules – Admin | GeoSurvey</title>
<link rel="stylesheet" href="../assets/css/main.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="app-body">
<?php include '../includes/header.php'; ?>
<div class="app-layout">
<?php include '../includes/sidebar_admin.php'; ?>
<main class="main-content">

<div class="page-header">
    <div><h1><i class="fas fa-clock"></i> Schedule Slots</h1><p>Manage engineer availability slots</p></div>
    <button class="btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Slot</button>
</div>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

<!-- Filter -->
<div class="schedule-calendar-wrapper">
    <div class="schedule-filter">
        <form method="GET" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <label>Filter by Engineer:</label>
            <select name="engineer_id" onchange="this.form.submit()">
                <option value="">All Engineers</option>
                <?php foreach ($engineers as $eng): ?>
                <option value="<?= $eng['id'] ?>" <?= $filterEng==$eng['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($eng['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <span style="font-size:13px;color:#9ca3af"><?= count($slots) ?> slots found</span>
    </div>

    <div class="table-responsive">
        <table class="slots-table">
            <thead><tr>
                <th>Engineer</th><th>Date</th><th>Start</th><th>End</th>
                <th>Type</th><th>Status</th><th>Notes</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($slots)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#9ca3af">No slots found</td></tr>
            <?php else: foreach ($slots as $slot): ?>
            <tr>
                <td><strong><?= htmlspecialchars($slot['engineer_name'] ?? '—') ?></strong></td>
                <td><?= date('M d, Y', strtotime($slot['date'])) ?></td>
                <td><?= date('h:i A', strtotime($slot['start_time'])) ?></td>
                <td><?= date('h:i A', strtotime($slot['end_time'])) ?></td>
                <td><span class="status-badge confirmed"><?= ucfirst(str_replace('_',' ',$slot['slot_type'])) ?></span></td>
                <td>
                    <?php if ($slot['is_available']): ?>
                    <span class="slot-available"><i class="fas fa-check-circle"></i> Available</span>
                    <?php else: ?>
                    <span class="slot-unavailable"><i class="fas fa-times-circle"></i> Unavailable</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($slot['notes'] ?? '—') ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <button class="btn-table-action" onclick="editSlot(<?= htmlspecialchars(json_encode($slot)) ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this slot?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $slot['id'] ?>">
                            <button type="submit" class="btn-table-action" style="background:#fee2e2;color:#dc2626">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" style="display:none" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-clock"></i> Add Schedule Slot</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="slotId" value="0">

            <div class="form-group">
                <label>Engineer *</label>
                <select name="engineer_id" id="slotEng" required>
                    <option value="">-- Select Engineer --</option>
                    <?php foreach ($engineers as $eng): ?>
                    <option value="<?= $eng['id'] ?>"><?= htmlspecialchars($eng['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date *</label>
                <input type="date" name="date" id="slotDate" required min="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Start Time *</label>
                    <input type="time" name="start_time" id="slotStart" required>
                </div>
                <div class="form-group">
                    <label>End Time *</label>
                    <input type="time" name="end_time" id="slotEnd" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Slot Type</label>
                    <select name="slot_type" id="slotType">
                        <option value="morning">Morning</option>
                        <option value="afternoon">Afternoon</option>
                        <option value="evening">Evening</option>
                        <option value="full_day">Full Day</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Availability</label>
                    <select name="is_available" id="slotAvail">
                        <option value="1">Available</option>
                        <option value="0">Unavailable</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <input type="text" name="notes" id="slotNotes" placeholder="Optional notes...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-modal-save"><i class="fas fa-save"></i> Save Slot</button>
            </div>
        </form>
    </div>
</div>

</main></div>
<script src="../assets/js/dashboard.js"></script>
<script>
function openModal(){ document.getElementById('modalOverlay').style.display='flex'; }
function closeModal(){ document.getElementById('modalOverlay').style.display='none'; }
function editSlot(s){
    document.getElementById('slotId').value=s.id;
    document.getElementById('modalTitle').innerHTML='<i class="fas fa-edit"></i> Edit Slot';
    document.getElementById('slotEng').value=s.engineer_id;
    document.getElementById('slotDate').value=s.date;
    document.getElementById('slotStart').value=s.start_time;
    document.getElementById('slotEnd').value=s.end_time;
    document.getElementById('slotType').value=s.slot_type;
    document.getElementById('slotAvail').value=s.is_available;
    document.getElementById('slotNotes').value=s.notes||'';
    openModal();
}
</script>
</body></html>
