<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Appointment.php';
require_once '../models/Payment.php';
require_once '../models/Notification.php';

if (!isLoggedIn() || !hasRole('client')) redirect('/auth/login.php');

$db = (new Database())->getConnection();
$appointmentModel = new Appointment($db);
$paymentModel = new Payment($db);

$appointments = $appointmentModel->getByClientId($_SESSION['user_id']);
$payments = $paymentModel->getByClientId($_SESSION['user_id']);

$stats = [
    'total' => count($appointments),
    'pending' => count(array_filter($appointments, fn($a) => $a['status'] === 'pending')),
    'confirmed' => count(array_filter($appointments, fn($a) => $a['status'] === 'confirmed')),
    'completed' => count(array_filter($appointments, fn($a) => $a['status'] === 'completed')),
];

$recentAppointments = array_slice($appointments, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - GeoSurvey Portal</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="app-body">

<?php include '../includes/header.php'; ?>

<div class="app-layout">
    <?php include '../includes/sidebar_client.php'; ?>

    <main class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h1>Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>! 👋</h1>
                <p>Here's what's happening with your survey projects today.</p>
            </div>
            <div class="welcome-actions">
                <a href="book-appointment.php" class="btn-primary">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                </a>
                <a href="engineers.php" class="btn-outline">
                    <i class="fas fa-search"></i> Find Engineers
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card" style="--accent: #667eea;">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['total'] ?></span>
                    <span class="stat-label">Total Appointments</span>
                </div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> All time</div>
            </div>
            <div class="stat-card" style="--accent: #f093fb;">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['pending'] ?></span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-trend neutral"><i class="fas fa-minus"></i> Awaiting</div>
            </div>
            <div class="stat-card" style="--accent: #4facfe;">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['confirmed'] ?></span>
                    <span class="stat-label">Confirmed</span>
                </div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> Active</div>
            </div>
            <div class="stat-card" style="--accent: #43e97b;">
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['completed'] ?></span>
                    <span class="stat-label">Completed</span>
                </div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> Done</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Recent Appointments -->
            <div class="dashboard-card wide">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Recent Appointments</h3>
                    <a href="track-status.php" class="card-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentAppointments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h4>No appointments yet</h4>
                        <p>Book your first appointment to get started</p>
                        <a href="book-appointment.php" class="btn-primary">Book Now</a>
                    </div>
                    <?php else: ?>
                    <div class="appointments-list">
                        <?php foreach ($recentAppointments as $apt): ?>
                        <div class="appointment-item">
                            <div class="apt-engineer">
                                <img src="<?= UPLOADS_URL ?>/profiles/<?= $apt['engineer_photo'] ?? 'default_avatar.png' ?>" 
                                     alt="" onerror="this.src='<?= ASSETS_URL ?>/images/default_avatar.png'">
                                <div>
                                    <strong><?= htmlspecialchars($apt['engineer_name']) ?></strong>
                                    <span><?= htmlspecialchars($apt['specialization']) ?></span>
                                </div>
                            </div>
                            <div class="apt-service">
                                <i class="fas fa-map"></i>
                                <?= htmlspecialchars($apt['service_type']) ?>
                            </div>
                            <div class="apt-date">
                                <i class="fas fa-calendar"></i>
                                <?= date('M d, Y', strtotime($apt['appointment_date'])) ?>
                                <span><?= date('h:i A', strtotime($apt['appointment_time'])) ?></span>
                            </div>
                            <div class="apt-status">
                                <span class="status-badge <?= $apt['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $apt['status'])) ?>
                                </span>
                            </div>
                            <!-- Eye icon → track status for this appointment -->
                            <a href="track-status.php?id=<?= $apt['id'] ?>" class="apt-action" title="Track Status">
                                <i class="fas fa-eye"></i>
                            </a>
                            <!-- Message icon → engineer's user account (not client) -->
                            <a href="messages.php?contact=<?= $apt['engineer_user_id'] ?? '' ?>" class="apt-action" title="Message Engineer" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                                <i class="fas fa-comment"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="book-appointment.php" class="quick-action-btn">
                            <div class="qa-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <span>Book Appointment</span>
                        </a>
                        <a href="engineers.php" class="quick-action-btn">
                            <div class="qa-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                <i class="fas fa-hard-hat"></i>
                            </div>
                            <span>Find Engineers</span>
                        </a>
                        <a href="payment.php" class="quick-action-btn">
                            <div class="qa-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <span>Submit Payment</span>
                        </a>
                        <a href="track-status.php" class="quick-action-btn">
                            <div class="qa-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                                <i class="fas fa-map-pin"></i>
                            </div>
                            <span>Track Status</span>
                        </a>
                        <a href="feedback.php" class="quick-action-btn">
                            <div class="qa-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                                <i class="fas fa-star"></i>
                            </div>
                            <span>Give Feedback</span>
                        </a>
                        <a href="messages.php" class="quick-action-btn">
                            <div class="qa-icon" style="background: linear-gradient(135deg, #a18cd1, #fbc2eb);">
                                <i class="fas fa-comments"></i>
                            </div>
                            <span>Messages</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-credit-card"></i> Recent Payments</h3>
                    <a href="payment.php" class="card-link">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                    <div class="empty-state small">
                        <i class="fas fa-receipt"></i>
                        <p>No payment records</p>
                    </div>
                    <?php else: ?>
                    <?php foreach (array_slice($payments, 0, 4) as $pay): ?>
                    <div class="payment-mini-item">
                        <div class="pay-info">
                            <strong><?= htmlspecialchars($pay['service_type']) ?></strong>
                            <span><?= date('M d, Y', strtotime($pay['created_at'])) ?></span>
                        </div>
                        <div class="pay-right">
                            <span class="pay-amount">₱<?= number_format($pay['amount'], 2) ?></span>
                            <span class="status-badge <?= $pay['status'] ?>"><?= ucfirst($pay['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- AI Chatbot -->
<?php include '../includes/chatbot.php'; ?>

<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/chatbot.js"></script>
</body>
</html>
