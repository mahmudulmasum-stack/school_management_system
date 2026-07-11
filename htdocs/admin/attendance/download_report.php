<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$date = $_GET['date'] ?? date('Y-m-d');
$type = $_GET['type'] ?? 'student';

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance_report_' . $date . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8

if ($type === 'student') {
    fputcsv($output, ['#', 'নাম', 'রোল', 'শ্রেণী', 'বিভাগ', 'মোবাইল', 'এন্ট্রি সময়', 'এক্সিট সময়', 'স্থিতি']);
    
    $sql = "SELECT s.name, s.roll, s.class, s.group_name, s.guardian_phone,
            CASE WHEN a.id IS NOT NULL THEN 'উপস্থিত' ELSE 'অনুপস্থিত' END as status,
            a.time_in as entry_time, a.time_out as exit_time
            FROM students s
            LEFT JOIN attendance a ON s.id = a.student_id AND a.date = :date
            WHERE s.status = 'active'
            ORDER BY s.class, s.roll";
} else {
    fputcsv($output, ['#', 'নাম', 'মোবাইল', 'পদবী', 'বিষয়', 'এন্ট্রি সময়', 'এক্সিট সময়', 'স্থিতি']);
    
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

$index = 1;
while ($row = $stmt->fetch()) {
    if ($type === 'student') {
        $sectionDisplay = 'N/A';
        if ($row['class'] >= 9 && $row['class'] <= 12) {
            $sectionDisplay = $row['group_name'] ?: 'N/A';
        }
        fputcsv($output, [
            $index++,
            htmlspecialchars($row['name']),
            htmlspecialchars($row['roll']),
            htmlspecialchars($row['class']),
            $sectionDisplay,
            $row['guardian_phone'] ?: '—',
            $row['entry_time'] ? date('h:i:s A', strtotime($row['entry_time'])) : '—',
            $row['exit_time'] ? date('h:i:s A', strtotime($row['exit_time'])) : '—',
            $row['status']
        ]);
    } else {
        fputcsv($output, [
            $index++,
            htmlspecialchars($row['name']),
            htmlspecialchars($row['number']),
            $row['designation'] ?: '—',
            $row['subject'] ?: '—',
            $row['entry_time'] ? date('h:i:s A', strtotime($row['entry_time'])) : '—',
            $row['exit_time'] ? date('h:i:s A', strtotime($row['exit_time'])) : '—',
            $row['status']
        ]);
    }
}

fclose($output);
exit();
?>