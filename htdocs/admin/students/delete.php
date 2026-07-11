<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $conn->prepare("SELECT photo_path FROM students WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();
        
        if ($student) {
            // Delete photo if not default
            if ($student['photo_path'] != 'uploads/default.jpg' && file_exists('../../' . $student['photo_path'])) {
                @unlink('../../' . $student['photo_path']);
            }
            
            // Delete attendance records first (foreign key)
            $stmt = $conn->prepare("DELETE FROM attendance WHERE student_id = :id");
            $stmt->execute(['id' => $id]);
            
            // Delete student
            $stmt = $conn->prepare("DELETE FROM students WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $_SESSION['success'] = 'ছাত্র মুছে ফেলা হয়েছে!';
        } else {
            $_SESSION['error'] = 'ছাত্র পাওয়া যায়নি';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'মুছে ফেলতে ত্রুটি: ' . $e->getMessage();
    }
}

header('Location: list.php');
exit();
?>