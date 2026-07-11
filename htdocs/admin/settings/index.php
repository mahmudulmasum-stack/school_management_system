<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'সেটিংস';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, full_name, email FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($current)) $errors[] = 'বর্তমান পাসওয়ার্ড দিন';
        if (empty($new)) $errors[] = 'নতুন পাসওয়ার্ড দিন';
        if (strlen($new) < 6) $errors[] = 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে';
        if ($new !== $confirm) $errors[] = 'পাসওয়ার্ড মিলছে না';
        
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $user_data = $stmt->fetch();
            
            if (password_verify($current, $user_data['password'])) {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute(['password' => $hashed, 'id' => $user_id]);
                $success = 'পাসওয়ার্ড পরিবর্তন করা হয়েছে!';
            } else {
                $errors[] = 'বর্তমান পাসওয়ার্ড ভুল';
            }
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>

<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px 16px;
}

.settings-card {
    background: white;
    border-radius: 16px;
    padding: 28px 32px;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    border: 1px solid #e2e8f0;
}

.settings-card h2 {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.settings-card h2 i {
    color: #64748b;
}

.profile-box {
    background: #f1f5f9;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.profile-box .avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #4f46e5;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
}

.profile-box .info h3 {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.profile-box .info p {
    color: #64748b;
    font-size: 14px;
    margin: 2px 0 0 0;
}

.alert-custom {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #f0fdf4;
    border-left: 4px solid #22c55e;
    color: #166534;
}

.alert-danger {
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.divider {
    border-top: 1px solid #e2e8f0;
    padding-top: 20px;
    margin-top: 20px;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 4px;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.form-group input:focus {
    border-color: #4f46e5;
    background: white;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

.btn-primary {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    background: #4f46e5;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary:hover {
    background: #3730a3;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
}

.btn-backup {
    display: inline-block;
    padding: 14px 28px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    background: #22c55e;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-backup:hover {
    background: #16a34a;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
}

.backup-section {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 12px;
}

@media (max-width: 640px) {
    .settings-card {
        padding: 20px 16px;
    }
    
    .profile-box {
        flex-direction: column;
        text-align: center;
    }
    
    .backup-section {
        flex-direction: column;
    }
    
    .backup-section a {
        text-align: center;
    }
}
</style>

<div class="settings-container">
    <div class="settings-card">
        <h2>
            <i class="fas fa-cog"></i> সেটিংস
        </h2>

        <?php if (!empty($errors)): ?>
            <div class="alert-custom alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo $e; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-custom alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Profile -->
        <div class="profile-box">
            <div class="avatar">
                <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="info">
                <h3><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></h3>
                <p>@<?php echo htmlspecialchars($user['username']); ?> <?php echo $user['email'] ? '| ' . htmlspecialchars($user['email']) : ''; ?></p>
            </div>
        </div>

        <!-- Change Password -->
        <div class="divider">
            <div class="section-title">
                <i class="fas fa-key text-yellow-600"></i> পাসওয়ার্ড পরিবর্তন
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label>বর্তমান পাসওয়ার্ড</label>
                    <input type="password" name="current_password" required placeholder="বর্তমান পাসওয়ার্ড দিন">
                </div>
                
                <div class="form-group">
                    <label>নতুন পাসওয়ার্ড (৬+ অক্ষর)</label>
                    <input type="password" name="new_password" required placeholder="নতুন পাসওয়ার্ড দিন">
                </div>
                
                <div class="form-group">
                    <label>নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                    <input type="password" name="confirm_password" required placeholder="আবার পাসওয়ার্ড দিন">
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-key"></i> পাসওয়ার্ড পরিবর্তন করুন
                </button>
            </form>
        </div>

        <!-- Backup -->
        <div class="divider">
            <div class="section-title">
                <i class="fas fa-database text-green-600"></i> ডেটাবেস ব্যাকআপ
            </div>
            <div class="backup-section">
                <a href="backup.php" class="btn-backup">
                    <i class="fas fa-download"></i> ব্যাকআপ ডাউনলোড
                </a>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-info-circle"></i> সর্বশেষ ১০টি ব্যাকআপ সংরক্ষিত থাকে
            </p>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>