<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Check if session expired (optional) - 1 hour timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    session_unset();
    session_destroy();
    header('Location: ../auth/login.php?msg=session_expired');
    exit();
}
$_SESSION['last_activity'] = time();
?>