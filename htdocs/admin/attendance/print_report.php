<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$date = $_GET['date'] ?? date('Y-m-d');
$type = $_GET['type'] ?? 'student';

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

// Check if off day
$stmt = $conn->prepare("SELECT id FROM off_days WHERE date = :date");
$stmt->execute(['date' => $date]);
$is_off_day = $stmt->fetch() ? true : false;

if ($type === 'student') {
    $sql = "SELECT s.name, s.roll, s.class, s.group_name, s.guardian_phone,
            CASE WHEN a.id IS NOT NULL THEN 'উপস্থিত' ELSE 'অনুপস্থিত' END as status,
            a.time_in as entry_time, a.time_out as exit_time
            FROM students s
            LEFT JOIN attendance a ON s.id = a.student_id AND a.date = :date
            WHERE s.status = 'active'
            ORDER BY s.class, s.roll";
} else {
    $sql = "SELECT t.name, t.number, t.subject, t.designation,
            CASE WHEN ta.id IS NOT NULL THEN 'উপস্থিত' ELSE 'অনুপস্থিত' END as status,
            ta.time_in as entry_time, ta.time_out as exit_time
            FROM teachers t
            LEFT JOIN teacher_attendance ta ON t.id = ta.teacher_id AND ta.date = :date
            WHERE t.status = 'active'
            ORDER BY t.name";
}

$stmt = $conn->prepare($sql);
$stmt->execute(['date' => $date]);
$attendance = $stmt->fetchAll();

$present_count = count(array_filter($attendance, fn($row) => $row['status'] === 'উপস্থিত'));
$total_count = count($attendance);
$absent_count = $total_count - $present_count;
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>উপস্থিতি রিপোর্ট - <?php echo date('d F Y', strtotime($date)); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Hind Siliguri', 'Segoe UI', sans-serif;
        }
        body {
            padding: 24px;
            background: white;
            color: #1e293b;
        }
        .print-container {
            max-width: 1100px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        .header h1 {
            font-size: 24px;
            color: #0f172a;
            margin: 0;
        }
        .header .slogan {
            color: #64748b;
            font-size: 14px;
            margin: 2px 0;
        }
        .header h2 {
            font-size: 20px;
            color: #1e293b;
            margin-top: 8px;
        }
        .header .date-info {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }
        .off-day-notice {
            background: #fef3c7;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border-left: 4px solid #f59e0b;
            color: #92400e;
            font-weight: 500;
        }
        .stats {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stats .stat-box {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        .stats .present-bg {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .stats .absent-bg {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .stats .total-bg {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background: #f1f5f9;
            font-weight: 600;
            color: #334155;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .status-present {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #dcfce7;
            color: #166534;
        }
        .status-absent {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #fee2e2;
            color: #991b1b;
        }
        .time-cell {
            font-size: 13px;
            color: #475569;
        }
        .footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
        }
        .no-print {
            text-align: center;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .no-print button,
        .no-print a {
            padding: 10px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print {
            background: #4f46e5;
            color: white;
        }
        .btn-print:hover {
            background: #3730a3;
            transform: scale(1.02);
        }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-close:hover {
            background: #cbd5e1;
        }
        .btn-back-home {
            background: #22c55e;
            color: white;
        }
        .btn-back-home:hover {
            background: #16a34a;
            transform: scale(1.02);
        }
        @media print {
            .no-print { display: none; }
            body { padding: 12px; }
            .header { border-bottom-color: #cbd5e1; }
            th { background: #e2e8f0; }
        }
        @media (max-width: 640px) {
            body { padding: 12px; }
            table { font-size: 12px; }
            th, td { padding: 6px 8px; }
            .stats { gap: 8px; }
            .stats .stat-box { padding: 6px 12px; font-size: 12px; }
            .header h1 { font-size: 18px; }
            .header h2 { font-size: 16px; }
            .no-print { flex-direction: column; align-items: center; }
            .no-print button,
            .no-print a { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="print-container">
    <div class="header">
        <h1>🏫 <?php echo SITE_NAME; ?></h1>
        <div class="slogan"><?php echo SITE_SLOGAN; ?></div>
        <h2><?php echo $type === 'student' ? 'ছাত্র' : 'শিক্ষক'; ?> উপস্থিতি রিপোর্ট</h2>
        <div class="date-info"><?php echo date('l, d F Y', strtotime($date)); ?></div>
    </div>

    <?php if ($is_off_day): ?>
        <div class="off-day-notice">
            ⚠️ এই দিনটি অফ ডে হিসেবে চিহ্নিত।
        </div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat-box present-bg">✅ উপস্থিত: <strong><?php echo $present_count; ?></strong></div>
        <div class="stat-box absent-bg">❌ অনুপস্থিত: <strong><?php echo $absent_count; ?></strong></div>
        <div class="stat-box total-bg">📊 মোট: <strong><?php echo $total_count; ?></strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <?php if ($type === 'student'): ?>
                    <th>নাম</th>
                    <th>রোল</th>
                    <th>শ্রেণী</th>
                    <th>মোবাইল</th>
                <?php else: ?>
                    <th>নাম</th>
                    <th>মোবাইল</th>
                    <th>পদবী</th>
                    <th>বিষয়</th>
                <?php endif; ?>
                <th>এন্ট্রি</th>
                <th>এক্সিট</th>
                <th>স্থিতি</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendance as $index => $row): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <?php if ($type === 'student'): ?>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['roll']); ?></td>
                    <td><?php echo htmlspecialchars($row['class']); ?></td>
                    <td><?php echo htmlspecialchars($row['guardian_phone'] ?: '—'); ?></td>
                <?php else: ?>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['number']); ?></td>
                    <td><?php echo htmlspecialchars($row['designation'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['subject'] ?: '—'); ?></td>
                <?php endif; ?>
                <td class="time-cell"><?php echo $row['entry_time'] ? date('h:i:s A', strtotime($row['entry_time'])) : '—'; ?></td>
                <td class="time-cell"><?php echo $row['exit_time'] ? date('h:i:s A', strtotime($row['exit_time'])) : '—'; ?></td>
                <td>
                    <?php if ($row['status'] === 'উপস্থিত'): ?>
                        <span class="status-present">✅ উপস্থিত</span>
                    <?php else: ?>
                        <span class="status-absent">❌ অনুপস্থিত</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> — সর্বস্বত্ব সংরক্ষিত</p>
        <p>প্রিন্ট তারিখ: <?php echo date('d F Y, h:i:s A'); ?></p>
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> প্রিন্ট/PDF
        </button>
        <button class="btn-close" onclick="window.close()">
            <i class="fas fa-times"></i> বন্ধ করুন
        </button>
        <a href="<?php echo $type === 'student' ? 'report.php' : 'teacher_report.php'; ?>?date=<?php echo urlencode($date); ?>" class="btn-back-home">
            <i class="fas fa-arrow-left"></i> রিপোর্টে ফিরে যান
        </a>
        <a href="../home.php" class="btn-back-home" style="background:#4f46e5;">
            <i class="fas fa-home"></i> ড্যাশবোর্ড
        </a>
    </div>
</div>
</body>
</html>