<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'উপস্থিতি ব্যবস্থাপনা';
$today = date('Y-m-d');
$day_of_week = date('w');

// Check if today is off day
$is_off_day = false;
$off_day_reason = '';

if ($day_of_week == 5) {
    $is_off_day = true;
    $off_day_reason = 'শুক্রবার (সাপ্তাহিক ছুটি)';
}

$stmt = $conn->prepare("SELECT reason FROM off_days WHERE date = :date");
$stmt->execute(['date' => $today]);
$off_day_db = $stmt->fetch();
if ($off_day_db) {
    $is_off_day = true;
    $off_day_reason = $off_day_db['reason'];
}

$holidays = [
    '2026-01-01' => 'নববর্ষ (ইংরেজি)',
    '2026-02-21' => 'আন্তর্জাতিক মাতৃভাষা দিবস',
    '2026-03-26' => 'স্বাধীনতা দিবস',
    '2026-04-14' => 'বাংলা নববর্ষ',
    '2026-05-01' => 'মে দিবস',
    '2026-08-15' => 'জাতীয় শোক দিবস',
    '2026-12-16' => 'বিজয় দিবস',
    '2026-12-25' => 'বড়দিন',
];

if (isset($holidays[$today])) {
    $is_off_day = true;
    $off_day_reason = $holidays[$today];
}

// Get today's stats
$stmt = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE date = :date");
$stmt->execute(['date' => $today]);
$present_count = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE status = 'active'");
$stmt->execute();
$total_students = $stmt->fetchColumn();

$absent_count = $total_students - $present_count;
?>
<?php include '../../includes/header.php'; ?>

<style>
/* Modern Dashboard Styles */
:root {
    --primary: #4f46e5;
    --primary-light: #818cf8;
    --primary-dark: #3730a3;
    --success: #22c55e;
    --danger: #ef4444;
    --warning: #f59e0b;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-300: #cbd5e1;
    --gray-500: #64748b;
    --gray-700: #334155;
    --gray-900: #0f172a;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    --radius: 16px;
    --radius-sm: 10px;
}

.dashboard-container {
    max-width: 1024px;
    margin: 0 auto;
    padding: 20px 16px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border-radius: var(--radius);
    padding: 20px 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-100);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stat-card.present::before { background: var(--success); }
.stat-card.absent::before { background: var(--danger); }
.stat-card.total::before { background: var(--primary); }

.stat-card .stat-number {
    font-size: 28px;
    font-weight: 800;
    color: var(--gray-900);
    line-height: 1.2;
}

.stat-card .stat-label {
    font-size: 13px;
    color: var(--gray-500);
    font-weight: 500;
    margin-top: 2px;
}

.stat-card .stat-icon {
    position: absolute;
    top: 16px;
    right: 16px;
    font-size: 20px;
    opacity: 0.15;
}

.stat-card.present .stat-number { color: #16a34a; }
.stat-card.absent .stat-number { color: #dc2626; }
.stat-card.total .stat-number { color: var(--primary); }

/* Scanner Card */
.scanner-card {
    background: white;
    border-radius: var(--radius);
    padding: 24px 28px;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-100);
    margin-bottom: 20px;
}

.scanner-card .card-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.scanner-card .card-title i {
    color: var(--primary);
}

.scanner-input-group {
    display: flex;
    gap: 12px;
}

.scanner-input-group input {
    flex: 1;
    padding: 14px 18px;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-size: 16px;
    outline: none;
    transition: all 0.3s ease;
    background: var(--gray-50);
}

.scanner-input-group input:focus {
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

.scanner-input-group input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.scanner-input-group button {
    padding: 14px 28px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 16px;
    border: none;
    cursor: pointer;
    background: var(--primary);
    color: white;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.scanner-input-group button:hover:not(:disabled) {
    background: var(--primary-dark);
    transform: scale(1.02);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
}

.scanner-input-group button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Display Card */
.display-card {
    background: var(--gray-50);
    border-radius: var(--radius-sm);
    padding: 24px;
    min-height: 160px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 2px dashed var(--gray-300);
    transition: all 0.3s ease;
    margin-top: 16px;
}

.display-card .empty-state {
    color: var(--gray-500);
    text-align: center;
}

.display-card .empty-state i {
    font-size: 40px;
    color: var(--gray-300);
    display: block;
    margin-bottom: 8px;
}

.display-card .result-card {
    text-align: center;
    width: 100%;
}

.display-card .result-card img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--success);
    margin-bottom: 8px;
}

.display-card .result-card h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
}

.display-card .result-card p {
    font-size: 14px;
    color: var(--gray-500);
    margin: 4px 0;
}

.status-badge {
    display: inline-block;
    padding: 4px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-top: 6px;
}

.status-entry {
    background: #dcfce7;
    color: #166534;
}

.status-exit {
    background: #fef3c7;
    color: #92400e;
}

.status-absent {
    background: #fee2e2;
    color: #991b1b;
}

/* Off Day Message */
.off-day-message {
    background: #fef3c7;
    border-left: 4px solid var(--warning);
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    margin-bottom: 16px;
    color: #92400e;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Quick Links */
.quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.quick-links a {
    padding: 10px 20px;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: white;
    border: 1px solid var(--gray-200);
    color: var(--gray-700);
}

.quick-links a:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.quick-links a.student-report { border-color: #818cf8; color: #4f46e5; }
.quick-links a.teacher-report { border-color: #34d399; color: #059669; }
.quick-links a.student-attendance { border-color: #a78bfa; color: #7c3aed; }
.quick-links a.teacher-attendance { border-color: #f472b6; color: #db2777; }
.quick-links a.off-days { border-color: #f87171; color: #dc2626; }
.quick-links a.back-home { border-color: #4f46e5; color: #4f46e5; }

/* Off Day Section */
.off-day-section {
    background: white;
    border-radius: var(--radius);
    padding: 20px 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-100);
}

.off-day-section .section-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 4px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.off-day-section .section-title i {
    color: var(--danger);
}

.off-day-section .sub-text {
    font-size: 12px;
    color: var(--gray-500);
    margin: 0 0 12px 0;
}

.off-day-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 12px;
}

.off-day-row input {
    padding: 10px 14px;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
    background: var(--gray-50);
}

.off-day-row input:focus {
    border-color: var(--danger);
    background: white;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.off-day-row button {
    padding: 10px 24px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    background: var(--danger);
    color: white;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.off-day-row button:hover {
    background: #dc2626;
    transform: scale(1.02);
}

/* Message Alerts */
.alert-custom {
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #f0fdf4;
    border-left: 4px solid var(--success);
    color: #166534;
}

.alert-danger {
    background: #fef2f2;
    border-left: 4px solid var(--danger);
    color: #991b1b;
}

.alert-hidden {
    display: none;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }
    
    .stat-card {
        padding: 14px 16px;
    }
    
    .stat-card .stat-number {
        font-size: 22px;
    }
    
    .scanner-input-group {
        flex-direction: column;
    }
    
    .scanner-input-group button {
        width: 100%;
        justify-content: center;
    }
    
    .off-day-row {
        grid-template-columns: 1fr;
    }
    
    .off-day-row button {
        width: 100%;
        justify-content: center;
    }
    
    .quick-links a {
        flex: 1;
        justify-content: center;
        padding: 8px 14px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .stat-card:last-child {
        grid-column: span 2;
    }
    
    .scanner-card {
        padding: 16px;
    }
    
    .off-day-section {
        padding: 16px;
    }
}
</style>

<div class="dashboard-container">
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card present">
            <div class="stat-icon">✅</div>
            <div class="stat-number"><?php echo $present_count; ?></div>
            <div class="stat-label">উপস্থিত</div>
        </div>
        <div class="stat-card absent">
            <div class="stat-icon">❌</div>
            <div class="stat-number"><?php echo $absent_count; ?></div>
            <div class="stat-label">অনুপস্থিত</div>
        </div>
        <div class="stat-card total">
            <div class="stat-icon">📊</div>
            <div class="stat-number"><?php echo $total_students; ?></div>
            <div class="stat-label">মোট ছাত্র</div>
        </div>
    </div>

    <!-- Scanner Card -->
    <div class="scanner-card">
        <div class="card-title">
            <i class="fas fa-qrcode"></i> NFC অ্যাটেনডেন্স স্ক্যানার
        </div>
        
        <?php if ($is_off_day): ?>
        <div class="off-day-message">
            <i class="fas fa-calendar-times"></i>
            <strong>আজ অফ ডে</strong> — কারণ: <?php echo htmlspecialchars($off_day_reason); ?>
        </div>
        <?php endif; ?>

        <div class="scanner-input-group">
            <input type="text" id="card_id" 
                   <?php if ($is_off_day) echo 'disabled'; ?>
                   placeholder="NFC কার্ড স্ক্যান করুন বা আইডি লিখুন" autofocus>
            <button id="submitBtn" <?php if ($is_off_day) echo 'disabled'; ?>>
                <i class="fas fa-check"></i> জমা
            </button>
        </div>

        <div id="message" class="alert-custom alert-hidden"></div>

        <div class="display-card" id="displayCard">
            <div class="empty-state">
                <i class="fas fa-id-card"></i>
                <p>কার্ড স্ক্যান করার জন্য অপেক্ষা করুন...</p>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <a href="report.php" class="student-report">
            <i class="fas fa-file-alt"></i> ছাত্র রিপোর্ট
        </a>
        <a href="teacher_report.php" class="teacher-report">
            <i class="fas fa-chalkboard-teacher"></i> শিক্ষক রিপোর্ট
        </a>
        <a href="student_attendance.php" class="student-attendance">
            <i class="fas fa-user-graduate"></i> ছাত্র উপস্থিতি
        </a>
        <a href="teacher_attendance.php" class="teacher-attendance">
            <i class="fas fa-user-tie"></i> শিক্ষক উপস্থিতি
        </a>
        <a href="off_days.php" class="off-days">
            <i class="fas fa-calendar-times"></i> অফ ডে তালিকা
        </a>
        <a href="../home.php" class="back-home">
            <i class="fas fa-home"></i> ড্যাশবোর্ড
        </a>
    </div>

    <!-- Off Days Section -->
    <div class="off-day-section">
        <div class="section-title">
            <i class="fas fa-calendar-plus"></i> অফ ডে মার্ক করুন
        </div>
        <p class="sub-text">* শুক্রবার ও সরকারি ছুটির দিন স্বয়ংক্রিয়</p>
        <form id="offDayForm" class="off-day-row">
            <input type="date" id="from_date" required>
            <input type="date" id="to_date" required>
            <button type="submit"><i class="fas fa-calendar-minus"></i> মার্ক করুন</button>
        </form>
        <div id="offDayMessage" class="alert-custom alert-hidden"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitBtn');
    const cardInput = document.getElementById('card_id');
    const message = document.getElementById('message');
    const displayCard = document.getElementById('displayCard');
    const offDayForm = document.getElementById('offDayForm');
    const offDayMessage = document.getElementById('offDayMessage');

    // Submit attendance
    if (submitBtn && cardInput) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const cardId = cardInput.value.trim();
            
            if (!cardId) {
                message.className = 'alert-custom alert-danger';
                message.innerHTML = '<i class="fas fa-exclamation-circle"></i> কার্ড আইডি দিন';
                message.classList.remove('alert-hidden');
                return;
            }
            
            message.className = 'alert-hidden';
            displayCard.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-gray-500">প্রক্রিয়াকরণ...</p></div>';
            
            fetch('submit_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ card_id: cardId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let statusClass = '';
                    let statusText = '';
                    
                    if (data.action === 'entry') {
                        statusClass = 'status-entry';
                        statusText = '✅ উপস্থিত (এন্ট্রি)';
                    } else if (data.action === 'exit') {
                        statusClass = 'status-exit';
                        statusText = '🚪 বেরিয়ে গেছেন (এক্সিট)';
                    }
                    
                    message.className = 'alert-custom alert-success';
                    message.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    message.classList.remove('alert-hidden');
                    
                    let timeHtml = '';
                    if (data.data.time_in) {
                        timeHtml += `<p>⏰ এন্ট্রি: ${data.data.time_in}</p>`;
                    }
                    if (data.data.time_out) {
                        timeHtml += `<p>🚪 এক্সিট: ${data.data.time_out}</p>`;
                    }
                    
                    const photoPath = data.data.photo_path ? `../../${data.data.photo_path}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(data.data.name) + '&background=4f46e5&color=fff&size=128';
                    
                    if (data.type === 'student') {
                        displayCard.innerHTML = `
                            <div class="result-card">
                                <img src="${photoPath}" alt="Photo" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(data.data.name)}&background=4f46e5&color=fff&size=128'">
                                <h3>${data.data.name}</h3>
                                <p>রোল: ${data.data.roll} | শ্রেণী: ${data.data.class}</p>
                                ${timeHtml}
                                <span class="status-badge ${statusClass}">${statusText}</span>
                            </div>
                        `;
                    } else if (data.type === 'teacher') {
                        displayCard.innerHTML = `
                            <div class="result-card">
                                <img src="${photoPath}" alt="Photo" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(data.data.name)}&background=4f46e5&color=fff&size=128'">
                                <h3>${data.data.name}</h3>
                                <p>${data.data.number}</p>
                                ${timeHtml}
                                <span class="status-badge ${statusClass}">${statusText}</span>
                            </div>
                        `;
                    }
                } else {
                    message.className = 'alert-custom alert-danger';
                    message.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                    message.classList.remove('alert-hidden');
                    displayCard.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-id-card"></i>
                            <p>কার্ড স্ক্যান করার জন্য অপেক্ষা করুন...</p>
                        </div>
                    `;
                }
                cardInput.value = '';
                cardInput.focus();
            })
            .catch(error => {
                console.error('Error:', error);
                message.className = 'alert-custom alert-danger';
                message.innerHTML = '<i class="fas fa-exclamation-circle"></i> সার্ভার ত্রুটি হয়েছে';
                message.classList.remove('alert-hidden');
                cardInput.value = '';
                cardInput.focus();
            });
        });

        // Enter key support
        cardInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitBtn.click();
            }
        });

        cardInput.focus();
    }

    // Off Day Form
    if (offDayForm) {
        offDayForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;
            
            if (!fromDate || !toDate) {
                offDayMessage.className = 'alert-custom alert-danger';
                offDayMessage.innerHTML = 'তারিখ নির্বাচন করুন';
                offDayMessage.classList.remove('alert-hidden');
                return;
            }
            if (new Date(toDate) < new Date(fromDate)) {
                offDayMessage.className = 'alert-custom alert-danger';
                offDayMessage.innerHTML = 'শেষ তারিখ শুরু তারিখের আগে হতে পারে না';
                offDayMessage.classList.remove('alert-hidden');
                return;
            }
            
            fetch('mark_off_day.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ from_date: fromDate, to_date: toDate })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    offDayMessage.className = 'alert-custom alert-success';
                    offDayMessage.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    offDayMessage.classList.remove('alert-hidden');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    offDayMessage.className = 'alert-custom alert-danger';
                    offDayMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                    offDayMessage.classList.remove('alert-hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                offDayMessage.className = 'alert-custom alert-danger';
                offDayMessage.innerHTML = 'সার্ভার ত্রুটি হয়েছে';
                offDayMessage.classList.remove('alert-hidden');
            });
        });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>