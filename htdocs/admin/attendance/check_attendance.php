<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

$card_id = $_GET['card_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

if (empty($card_id)) {
    echo json_encode(['error' => 'কার্ড আইডি প্রয়োজন']);
    exit();
}

try {
    // Check student
    $stmt = $conn->prepare("SELECT id, name, roll, class, photo_path FROM students WHERE card_id = :card_id AND status = 'active'");
    $stmt->execute(['card_id' => $card_id]);
    $student = $stmt->fetch();

    if ($student) {
        $stmt = $conn->prepare("SELECT id, time_in, time_out FROM attendance WHERE student_id = :student_id AND date = :date");
        $stmt->execute(['student_id' => $student['id'], 'date' => $date]);
        $attendance = $stmt->fetch();
        
        $status = 'absent';
        $time_in = null;
        $time_out = null;
        
        if ($attendance) {
            $status = 'present';
            $time_in = $attendance['time_in'];
            $time_out = $attendance['time_out'] ?? null;
            if ($time_out) {
                $status = 'exited';
            }
        }
        
        echo json_encode([
            'success' => true,
            'type' => 'student',
            'data' => [
                'name' => htmlspecialchars($student['name']),
                'roll' => htmlspecialchars($student['roll']),
                'class' => htmlspecialchars($student['class']),
                'photo_path' => htmlspecialchars($student['photo_path'] ?? 'uploads/default.jpg'),
                'status' => $status,
                'time_in' => $time_in ? date('h:i:s A', strtotime($time_in)) : null,
                'time_out' => $time_out ? date('h:i:s A', strtotime($time_out)) : null,
                'date' => $date
            ]
        ]);
        exit();
    }

    // Check teacher
    $stmt = $conn->prepare("SELECT id, name, number, photo_path FROM teachers WHERE card_id = :card_id AND status = 'active'");
    $stmt->execute(['card_id' => $card_id]);
    $teacher = $stmt->fetch();

    if ($teacher) {
        $stmt = $conn->prepare("SELECT id, time_in, time_out FROM teacher_attendance WHERE teacher_id = :teacher_id AND date = :date");
        $stmt->execute(['teacher_id' => $teacher['id'], 'date' => $date]);
        $attendance = $stmt->fetch();
        
        $status = 'absent';
        $time_in = null;
        $time_out = null;
        
        if ($attendance) {
            $status = 'present';
            $time_in = $attendance['time_in'];
            $time_out = $attendance['time_out'] ?? null;
            if ($time_out) {
                $status = 'exited';
            }
        }
        
        echo json_encode([
            'success' => true,
            'type' => 'teacher',
            'data' => [
                'name' => htmlspecialchars($teacher['name']),
                'number' => htmlspecialchars($teacher['number']),
                'photo_path' => htmlspecialchars($teacher['photo_path'] ?? 'uploads/default.jpg'),
                'status' => $status,
                'time_in' => $time_in ? date('h:i:s A', strtotime($time_in)) : null,
                'time_out' => $time_out ? date('h:i:s A', strtotime($time_out)) : null,
                'date' => $date
            ]
        ]);
        exit();
    }

    echo json_encode(['success' => false, 'error' => 'কার্ড আইডি পাওয়া যায়নি']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'সার্ভার ত্রুটি: ' . $e->getMessage()]);
}
?>