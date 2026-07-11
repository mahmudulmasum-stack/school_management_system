<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM teachers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();
        
        if ($teacher) {
            $teacher['photo_path'] = $teacher['photo_path'] ?? 'uploads/default.jpg';
            echo json_encode($teacher);
        } else {
            echo json_encode(['error' => 'Teacher not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>