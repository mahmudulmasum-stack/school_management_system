<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();
        
        if ($student) {
            // Sanitize output
            $student['photo_path'] = $student['photo_path'] ?? 'uploads/default.jpg';
            echo json_encode($student);
        } else {
            echo json_encode(['error' => 'Student not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>