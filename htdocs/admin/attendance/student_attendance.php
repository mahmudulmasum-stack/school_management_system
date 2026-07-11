<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$page_title = 'ছাত্র উপস্থিতি';

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

$student = null;
$attendance_data = [];

if ($student_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = :id");
    $stmt->execute(['id' => $student_id]);
    $student = $stmt->fetch();
    
    if ($student) {
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $stmt = $conn->prepare("SELECT date, time_in, time_out, status FROM attendance WHERE student_id = :student_id AND date BETWEEN :start AND :end ORDER BY date");
        $stmt->execute(['student_id' => $student_id, 'start' => $start_date, 'end' => $end_date]);
        $attendance_data = $stmt->fetchAll();
    }
}
?>
<?php include '../../includes/header.php'; ?>

<style>
.student-att-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px 16px;
}

.att-card {
    background: white;
    border-radius: 16px;
    padding: 24px 28px;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    border: 1px solid #e2e8f0;
}

.att-card h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.att-card h2 i {
    color: #4f46e5;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
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
    flex: 1;
    min-width: 140px;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: #4f46e5;
    background: white;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

.filter-form button {
    padding: 10px 28px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    background: #4f46e5;
    color: white;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.filter-form button:hover {
    background: #3730a3;
}

.student-profile {
    background: #f1f5f9;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.student-profile img {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
}

.student-profile h3 {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.student-profile p {
    color: #64748b;
    font-size: 14px;
    margin: 2px 0 0 0;
}

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
}

.table-custom td {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-custom tr:hover td {
    background: #f8fafc;
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

.alert-danger {
    padding: 12px 16px;
    border-radius: 10px;
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
    margin-bottom: 16px;
}

.btn-back-home {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    background: #4f46e5;
    color: white;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-back-home:hover {
    background: #3730a3;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
}

.back-home-wrapper {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

@media (max-width: 640px) {
    .att-card {
        padding: 16px;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .filter-form input,
    .filter-form select,
    .filter-form button {
        width: 100%;
    }
    
    .student-profile {
        flex-direction: column;
        text-align: center;
    }
    
    .table-custom th,
    .table-custom td {
        padding: 8px 10px;
        font-size: 13px;
    }
    
    .back-home-wrapper {
        flex-direction: column;
    }
    
    .btn-back-home {
        justify-content: center;
    }
}
</style>

<div class="student-att-container">
    <div class="att-card">
        <h2>
            <i class="fas fa-user-graduate"></i> ছাত্র উপস্থিতি
        </h2>

        <form method="GET" class="filter-form">
            <input type="text" name="student_id" placeholder="ছাত্র আইডি দিন" 
                   value="<?php echo htmlspecialchars($student_id); ?>">
            <select name="month">
                <?php for($m=1; $m<=12; $m++): ?>
                    <option value="<?php echo str_pad($m,2,'0',STR_PAD_LEFT); ?>" 
                            <?php echo $month == str_pad($m,2,'0',STR_PAD_LEFT) ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="year">
                <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">দেখুন</button>
        </form>

        <?php if ($student): ?>
            <div class="student-profile">
                <img src="../../<?php echo htmlspecialchars($student['photo_path'] ?? 'uploads/default.jpg'); ?>" 
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($student['name']); ?>&background=4f46e5&color=fff&size=56'">
                <div>
                    <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                    <p>রোল: <?php echo htmlspecialchars($student['roll']); ?> | শ্রেণী: <?php echo htmlspecialchars($student['class']); ?></p>
                </div>
            </div>

            <div class="table-container">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>তারিখ</th>
                            <th>এন্ট্রি</th>
                            <th>এক্সিট</th>
                            <th>স্থিতি</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_data as $row): ?>
                        <tr>
                            <td><?php echo date('d F Y', strtotime($row['date'])); ?></td>
                            <td><?php echo $row['time_in'] ? date('h:i:s A', strtotime($row['time_in'])) : '—'; ?></td>
                            <td><?php echo $row['time_out'] ? date('h:i:s A', strtotime($row['time_out'])) : '—'; ?></td>
                            <td>
                                <?php if ($row['status'] == 'present'): ?>
                                    <span class="status-badge status-present">✅ উপস্থিত</span>
                                <?php else: ?>
                                    <span class="status-badge status-absent">❌ অনুপস্থিত</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($attendance_data)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">কোনো ডাটা পাওয়া যায়নি</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($student_id): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-circle"></i> ছাত্র পাওয়া যায়নি
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>ছাত্র আইডি দিয়ে উপস্থিতি দেখুন</p>
            </div>
        <?php endif; ?>

        <!-- বেক টু হোম -->
        <div class="back-home-wrapper">
            <a href="../home.php" class="btn-back-home">
                <i class="fas fa-home"></i> ড্যাশবোর্ডে ফিরে যান
            </a>
            <a href="report.php" class="btn-back-home" style="background:#22c55e;">
                <i class="fas fa-file-alt"></i> রিপোর্টে ফিরে যান
            </a>
            <a href="index.php" class="btn-back-home" style="background:#eab308;">
                <i class="fas fa-qrcode"></i> স্ক্যানারে ফিরে যান
            </a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>