<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'daily';
$date = $_GET['date'] ?? date('Y-m-d');

$response = [];

try {
    if ($type === 'daily') {
        // Daily statistics
        $stmt = $conn->query("SELECT COUNT(*) FROM students WHERE status = 'active'");
        $response['total_students'] = (int)$stmt->fetchColumn();
        
        $stmt = $conn->query("SELECT COUNT(*) FROM teachers WHERE status = 'active'");
        $response['total_teachers'] = (int)$stmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE date = :date");
        $stmt->execute(['date' => $date]);
        $response['present_today'] = (int)$stmt->fetchColumn();
        
        // Check if today is off day
        $stmt = $conn->prepare("SELECT id FROM off_days WHERE date = :date");
        $stmt->execute(['date' => $date]);
        $response['is_off_day'] = $stmt->fetch() ? true : false;
        
        // Absent today
        $response['absent_today'] = $response['total_students'] - $response['present_today'];
        $response['attendance_percentage'] = $response['total_students'] > 0 
            ? round(($response['present_today'] / $response['total_students']) * 100, 2) 
            : 0;
        
    } elseif ($type === 'monthly') {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));
        
        // Get total students
        $stmt = $conn->query("SELECT COUNT(*) FROM students WHERE status = 'active'");
        $total_students = (int)$stmt->fetchColumn();
        
        // Get attendance for the month
        $stmt = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE date BETWEEN :start AND :end");
        $stmt->execute(['start' => $start_date, 'end' => $end_date]);
        $total_attendance = (int)$stmt->fetchColumn();
        
        // Get working days (excluding off days and weekends)
        $stmt = $conn->prepare("SELECT date FROM off_days WHERE date BETWEEN :start AND :end");
        $stmt->execute(['start' => $start_date, 'end' => $end_date]);
        $off_days = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $working_days = 0;
        $current = strtotime($start_date);
        while ($current <= strtotime($end_date)) {
            $d = date('Y-m-d', $current);
            $day_of_week = date('w', $current);
            if ($day_of_week != 5 && $day_of_week != 6 && !in_array($d, $off_days)) {
                $working_days++;
            }
            $current = strtotime('+1 day', $current);
        }
        
        $response['month'] = $month;
        $response['year'] = $year;
        $response['total_students'] = $total_students;
        $response['working_days'] = $working_days;
        $response['total_attendance'] = $total_attendance;
        $response['average_daily_attendance'] = $working_days > 0 ? round($total_attendance / $working_days, 2) : 0;
        $response['attendance_percentage'] = $total_students > 0 && $working_days > 0 
            ? round(($total_attendance / ($total_students * $working_days)) * 100, 2) 
            : 0;
    }
    
    $response['success'] = true;

} catch (PDOException $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>