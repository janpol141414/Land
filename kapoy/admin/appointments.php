<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Appointment.php';
require_once '../models/Notification.php';
require_once '../helpers/EmailHelper.php';

if (!isLoggedIn() || !hasRole('admin')) redirect('/auth/login.php');

$db = (new Database())->getConnection();
$appointmentModel = new Appointment($db);

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $apt_id  = intval($_POST['apt_id']);
    $status  = $_POST['new_status'];
    $allowed = ['pending','confirmed','in_progress','completed','cancelled'];
    if ($apt_id && in_array($status, $allowed)) {
        if ($appointmentModel->updateStatus($apt_id, $status)) {
            $apt = $appointmentModel->getById($apt_id);
            $notif = new Notification($db);
            $notif->create($apt['client_id'], 'Appointment Status Updated',
                "Your appointment ({$apt['confirmation_code']}) status changed to: " . ucfirst(str_replace('_',' ',$status)),
                'appointment', BASE_URL.'/client/track-status.php?id='.$apt_id);
            EmailHelper::sendStatusUpdate($apt['client_email'], $apt['client_name'], $apt, $status);
            $success = 'Status updated successfully.';
        } else { $error = 'Update failed.'; }
    }
}

$statusFilter = $_GET['status'] ?? '';
$appointments  = $appointmentModel->getAll($statusFilter ? ['status'=>$statusFilter] : []);
$stats         = $appointmentModel->getStats();

$selectedId  = intval($_GET['id'] ?? 0);
$selectedApt = $selectedId ? $appointmentModel->getById($selectedId) : null;
$progress    = $selectedId ? $appointmentModel->getProgressUpdates($selectedId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Appointments – Admin | GeoSurvey</title>
<link rel="stylesheet" href="../assets/css/main.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="../assets/css/tracking.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="app-body">
<?php include '../includes/header.php'; ?>
<div class="app-layout">
<?php include '../includes/sidebar_admin.php'; ?>
<main class="main-content">

<div class="page-header">
    <div><h1><i class="fas fa-calendar-alt"></i> Appointments</h1><p>Manage and update all survey appointments</p></div>
    <a href="schedules.php" class="btn-primary"><i class="fas fa-clock"></i> Manage Schedules</a>
</div>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- Stats -->
<div class="stats-grid mini" style="grid-template-columns:repeat(5,1fr)">
    <?php
    $sc = [
        ['label'=>'Total',       'val'=>$stats['total'],       'icon'=>'fa-calendar-alt', 'color'=>'#667eea'],
        ['label'=>'Pending',     'val'=>$stats['pending'],     'icon'=>'fa-clock',        'color'=>'#f093fb'],
        ['label'=>'Confirmed',   'val'=>$stats['confirmed'],   'icon'=>'fa-check',        'color'=>'#4facfe'],
        ['label'=>'In Progress', 'val'=>$stats['in_progress'], 'icon'=>'fa-hard-hat',     'color'=>'#fa709a'],
        ['label'=>'Completed',   'val'=>$stats['completed'],   'icon'=>'fa-trophy',       'color'=>'#43e97b'],
    ];
    foreach ($sc as $s): ?>
    <div class="stat-card mini" style="--accent:<?= $s['color'] ?>">
        <div class="stat-icon"><i class="fas <?= $s['icon'] ?>"></i></div>
        <div class="stat-info"><span class="stat-value"><?= $s['val'] ?></span><span class="stat-label"><?= $s['label'] ?></span></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filter Tabs -->
<div class="filter-bar">
    <div class="filter-tabs">
        <?php foreach ([''=>'All','pending'=>'Pending','confirmed'=>'Confirmed','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v): ?>
        <a href="appointments.php?status=<?= $k ?><?= $selectedId ? '&id='.$selectedId : '' ?>"
           class="filter-tab <?= $statusFilter===$k ? 'active' : '' ?>"><?= $v ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="admin-layout">
    <!-- List -->
    <div class="admin-list">
        <?php if (empty($appointments)): ?>
        <div class="empty-state small"><i class="fas fa-calendar-times"></i><p>No appointments found</p></div>
        <?php else: foreach ($appointments as $apt): ?>
        <a href="appointments.php?id=<?= $apt['id'] ?><?= $statusFilter ? '&status='.$statusFilter : '' ?>"
           class="admin-list-item <?= $selectedId==$apt['id'] ? 'active' : '' ?>">
            <div class="admin-item-avatar">
                <img src="<?= UPLOADS_URL ?>/profiles/<?= $apt['client_photo'] ?? 'default_avatar.png' ?>" alt=""
                     onerror="this.src='<?= ASSETS_URL ?>/images/default_avatar.png'">
            </div>
            <div class="admin-item-info">
                <strong><?= htmlspecialchars($apt['client_name']) ?></strong>
                <span><?= htmlspecialchars($apt['service_type']) ?></span>
                <span class="admin-item-date"><?= date('M d, Y', strtotime($apt['appointment_date'])) ?></span>
            </div>
            <span class="status-badge <?= $apt['status'] ?>"><?= ucfirst(str_replace('_',' ',$apt['status'])) ?></span>
        </a>
        <?php endforeach; endif; ?>
    </div>

    <!-- Detail -->
    <div class="admin-detail">
        <?php if (!$selectedApt): ?>
        <div class="admin-placeholder"><i class="fas fa-calendar-alt"></i><h3>Select an appointment</h3><p>Click to view details and update status</p></div>
        <?php else: ?>
        <div class="tracking-detail-content">
            <div class="tracking-detail-header">
                <div class="tracking-conf-code">
                    <span>Confirmation Code</span>
                    <strong><?= htmlspecialchars($selectedApt['confirmation_code']) ?></strong>
                </div>
                <span class="status-badge-lg <?= $selectedApt['status'] ?>"><?= ucfirst(str_replace('_',' ',$selectedApt['status'])) ?></span>
            </div>

            <div class="tracking-info-grid">
                <div class="tracking-info-card">
                    <h4><i class="fas fa-user"></i> Client</h4>
                    <div class="engineer-tracking-card">
                        <img src="<?= UPLOADS_URL ?>/profiles/<?= $selectedApt['client_photo'] ?? 'default_avatar.png' ?>" alt=""
                             onerror="this.src='<?= ASSETS_URL ?>/images/default_avatar.png'">
                        <div>
                            <strong><?= htmlspecialchars($selectedApt['client_name']) ?></strong>
                            <span><?= htmlspecialchars($selectedApt['client_email']) ?></span>
                            <span><?= htmlspecialchars($selectedApt['client_phone'] ?? '') ?></span>
                        </div>
                    </div>
                </div>
                <div class="tracking-info-card">
                    <h4><i class="fas fa-hard-hat"></i> Engineer</h4>
                    <div class="engineer-tracking-card">
                        <img src="<?= UPLOADS_URL ?>/profiles/<?= $selectedApt['engineer_photo'] ?? 'default_avatar.png' ?>" alt=""
                             onerror="this.src='<?= ASSETS_URL ?>/images/default_avatar.png'">
                        <div>
                            <strong><?= htmlspecialchars($selectedApt['engineer_name']) ?></strong>
                            <span><?= htmlspecialchars($selectedApt['specialization']) ?></span>
                            <span><?= htmlspecialchars($selectedApt['engineer_phone'] ?? '') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tracking-info-card" style="margin-bottom:16px">
                <h4><i class="fas fa-map"></i> Service Details</h4>
                <div class="info-rows">
                    <?php foreach ([
                        'Service'  => $selectedApt['service_type'],
                        'Date'     => date('F d, Y', strtotime($selectedApt['appointment_date'])),
                        'Time'     => date('h:i A', strtotime($selectedApt['appointment_time'])),
                        'Location' => $selectedApt['location'],
                        'Amount'   => '₱'.number_format($selectedApt['total_amount'],2),
                        'Notes'    => $selectedApt['notes'] ?: '—',
                    ] as $lbl => $val): ?>
                    <div class="info-row"><span><?= $lbl ?></span><strong><?= htmlspecialchars($val) ?></strong></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Progress Timeline -->
            <?php if (!empty($progress)): ?>
            <div class="progress-timeline" style="margin-bottom:16px">
                <h4><i class="fas fa-history"></i> Progress Updates</h4>
                <div class="timeline">
                    <?php foreach ($progress as $upd): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <strong><?= htmlspecialchars($upd['status']) ?></strong>
                                <span><?= date('M d, Y h:i A', strtotime($upd['created_at'])) ?></span>
                            </div>
                            <p><?= htmlspecialchars($upd['description']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Update Status -->
            <div class="status-update-form">
                <h4><i class="fas fa-sync-alt"></i> Update Status</h4>
                <form method="POST">
                    <input type="hidden" name="apt_id" value="<?= $selectedApt['id'] ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>New Status</label>
                            <select name="new_status">
                                <?php foreach (['pending','confirmed','in_progress','completed','cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $selectedApt['status']===$s ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_',' ',$s)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="update_status" class="btn-update-status">
                        <i class="fas fa-save"></i> Update & Notify Client
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</main></div>
<script src="../assets/js/dashboard.js"></script>
</body></html>
