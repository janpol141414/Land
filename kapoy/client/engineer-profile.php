<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Engineer.php';

if (!isLoggedIn() || !hasRole('client')) redirect('/auth/login.php');

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('/client/engineers.php');

$db = (new Database())->getConnection();
$engineerModel = new Engineer($db);
$engineer = $engineerModel->getById($id);

if (!$engineer) redirect('/client/engineers.php');

$reviews = $engineerModel->getReviews($id);
$availableSlots = $engineerModel->getAvailableSlots($id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($engineer['name']) ?> - Engineer Profile</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/engineers.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="../assets/css/booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="app-body">

<?php include '../includes/header.php'; ?>

<div class="app-layout">
    <?php include '../includes/sidebar_client.php'; ?>

    <main class="main-content">
        <div class="back-nav">
            <a href="engineers.php"><i class="fas fa-arrow-left"></i> Back to Engineers</a>
        </div>

        <!-- Profile Hero -->
        <div class="profile-hero">
            <div class="profile-cover"></div>
            <div class="profile-hero-content">
                <div class="profile-photo-lg">
                    <img src="<?= UPLOADS_URL ?>/profiles/<?= $engineer['profile_photo'] ?? 'default_avatar.png' ?>" 
                         alt="<?= htmlspecialchars($engineer['name']) ?>"
                         onerror="this.src='<?= ASSETS_URL ?>/images/default_avatar.png'">
                    <div class="availability-ring <?= $engineer['availability_status'] ?>"></div>
                </div>
                <div class="profile-hero-info">
                    <div class="profile-name-row">
                        <h1><?= htmlspecialchars($engineer['name']) ?></h1>
                        <span class="verified-badge"><i class="fas fa-check-circle"></i> PRC Licensed</span>
                    </div>
                    <p class="profile-specialization"><?= htmlspecialchars($engineer['specialization']) ?></p>
                    <div class="profile-meta-row">
                        <?php if ($engineer['company_name']): ?>
                        <span><i class="fas fa-building"></i> <?= htmlspecialchars($engineer['company_name']) ?></span>
                        <?php endif; ?>
                        <span><i class="fas fa-briefcase"></i> <?= $engineer['experience_years'] ?> years experience</span>
                        <span><i class="fas fa-id-card"></i> <?= htmlspecialchars($engineer['license_number']) ?></span>
                    </div>
                    <div class="profile-rating-row">
                        <div class="stars-lg">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= round($engineer['rating']) ? 'filled' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-score"><?= number_format($engineer['rating'], 1) ?></span>
                        <span class="rating-count">(<?= $engineer['total_reviews'] ?> reviews)</span>
                        <span class="availability-status-badge <?= $engineer['availability_status'] ?>">
                            <i class="fas fa-circle"></i> <?= ucfirst($engineer['availability_status']) ?>
                        </span>
                    </div>
                </div>
                <div class="profile-hero-actions">
                    <div class="hourly-rate">
                        <span class="rate-label">Hourly Rate</span>
                        <span class="rate-value">₱<?= number_format($engineer['hourly_rate'], 0) ?></span>
                    </div>
                    <?php if ($engineer['availability_status'] === 'available'): ?>
                    <a href="book-appointment.php?engineer_id=<?= $engineer['id'] ?>" class="btn-book-profile">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </a>
                    <?php else: ?>
                    <button class="btn-book-profile disabled" disabled>
                        <i class="fas fa-clock"></i> Currently Unavailable
                    </button>
                    <?php endif; ?>
                    <a href="messages.php?contact=<?= $engineer['user_id'] ?>" class="btn-message-profile">
                        <i class="fas fa-comment"></i> Send Message
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Tabs -->
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="showTab('about')">About</button>
            <button class="tab-btn" onclick="showTab('services')">Services</button>
            <button class="tab-btn" onclick="showTab('availability')">Availability</button>
            <button class="tab-btn" onclick="showTab('reviews')">Reviews (<?= count($reviews) ?>)</button>
        </div>

        <div class="profile-content">
            <!-- About Tab -->
            <div class="tab-content active" id="tab-about">
                <div class="profile-grid">
                    <div class="profile-main">
                        <div class="profile-section-card">
                            <h3><i class="fas fa-user"></i> About</h3>
                            <p><?= nl2br(htmlspecialchars($engineer['bio'] ?? $engineer['user_bio'] ?? 'No bio available.')) ?></p>
                        </div>

                        <div class="profile-section-card">
                            <h3><i class="fas fa-tools"></i> Skills & Expertise</h3>
                            <div class="skills-grid">
                                <?php if ($engineer['skills']): ?>
                                <?php foreach (explode(',', $engineer['skills']) as $skill): ?>
                                <span class="skill-chip"><?= htmlspecialchars(trim($skill)) ?></span>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <p>No skills listed.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="profile-section-card">
                            <h3><i class="fas fa-certificate"></i> Certifications</h3>
                            <?php if ($engineer['certifications']): ?>
                            <div class="certs-list">
                                <?php foreach (explode(',', $engineer['certifications']) as $cert): ?>
                                <div class="cert-item">
                                    <i class="fas fa-award"></i>
                                    <span><?= htmlspecialchars(trim($cert)) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <p>No certifications listed.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="profile-sidebar">
                        <div class="profile-section-card">
                            <h3><i class="fas fa-info-circle"></i> Details</h3>
                            <div class="detail-list">
                                <div class="detail-item">
                                    <span class="detail-label">License No.</span>
                                    <span class="detail-value"><?= htmlspecialchars($engineer['license_number'] ?? 'N/A') ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Experience</span>
                                    <span class="detail-value"><?= $engineer['experience_years'] ?> years</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Hourly Rate</span>
                                    <span class="detail-value">₱<?= number_format($engineer['hourly_rate'], 2) ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Company</span>
                                    <span class="detail-value"><?= htmlspecialchars($engineer['company_name'] ?? 'Independent') ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value availability-<?= $engineer['availability_status'] ?>">
                                        <?= ucfirst($engineer['availability_status']) ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Member Since</span>
                                    <span class="detail-value"><?= date('M Y', strtotime($engineer['member_since'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if ($engineer['company_name']): ?>
                        <div class="profile-section-card">
                            <h3><i class="fas fa-building"></i> Company</h3>
                            <div class="company-mini">
                                <strong><?= htmlspecialchars($engineer['company_name']) ?></strong>
                                <?php if ($engineer['company_address']): ?>
                                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($engineer['company_address']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Services Tab -->
            <div class="tab-content" id="tab-services">
                <div class="services-offered">
                    <h3>Services Offered</h3>
                    <div class="services-list-profile">
                        <?php
                        $servicesList = [
                            ['name' => 'Boundary Survey', 'price' => '₱5,000+', 'icon' => 'fa-border-all', 'days' => '3-5 days'],
                            ['name' => 'Topographic Survey', 'price' => '₱8,000+', 'icon' => 'fa-mountain', 'days' => '5-7 days'],
                            ['name' => 'Construction Layout', 'price' => '₱6,000+', 'icon' => 'fa-building', 'days' => '2-3 days'],
                            ['name' => 'As-Built Survey', 'price' => '₱7,000+', 'icon' => 'fa-drafting-compass', 'days' => '4-6 days'],
                        ];
                        foreach ($servicesList as $svc):
                        ?>
                        <div class="service-profile-item">
                            <div class="svc-icon"><i class="fas <?= $svc['icon'] ?>"></i></div>
                            <div class="svc-info">
                                <h4><?= $svc['name'] ?></h4>
                                <span><?= $svc['days'] ?></span>
                            </div>
                            <div class="svc-price"><?= $svc['price'] ?></div>
                            <a href="book-appointment.php?engineer_id=<?= $engineer['id'] ?>&service=<?= urlencode($svc['name']) ?>" 
                               class="btn-book-svc">Book</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Availability Tab -->
            <div class="tab-content" id="tab-availability">
                <div class="availability-section">
                    <h3>Available Slots</h3>
                    <?php if (empty($availableSlots)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No available slots at the moment</p>
                    </div>
                    <?php else: ?>
                    <div class="slots-grid">
                        <?php foreach ($availableSlots as $slot): ?>
                        <div class="slot-card">
                            <div class="slot-date">
                                <span class="slot-day"><?= date('D', strtotime($slot['date'])) ?></span>
                                <span class="slot-num"><?= date('d', strtotime($slot['date'])) ?></span>
                                <span class="slot-month"><?= date('M Y', strtotime($slot['date'])) ?></span>
                            </div>
                            <div class="slot-time">
                                <i class="fas fa-clock"></i>
                                <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                            </div>
                            <span class="slot-type-badge"><?= ucfirst(str_replace('_', ' ', $slot['slot_type'])) ?></span>
                            <a href="book-appointment.php?engineer_id=<?= $engineer['id'] ?>&date=<?= $slot['date'] ?>" 
                               class="btn-book-slot">Book This Slot</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-content" id="tab-reviews">
                <div class="reviews-section">
                    <div class="reviews-summary">
                        <div class="rating-big">
                            <span class="rating-number"><?= number_format($engineer['rating'], 1) ?></span>
                            <div class="stars-big">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= round($engineer['rating']) ? 'filled' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span><?= $engineer['total_reviews'] ?> reviews</span>
                        </div>
                    </div>

                    <div class="reviews-list">
                        <?php if (empty($reviews)): ?>
                        <div class="empty-state">
                            <i class="fas fa-star"></i>
                            <p>No reviews yet</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <img src="<?= UPLOADS_URL ?>/profiles/<?= $review['client_photo'] ?? 'default_avatar.png' ?>" 
                                     alt="" onerror="this.src='<?= ASSETS_URL ?>/images/default_avatar.png'">
                                <div class="review-meta">
                                    <strong><?= htmlspecialchars($review['client_name']) ?></strong>
                                    <div class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="review-date"><?= date('M d, Y', strtotime($review['created_at'])) ?></span>
                            </div>
                            <p class="review-comment"><?= htmlspecialchars($review['comment']) ?></p>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/chatbot.php'; ?>
<script src="../assets/js/chatbot.js"></script>
<script>
function showTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}
</script>
</body>
</html>
