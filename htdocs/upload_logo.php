<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'ফাইল আপলোড হয়নি']);
    exit;
}

$file = $_FILES['logo'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'শুধুমাত্র JPEG, PNG, GIF বা WEBP ফাইল অনুমোদিত']);
    exit;
}

// Validate file size (max 2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'ফাইল সাইজ ২MB এর বেশি হতে পারে না']);
    exit;
}

$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = 'logo.' . $ext;
$target = $upload_dir . $filename;

// Delete old logo files
foreach (glob($upload_dir . 'logo.*') as $old) {
    if ($old !== $target) {
        @unlink($old);
    }
}

if (move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode([
        'success' => true,
        'path' => '../uploads/' . $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'ফাইল সংরক্ষণ করতে সমস্যা']);
}
?>