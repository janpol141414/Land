<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/Engineer.php';

if (isLoggedIn()) redirect('/client/dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'client';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!in_array($role, ['client', 'engineer'])) {
        $error = 'Invalid role selected.';
    } else {
        $db = (new Database())->getConnection();
        $userModel = new User($db);

        if ($userModel->emailExists($email)) {
            $error = 'This email is already registered. Please use a different email.';
        } else {
            // Handle profile photo upload
            $profile_photo = 'default_avatar.png';
            if (!empty($_FILES['profile_photo']['name'])) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'user_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], PROFILE_PHOTO_PATH . $filename)) {
                        $profile_photo = $filename;
                    }
                }
            }

            $userModel->name = $name;
            $userModel->email = $email;
            $userModel->password = $password;
            $userModel->role = $role;
            $userModel->phone = $phone;
            $userModel->address = trim($_POST['address'] ?? '');
            $userModel->profile_photo = $profile_photo;
            $userModel->bio = trim($_POST['bio'] ?? '');

            $userId = $userModel->register();

            if ($userId) {
                // If engineer, create engineer profile
                if ($role === 'engineer') {
                    $engineerModel = new Engineer($db);
                    $engineerModel->create([
                        'user_id' => $userId,
                        'company_id' => null,
                        'license_number' => trim($_POST['license_number'] ?? ''),
                        'specialization' => trim($_POST['specialization'] ?? ''),
                        'experience_years' => intval($_POST['experience_years'] ?? 0),
                        'availability_status' => 'available',
                        'bio' => trim($_POST['bio'] ?? ''),
                        'skills' => trim($_POST['skills'] ?? ''),
                        'certifications' => trim($_POST['certifications'] ?? ''),
                        'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0)
                    ]);
                }

                // Auto login
                $_SESSION['user_id'] = $userId;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                $_SESSION['profile_photo'] = $profile_photo;

                if ($role === 'engineer') redirect('/engineer/dashboard.php');
                else redirect('/client/dashboard.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Land Surveying Portal</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/enhancements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-body">

<div class="auth-container register-container">
    <div class="auth-left">
        <div class="auth-left-content">
            <div class="auth-brand">
                <div class="brand-icon-lg"><i class="fas fa-map-marked-alt"></i></div>
                <h1>Join GeoSurvey</h1>
                <p>Create your free account today</p>
            </div>
            <div class="register-benefits">
                <h3>Why Join Us?</h3>
                <div class="benefit-item"><i class="fas fa-check-circle"></i> Access 50+ licensed engineers</div>
                <div class="benefit-item"><i class="fas fa-check-circle"></i> Easy online appointment booking</div>
                <div class="benefit-item"><i class="fas fa-check-circle"></i> Real-time project tracking</div>
                <div class="benefit-item"><i class="fas fa-check-circle"></i> Secure payment processing</div>
                <div class="benefit-item"><i class="fas fa-check-circle"></i> 24/7 AI assistant support</div>
                <div class="benefit-item"><i class="fas fa-check-circle"></i> Digital survey reports</div>
            </div>
        </div>
    </div>

    <div class="auth-right register-right">
        <div class="auth-form-container">
            <div class="auth-form-header">
                <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
                <h2>Create Account</h2>
                <p>Fill in your details to get started</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Role Selection -->
            <div class="role-selector">
                <div class="role-option active" data-role="client" onclick="selectRole('client')">
                    <i class="fas fa-user"></i>
                    <span>Client</span>
                    <small>I need surveying services</small>
                </div>
                <div class="role-option" data-role="engineer" onclick="selectRole('engineer')">
                    <i class="fas fa-hard-hat"></i>
                    <span>Engineer</span>
                    <small>I provide surveying services</small>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" class="auth-form" id="registerForm">
                <input type="hidden" name="role" id="roleInput" value="client">

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="name" placeholder="Your full name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" name="phone" placeholder="+63 9XX XXX XXXX"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" placeholder="your@email.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye" id="confirm_password-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-map-marker-alt input-icon"></i>
                        <input type="text" name="address" placeholder="Your address"
                               value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                    </div>
                </div>

                <!-- Engineer-specific fields -->
                <div id="engineerFields" style="display:none;">
                    <div class="form-divider"><span>Engineer Details</span></div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>PRC License Number</label>
                            <div class="input-wrapper">
                                <i class="fas fa-id-card input-icon"></i>
                                <input type="text" name="license_number" placeholder="GE-XXXX-XXXXXX">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Years of Experience</label>
                            <div class="input-wrapper">
                                <i class="fas fa-briefcase input-icon"></i>
                                <input type="number" name="experience_years" placeholder="0" min="0" max="50">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <div class="input-wrapper">
                            <i class="fas fa-star input-icon"></i>
                            <input type="text" name="specialization" placeholder="e.g., Boundary & Topographic Survey">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Skills</label>
                        <div class="input-wrapper">
                            <i class="fas fa-tools input-icon"></i>
                            <input type="text" name="skills" placeholder="GPS, AutoCAD, Total Station...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Hourly Rate (₱)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-peso-sign input-icon"></i>
                            <input type="number" name="hourly_rate" placeholder="1500" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Profile Photo</label>
                    <div class="photo-upload" id="photoUpload">
                        <input type="file" name="profile_photo" id="photoInput" accept="image/*" onchange="previewPhoto(this)">
                        <div class="photo-preview" id="photoPreview">
                            <i class="fas fa-camera"></i>
                            <span>Click to upload photo</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label terms-check">
                        <input type="checkbox" required>
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn-auth-submit" id="registerBtn">
                    <span>Create Account</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<script>
function selectRole(role) {
    document.querySelectorAll('.role-option').forEach(el => el.classList.remove('active'));
    document.querySelector('[data-role="' + role + '"]').classList.add('active');
    document.getElementById('roleInput').value = role;
    document.getElementById('engineerFields').style.display = role === 'engineer' ? 'block' : 'none';
}

function togglePassword(id) {
    const input = document.getElementById(id);
    const eye = document.getElementById(id + '-eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').innerHTML = 
                '<img src="' + e.target.result + '" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('registerForm').addEventListener('submit', function() {
    const btn = document.getElementById('registerBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
    btn.disabled = true;
});
</script>
</body>
</html>
