<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$page_title = 'ড্যাশবোর্ড';

// Statistics
$stats = [];

// Total Students
$stmt = $conn->query("SELECT COUNT(*) FROM students");
$stats['students'] = $stmt->fetchColumn();

// Total Teachers
$stmt = $conn->query("SELECT COUNT(*) FROM teachers");
$stats['teachers'] = $stmt->fetchColumn();

// Total Staff
$stmt = $conn->query("SELECT COUNT(*) FROM staff");
$stats['staff'] = $stmt->fetchColumn();

// Today's Attendance
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE date = :date");
$stmt->execute(['date' => $today]);
$stats['present'] = $stmt->fetchColumn();

// Check if today is off day
$stmt = $conn->prepare("SELECT id FROM off_days WHERE date = :date");
$stmt->execute(['date' => $today]);
$is_off_day = $stmt->fetch() ? true : false;

// Total Attendance (all time)
$stmt = $conn->query("SELECT COUNT(*) FROM attendance");
$stats['total_attendance'] = $stmt->fetchColumn();

// Absent today
$stats['absent'] = $stats['students'] - $stats['present'];

// Today's date
$stats['today'] = date('d F Y');

// User info
$user_name = $_SESSION['full_name'] ?? $_SESSION['user'] ?? 'Admin';
?>
<?php include '../includes/header.php'; ?>

<style>
/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: white;
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.stat-card .stat-icon {
    font-size: 28px;
    margin-bottom: 6px;
}
.stat-card .stat-number {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
}
.stat-card .stat-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}
.stat-card.border-indigo { border-left: 4px solid #4f46e5; }
.stat-card.border-green { border-left: 4px solid #22c55e; }
.stat-card.border-purple { border-left: 4px solid #8b5cf6; }
.stat-card.border-yellow { border-left: 4px solid #eab308; }
.stat-card.border-red { border-left: 4px solid #ef4444; }

/* Welcome Banner - শুধু স্বাগতম বার্তা */
.welcome-banner {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
    padding: 18px 28px;
    border-radius: 16px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    box-shadow: 0 4px 20px rgba(55, 48, 163, 0.3);
}
.welcome-banner .welcome-text {
    color: #fcd34d;
    font-size: 20px;
    font-weight: 700;
}
.welcome-banner .welcome-text span {
    color: white;
}
.welcome-banner .live-time {
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    background: rgba(255,255,255,0.1);
    padding: 6px 20px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.1);
    font-family: 'Courier New', monospace;
}

/* Menu Grid */
.menu-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.menu-item {
    background: white;
    border-radius: 16px;
    padding: 24px 16px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    text-decoration: none;
    color: #0f172a;
    transition: all 0.3s ease;
}
.menu-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}
.menu-item .icon {
    font-size: 36px;
    margin-bottom: 10px;
    display: block;
}
.menu-item h4 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}
.menu-item p {
    font-size: 13px;
    color: #94a3b8;
    margin: 4px 0 0 0;
}

.off-day-notice {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 14px 20px;
    border-radius: 12px;
    color: #92400e;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}

@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .menu-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .menu-grid {
        grid-template-columns: 1fr 1fr;
    }
    .stat-card .stat-number {
        font-size: 20px;
    }
    .welcome-banner {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<!-- ===== শুধুমাত্র ওয়েলকাম ব্যানার (হেডার নেই) ===== -->
<div class="welcome-banner">
    <div class="welcome-text">
        👋 স্বাগতম, <span><?php echo htmlspecialchars($user_name); ?></span>
    </div>
    <div class="live-time" id="liveClock"><?php echo date('h:i:s A'); ?></div>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card border-indigo">
        <div class="stat-icon">👨‍🎓</div>
        <div class="stat-number"><?php echo number_format($stats['students']); ?></div>
        <div class="stat-label">মোট ছাত্র</div>
    </div>
    <div class="stat-card border-green">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-number"><?php echo number_format($stats['teachers']); ?></div>
        <div class="stat-label">মোট শিক্ষক</div>
    </div>
    <div class="stat-card border-purple">
        <div class="stat-icon">👤</div>
        <div class="stat-number"><?php echo number_format($stats['staff']); ?></div>
        <div class="stat-label">মোট কর্মচারী</div>
    </div>
    <div class="stat-card border-yellow">
        <div class="stat-icon">✅</div>
        <div class="stat-number"><?php echo number_format($stats['present']); ?></div>
        <div class="stat-label">আজকে উপস্থিত</div>
    </div>
    <div class="stat-card border-red">
        <div class="stat-icon">❌</div>
        <div class="stat-number"><?php echo number_format($stats['absent']); ?></div>
        <div class="stat-label">আজকে অনুপস্থিত</div>
    </div>
</div>

<!-- Menu -->
<div class="menu-grid">
    <a href="../admin/attendance/index.php" class="menu-item">
        <span class="icon">📋</span>
        <h4>উপস্থিতি</h4>
        <p>NFC স্ক্যানার</p>
    </a>
    <a href="../admin/attendance/report.php" class="menu-item">
        <span class="icon">📊</span>
        <h4>ছাত্র রিপোর্ট</h4>
        <p>উপস্থিতি রিপোর্ট</p>
    </a>
    <a href="../admin/students/list.php" class="menu-item">
        <span class="icon">👨‍🎓</span>
        <h4>ছাত্র তালিকা</h4>
        <p>সকল ছাত্র</p>
    </a>
    <a href="../admin/students/add.php" class="menu-item">
        <span class="icon">➕</span>
        <h4>ছাত্র যোগ</h4>
        <p>নতুন ছাত্র</p>
    </a>
    <a href="../admin/teachers/list.php" class="menu-item">
        <span class="icon">👨‍🏫</span>
        <h4>শিক্ষক তালিকা</h4>
        <p>সকল শিক্ষক</p>
    </a>
    <a href="../admin/teachers/add.php" class="menu-item">
        <span class="icon">➕</span>
        <h4>শিক্ষক যোগ</h4>
        <p>নতুন শিক্ষক</p>
    </a>
    <a href="../admin/attendance/teacher_report.php" class="menu-item">
        <span class="icon">📊</span>
        <h4>শিক্ষক রিপোর্ট</h4>
        <p>উপস্থিতি রিপোর্ট</p>
    </a>
    <a href="../admin/settings/index.php" class="menu-item">
        <span class="icon">⚙️</span>
        <h4>সেটিংস</h4>
        <p>ব্যাকআপ & কনফিগ</p>
    </a>
</div>

<!-- Off Day Notice -->
<?php if ($is_off_day): ?>
<div class="off-day-notice">
    <i class="fas fa-calendar-times fa-lg"></i>
    <strong>আজ অফ ডে</strong> — <?php echo date('d F Y'); ?> তারিখে কোনো ক্লাস বা উপস্থিতি নেওয়া যাবে না।
</div>
<?php endif; ?>

<script>
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    const ampm = h >= 12 ? 'PM' : 'AM';
    const dh = h % 12 || 12;
    document.getElementById('liveClock').textContent = `${dh}:${m}:${s} ${ampm}`;
}
setInterval(updateClock, 1000);
updateClock();
</script>

<?php include '../includes/footer.php'; ?>