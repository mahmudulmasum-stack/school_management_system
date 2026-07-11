<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$date = $_GET['date'] ?? date('Y-m-d');
$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance_report_' . $date . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8

fputcsv($output, ['#', 'নাম', 'রোল', 'শ্রেণী', 'বিভাগ', 'মোবাইল', 'স্থিতি', 'সময়']);

$sql = "SELECT s.name, s.roll, s.class, s.group_name, s.guardian_phone,
        CASE WHEN a.id IS NOT NULL THEN 'উপস্থিত' ELSE 'অনুপস্থিত' END as status,
        a.time_in as entry_time
        FROM students s
        LEFT JOIN attendance a ON s.id = a.student_id AND a.date = :date
        WHERE s.status = 'active'";
$params = [':date' => $date];

if (!empty($class)) {
    $sql .= " AND s.class = :class";
    $params[':class'] = $class;
}
if (!empty($section)) {
    $sql .= " AND s.group_name = :section";
    $params[':section'] = $section;
}

$sql .= " ORDER BY s.class, s.roll";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

$index = 1;
while ($row = $stmt->fetch()) {
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
        $row['status'],
        $row['entry_time'] ? date('h:i:s A', strtotime($row['entry_time'])) : '—'
    ]);
}

fclose($output);
exit();
?>