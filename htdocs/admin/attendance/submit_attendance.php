<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$card_id = $input['card_id'] ?? '';
$date = date('Y-m-d');
$time = date('H:i:s');

$response = ['success' => false, 'message' => ''];

if (empty($card_id)) {
    $response['message'] = 'কার্ড আইডি দিন';
    echo json_encode($response);
    exit();
}

try {
    // Check if today is off day
    $stmt = $conn->prepare("SELECT id FROM off_days WHERE date = :date");
    $stmt->execute(['date' => $date]);
    if ($stmt->fetch()) {
        $response['message'] = 'আজ অফ ডে। উপস্থিতি নেওয়া যাবে না।';
        echo json_encode($response);
        exit();
    }

    // Check for student
    $stmt = $conn->prepare("SELECT id, name, roll, class, group_name, photo_path FROM students WHERE card_id = :card_id AND status = 'active'");
    $stmt->execute(['card_id' => $card_id]);
    $student = $stmt->fetch();

    if ($student) {
        // Check if already marked present today
        $stmt = $conn->prepare("SELECT id, time_in, time_out FROM attendance WHERE student_id = :student_id AND date = :date");
        $stmt->execute(['student_id' => $student['id'], 'date' => $date]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // If already present and no time_out, mark as exit (time_out)
            if ($existing['time_in'] && !$existing['time_out']) {
                $stmt = $conn->prepare("UPDATE attendance SET time_out = :time_out WHERE id = :id");
                $stmt->execute(['time_out' => $time, 'id' => $existing['id']]);
                
                $response['success'] = true;
                $response['message'] = 'আউট সময় রেকর্ড করা হয়েছে!';
                $response['type'] = 'student';
                $response['action'] = 'exit';
                $response['data'] = [
                    'name' => htmlspecialchars($student['name']),
                    'roll' => htmlspecialchars($student['roll']),
                    'class' => htmlspecialchars($student['class']),
                    'photo_path' => htmlspecialchars($student['photo_path'] ?? 'uploads/default.jpg'),
                    'time_in' => date('h:i:s A', strtotime($existing['time_in'])),
                    'time_out' => date('h:i:s A', strtotime($time))
                ];
                echo json_encode($response);
                exit();
            } else {
                $response['message'] = 'আপনি ইতিমধ্যে উপস্থিতি দিয়েছেন এবং বেরিয়ে গেছেন';
                echo json_encode($response);
                exit();
            }
        }
        
        // Mark attendance (entry)
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, time_in, status, nfc_scanned) VALUES (:student_id, :date, :time, 'present', 1)");
        $stmt->execute(['student_id' => $student['id'], 'date' => $date, 'time' => $time]);
        
        $response['success'] = true;
        $response['message'] = 'উপস্থিতি সফলভাবে নেওয়া হয়েছে!';
        $response['type'] = 'student';
        $response['action'] = 'entry';
        $response['data'] = [
            'name' => htmlspecialchars($student['name']),
            'roll' => htmlspecialchars($student['roll']),
            'class' => htmlspecialchars($student['class']),
            'photo_path' => htmlspecialchars($student['photo_path'] ?? 'uploads/default.jpg'),
            'time_in' => date('h:i:s A', strtotime($time)),
            'time_out' => null
        ];
        echo json_encode($response);
        exit();
    }

    // Check for teacher
    $stmt = $conn->prepare("SELECT id, name, number, photo_path FROM teachers WHERE card_id = :card_id AND status = 'active'");
    $stmt->execute(['card_id' => $card_id]);
    $teacher = $stmt->fetch();

    if ($teacher) {
        $stmt = $conn->prepare("SELECT id, time_in, time_out FROM teacher_attendance WHERE teacher_id = :teacher_id AND date = :date");
        $stmt->execute(['teacher_id' => $teacher['id'], 'date' => $date]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($existing['time_in'] && !$existing['time_out']) {
                $stmt = $conn->prepare("UPDATE teacher_attendance SET time_out = :time_out WHERE id = :id");
                $stmt->execute(['time_out' => $time, 'id' => $existing['id']]);
                
                $response['success'] = true;
                $response['message'] = 'আউট সময় রেকর্ড করা হয়েছে!';
                $response['type'] = 'teacher';
                $response['action'] = 'exit';
                $response['data'] = [
                    'name' => htmlspecialchars($teacher['name']),
                    'number' => htmlspecialchars($teacher['number']),
                    'photo_path' => htmlspecialchars($teacher['photo_path'] ?? 'uploads/default.jpg'),
                    'time_in' => date('h:i:s A', strtotime($existing['time_in'])),
                    'time_out' => date('h:i:s A', strtotime($time))
                ];
                echo json_encode($response);
                exit();
            } else {
                $response['message'] = 'আপনি ইতিমধ্যে উপস্থিতি দিয়েছেন এবং বেরিয়ে গেছেন';
                echo json_encode($response);
                exit();
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO teacher_attendance (teacher_id, date, time_in, status, nfc_scanned) VALUES (:teacher_id, :date, :time, 'present', 1)");
        $stmt->execute(['teacher_id' => $teacher['id'], 'date' => $date, 'time' => $time]);
        
        $response['success'] = true;
        $response['message'] = 'উপস্থিতি সফলভাবে নেওয়া হয়েছে!';
        $response['type'] = 'teacher';
        $response['action'] = 'entry';
        $response['data'] = [
            'name' => htmlspecialchars($teacher['name']),
            'number' => htmlspecialchars($teacher['number']),
            'photo_path' => htmlspecialchars($teacher['photo_path'] ?? 'uploads/default.jpg'),
            'time_in' => date('h:i:s A', strtotime($time)),
            'time_out' => null
        ];
        echo json_encode($response);
        exit();
    }

    $response['message'] = 'এই কার্ড আইডি পাওয়া যায়নি!';
    echo json_encode($response);

} catch (PDOException $e) {
    $response['message'] = 'সার্ভার ত্রুটি: ' . $e->getMessage();
    echo json_encode($response);
}
?>