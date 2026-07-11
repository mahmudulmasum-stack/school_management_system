<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$file = $_GET['file'] ?? '';
$backup_dir = '../../backups/';
$filepath = $backup_dir . basename($file);

if (file_exists($filepath) && is_file($filepath)) {
    if (unlink($filepath)) {
        $_SESSION['success'] = 'ব্যাকআপ ফাইল মুছে ফেলা হয়েছে!';
    } else {
        $_SESSION['error'] = 'ফাইল মুছে ফেলতে সমস্যা হয়েছে';
    }
} else {
    $_SESSION['error'] = 'ফাইল পাওয়া যায়নি';
}

header('Location: index.php');
exit();
?>