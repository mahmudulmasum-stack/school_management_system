<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

$file = $_GET['file'] ?? '';
$backup_dir = '../../backups/';
$filepath = $backup_dir . basename($file);

if (file_exists($filepath) && is_file($filepath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit();
} else {
    $_SESSION['error'] = 'ফাইল পাওয়া যায়নি';
    header('Location: index.php');
    exit();
}
?>