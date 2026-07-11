<?php
require_once 'config/db.php';

echo "<h1>✅ ডেটাবেস কানেকশন টেস্ট</h1>";
echo "<hr>";

echo "<p><strong>ডেটাবেস নাম:</strong> " . DB_NAME . "</p>";
echo "<p><strong>হোস্ট:</strong> " . DB_HOST . "</p>";
echo "<p><strong>ইউজার:</strong> " . DB_USER . "</p>";
echo "<p><strong>স্ট্যাটাস:</strong> ✅ কানেকশন সফল!</p>";

echo "<hr>";

// টেস্ট কোয়েরি - students
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM students");
    $count = $stmt->fetchColumn();
    echo "<p><strong>মোট ছাত্র:</strong> " . $count . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'><strong>ত্রুটি:</strong> " . $e->getMessage() . "</p>";
}

// টেস্ট কোয়েরি - teachers
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM teachers");
    $count = $stmt->fetchColumn();
    echo "<p><strong>মোট শিক্ষক:</strong> " . $count . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'><strong>ত্রুটি:</strong> " . $e->getMessage() . "</p>";
}

// টেস্ট কোয়েরি - users
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "<p><strong>মোট ইউজার:</strong> " . $count . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'><strong>ত্রুটি:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p style='color:green;font-size:18px;font-weight:bold;'>🎉 সবকিছু ঠিক আছে! আপনি এখন লগইন করতে পারেন।</p>";
echo "<p><a href='auth/login.php' style='background:#4338ca;color:white;padding:10px 20px;border-radius:10px;text-decoration:none;'>🔑 লগইন পেজে যান</a></p>";
?>