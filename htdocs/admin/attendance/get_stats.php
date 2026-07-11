<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$from_date = $input['from_date'] ?? '';
$to_date = $input['to_date'] ?? '';

$response = ['success' => false, 'message' => ''];

if (empty($from_date) || empty($to_date)) {
    $response['message'] = 'তারিখ নির্বাচন করুন';
    echo json_encode($response);
    exit();
}

if (strtotime($to_date) < strtotime($from_date)) {
    $response['message'] = 'শেষ তারিখ শুরু তারিখের আগে হতে পারে না';
    echo json_encode($response);
    exit();
}

try {
    $conn->beginTransaction();
    
    $current = $from_date;
    $count = 0;
    while (strtotime($current) <= strtotime($to_date)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO off_days (date, reason, is_holiday) VALUES (:date, 'ম্যানুয়ালি মার্ক করা', 1)");
        $stmt->execute(['date' => $current]);
        if ($stmt->rowCount() > 0) $count++;
        $current = date('Y-m-d', strtotime($current . ' +1 day'));
    }
    
    $conn->commit();
    $response['success'] = true;
    $response['message'] = "$count দিন অফ ডে হিসেবে মার্ক করা হয়েছে!";
    $response['count'] = $count;
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = 'ত্রুটি: ' . $e->getMessage();
}

echo json_encode($response);
?>