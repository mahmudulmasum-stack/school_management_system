<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$date = $_GET['date'] ?? date('Y-m-d');
$subject = $_GET['subject'] ?? '';
$designation = $_GET['designation'] ?? '';

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="teacher_attendance_report_' . $date . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8

fputcsv($output, ['#', 'নাম', 'মোবাইল', 'পদবী', 'বিষয়', 'স্থিতি', 'সময়']);

$sql = "SELECT t.name, t.number, t.subject, t.designation,
        CASE WHEN ta.id IS NOT NULL THEN 'উপস্থিত' ELSE 'অনুপস্থিত' END as status,
        ta.time_in as entry_time
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

$index = 1;
while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $index++,
        htmlspecialchars($row['name']),
        htmlspecialchars($row['number']),
        $row['designation'] ?: '—',
        $row['subject'] ?: '—',
        $row['status'],
        $row['entry_time'] ? date('h:i:s A', strtotime($row['entry_time'])) : '—'
    ]);
}

fclose($output);
exit();
?>