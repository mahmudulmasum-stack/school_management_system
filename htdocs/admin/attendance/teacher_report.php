<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'শিক্ষক উপস্থিতি রিপোর্ট';

$date = $_GET['date'] ?? date('Y-m-d');
$subject = $_GET['subject'] ?? '';
$designation = $_GET['designation'] ?? '';

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$sql = "SELECT t.id, t.name, t.number, t.subject, t.designation, t.photo_path,
        CASE WHEN ta.id IS NOT NULL THEN 'present' ELSE 'absent' END as status,
        ta.time_in as entry_time, ta.time_out as exit_time
        FROM teachers t
        LEFT JOIN teacher_attendance ta ON t.id = ta.teacher_id AND ta.date = :date
        WHERE t.status = 'active'";
$params = [':date' => $date];

if (!empty($subject)) {
    $sql .= " AND t.subject = :subject";
    $params[':subject'] = $subject;
}
if (!empty($designation)) {
    $sql .= " AND t.designation = :designation";
    $params[':designation'] = $designation;
}

$sql .= " ORDER BY t.name";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$attendance = $stmt->fetchAll();

$present_count = count(array_filter($attendance, fn($row) => $row['status'] === 'present'));
$absent_count = count($attendance) - $present_count;
$total_count = count($attendance);

// Get unique subjects and designations for filter
$subjects = $conn->query("SELECT DISTINCT subject FROM teachers WHERE subject IS NOT NULL AND subject != ''")->fetchAll(PDO::FETCH_COLUMN);
$designations = $conn->query("SELECT DISTINCT designation FROM teachers WHERE designation IS NOT NULL AND designation != ''")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include '../../includes/header.php'; ?>

<style>
.report-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px 16px;
}

.report-card {
    background: white;
    border-radius: 16px;
    padding: 24px 28px;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    border: 1px solid #e2e8f0;
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.report-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.report-header h2 i {
    color: #22c55e;
}

.report-header .date-label {
    font-size: 14px;
    font-weight: 400;
    color: #64748b;
    margin-left: 8px;
}

.btn-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-custom {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-print {
    background: #22c55e;
    color: white;
}

.btn-print:hover {
    background: #16a34a;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
}

.btn-csv {
    background: #4f46e5;
    color: white;
}

.btn-csv:hover {
    background: #3730a3;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
}

.btn-back-home {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ef4444;
    color: white;
}

.btn-back-home:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
}

.filter-form {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto auto;
    gap: 12px;
    margin-bottom: 20px;
}

.filter-form input,
.filter-form select {
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: #22c55e;
    background: white;
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
}

.filter-form button {
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    background: #22c55e;
    color: white;
    transition: all 0.3s ease;
}

.filter-form button:hover {
    background: #16a34a;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.stat-box {
    padding: 14px 20px;
    border-radius: 12px;
    text-align: center;
}

.stat-box .label {
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 2px;
}

.stat-box .number {
    font-size: 24px;
    font-weight: 700;
}

.stat-box.present {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}
.stat-box.present .label { color: #15803d; }
.stat-box.present .number { color: #16a34a; }

.stat-box.absent {
    background: #fef2f2;
    border: 1px solid #fecaca;
}
.stat-box.absent .label { color: #b91c1c; }
.stat-box.absent .number { color: #dc2626; }

.stat-box.total {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
}
.stat-box.total .label { color: #1d4ed8; }
.stat-box.total .number { color: #2563eb; }

.table-container {
    overflow-x: auto;
}

.table-custom {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.table-custom thead {
    background: #f1f5f9;
}

.table-custom th {
    padding: 10px 14px;
    text-align: left;
    font-weight: 600;
    color: #334155;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.table-custom td {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-custom tr:hover td {
    background: #f8fafc;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.status-badge {
    display: inline-block;
    padding: 3px 14px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
}

.status-present {
    background: #dcfce7;
    color: #166534;
}

.status-absent {
    background: #fee2e2;
    color: #991b1b;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #94a3b8;
}

.empty-state i {
    font-size: 56px;
    display: block;
    margin-bottom: 12px;
    color: #cbd5e1;
}

.back-home-wrapper {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

@media (max-width: 768px) {
    .filter-form {
        grid-template-columns: 1fr 1fr;
    }
    
    .filter-form button {
        grid-column: span 2;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr 1fr;
    }
    
    .report-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-group {
        justify-content: stretch;
    }
    
    .btn-group a {
        flex: 1;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .report-card {
        padding: 16px;
    }
    
    .filter-form {
        grid-template-columns: 1fr;
    }
    
    .filter-form button {
        grid-column: span 1;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .stat-box.total {
        grid-column: span 2;
    }
    
    .back-home-wrapper {
        flex-direction: column;
    }
    
    .btn-back-home {
        justify-content: center;
    }
}
</style>

<div class="report-container">
    <div class="report-card">
        <div class="report-header">
            <h2>
                <i class="fas fa-chalkboard-teacher"></i> শিক্ষক উপস্থিতি রিপোর্ট
                <span class="date-label"><?php echo date('d F Y', strtotime($date)); ?></span>
            </h2>
            <div class="btn-group">
                <a href="print_report.php?date=<?php echo urlencode($date); ?>&type=teacher" target="_blank" class="btn-custom btn-print">
                    <i class="fas fa-print"></i> প্রিন্ট/PDF
                </a>
                <a href="export_teacher_report.php?date=<?php echo urlencode($date); ?>&subject=<?php echo urlencode($subject); ?>&designation=<?php echo urlencode($designation); ?>" class="btn-custom btn-csv">
                    <i class="fas fa-download"></i> CSV
                </a>
                <a href="../home.php" class="btn-custom btn-back-home">
                    <i class="fas fa-home"></i> ড্যাশবোর্ড
                </a>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" class="filter-form">
            <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
            <select name="subject">
                <option value="">সব বিষয়</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $subject == $s ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="designation">
                <option value="">সব পদবী</option>
                <?php foreach ($designations as $d): ?>
                    <option value="<?php echo htmlspecialchars($d); ?>" <?php echo $designation == $d ? 'selected' : ''; ?>><?php echo htmlspecialchars($d); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">দেখুন</button>
            <a href="teacher_report.php" class="btn-custom" style="background:#e2e8f0;color:#334155;text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:10px;font-weight:600;font-size:14px;">রিসেট</a>
        </form>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-box present">
                <div class="label">✅ উপস্থিত</div>
                <div class="number"><?php echo $present_count; ?></div>
            </div>
            <div class="stat-box absent">
                <div class="label">❌ অনুপস্থিত</div>
                <div class="number"><?php echo $absent_count; ?></div>
            </div>
            <div class="stat-box total">
                <div class="label">📊 মোট</div>
                <div class="number"><?php echo $total_count; ?></div>
            </div>
        </div>

        <!-- Table -->
        <?php if ($total_count > 0): ?>
        <div class="table-container">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ছবি</th>
                        <th>নাম</th>
                        <th>মোবাইল</th>
                        <th>পদবী</th>
                        <th>বিষয়</th>
                        <th>এন্ট্রি</th>
                        <th>এক্সিট</th>
                        <th>স্থিতি</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $index => $row): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <img src="../../<?php echo htmlspecialchars($row['photo_path'] ?? 'uploads/default.jpg'); ?>" 
                                 class="avatar" 
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['name']); ?>&background=22c55e&color=fff&size=36'">
                        </td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['number']); ?></td>
                        <td><?php echo htmlspecialchars($row['designation'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['subject'] ?: '—'); ?></td>
                        <td><?php echo $row['entry_time'] ? date('h:i:s A', strtotime($row['entry_time'])) : '—'; ?></td>
                        <td><?php echo $row['exit_time'] ? date('h:i:s A', strtotime($row['exit_time'])) : '—'; ?></td>
                        <td>
                            <?php if ($row['status'] === 'present'): ?>
                                <span class="status-badge status-present">✅ উপস্থিত</span>
                            <?php else: ?>
                                <span class="status-badge status-absent">❌ অনুপস্থিত</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>কোনো ডাটা পাওয়া যায়নি</p>
        </div>
        <?php endif; ?>

        <!-- বেক টু হোম -->
        <div class="back-home-wrapper">
            <a href="../home.php" class="btn-custom" style="background:#4f46e5;color:white;">
                <i class="fas fa-home"></i> ড্যাশবোর্ডে ফিরে যান
            </a>
            <a href="index.php" class="btn-custom" style="background:#eab308;color:white;">
                <i class="fas fa-qrcode"></i> স্ক্যানারে ফিরে যান
            </a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>