<?php
/**
 * Migration: Add updated_at to schedules table
 * Run once: http://localhost/kapoy/add_updated_at.php
 * Delete after running.
 */
require_once 'config/database.php';
$db = (new Database())->getConnection();

$results = [];

// Add updated_at to schedules if missing
try {
    $db->exec("ALTER TABLE schedules ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    $results[] = '✅ Added updated_at to schedules';
} catch (PDOException $e) {
    $results[] = '⚠️ schedules.updated_at: ' . $e->getMessage();
}

// Add updated_at to payments if missing
try {
    $db->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    $results[] = '✅ payments.updated_at OK';
} catch (PDOException $e) {
    $results[] = '⚠️ payments.updated_at: ' . $e->getMessage();
}

// Ensure appointments.updated_at exists
try {
    $db->exec("ALTER TABLE appointments ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    $results[] = '✅ appointments.updated_at OK';
} catch (PDOException $e) {
    $results[] = '⚠️ appointments.updated_at: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head><title>Migration</title>
<style>body{font-family:Arial,sans-serif;max-width:600px;margin:60px auto;padding:20px}
.ok{background:#d1fae5;color:#065f46;padding:16px;border-radius:10px;margin:8px 0}
a.btn{display:inline-block;margin-top:20px;padding:12px 28px;background:#1a3c5e;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold}
</style></head>
<body>
<h2>🔧 Database Migration</h2>
<?php foreach ($results as $r): ?>
<div class="ok"><?= htmlspecialchars($r) ?></div>
<?php endforeach; ?>
<a href="admin/dashboard.php" class="btn">Go to Admin Dashboard →</a>
<p style="margin-top:16px;color:#991b1b;font-size:13px">⚠️ <strong>Delete this file</strong> after running.</p>
</body></html>
