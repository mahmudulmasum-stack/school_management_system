<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $conn->prepare("DELETE FROM off_days WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $_SESSION['success'] = 'অফ ডে মুছে ফেলা হয়েছে!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'মুছে ফেলতে ত্রুটি: ' . $e->getMessage();
    }
}

header('Location: off_days.php');
exit();
?>