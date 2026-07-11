<?php
// ========================================================
// ডেটাবেস কনফিগারেশন - InfinityFree
// ========================================================

// ডেটাবেস তথ্য (আপনার vPanel থেকে কপি করুন)
define('DB_HOST', 'sql310.infinityfree.com');
define('DB_USER', 'if0_42282183');
define('DB_PASS', 'Masum2821');
define('DB_NAME', 'if0_42282183_school_management');

// ========================================================
// সাইট কনফিগারেশন
// ========================================================

define('SITE_NAME', 'Sunshine School & College');
define('SITE_SLOGAN', 'শিক্ষা, সংস্কৃতি, উন্নয়ন');
define('ESTABLISHED_YEAR', 2000);
define('BASE_URL', 'https://masumschool.site.je/');

// ========================================================
// ডেটাবেস কানেকশন
// ========================================================

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    die("ডেটাবেস কানেকশন ত্রুটি: " . $e->getMessage());
}

// ========================================================
// সেশন স্টার্ট
// ========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================================
// হেল্পার ফাংশন
// ========================================================

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser() {
    return $_SESSION['user'] ?? null;
}

function getRole() {
    return $_SESSION['role'] ?? null;
}

function getFullName() {
    return $_SESSION['full_name'] ?? 'User';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// ========================================================
// টাইমজোন সেট করুন
// ========================================================

date_default_timezone_set('Asia/Dhaka');
?>