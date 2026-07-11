<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $conn->prepare("SELECT photo_path FROM teachers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();
        
        if ($teacher) {
            if ($teacher['photo_path'] != 'uploads/default.jpg' && file_exists('../../' . $teacher['photo_path'])) {
                @unlink('../../' . $teacher['photo_path']);
            }
            
            // Delete teacher attendance records
            $stmt = $conn->prepare("DELETE FROM teacher_attendance WHERE teacher_id = :id");
            $stmt->execute(['id' => $id]);
            
            $stmt = $conn->prepare("DELETE FROM teachers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $_SESSION['success'] = 'শিক্ষক মুছে ফেলা হয়েছে!';
        } else {
            $_SESSION['error'] = 'শিক্ষক পাওয়া যায়নি';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'মুছে ফেলতে ত্রুটি: ' . $e->getMessage();
    }
}

header('Location: list.php');
exit();
?>