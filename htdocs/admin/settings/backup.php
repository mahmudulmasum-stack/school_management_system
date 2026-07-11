<?php
require_once '../../config/db.php';
require_once '../../includes/auth_check.php';

// Create backup directory if not exists
$backup_dir = '../../backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$filepath = $backup_dir . $filename;

try {
    // Get all tables
    $tables = [];
    $stmt = $conn->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    // Generate SQL
    $output = "-- Database Backup\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- Database: " . DB_NAME . "\n\n";
    $output .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $output .= "START TRANSACTION;\n\n";

    foreach ($tables as $table) {
        // Create table
        $stmt = $conn->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch();
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $output .= $row['Create Table'] . ";\n\n";
        
        // Insert data
        $stmt = $conn->query("SELECT * FROM `$table`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns = array_keys($row);
            $values = array_map(function($v) {
                return is_null($v) ? 'NULL' : "'" . addslashes($v) . "'";
            }, array_values($row));
            $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }
        $output .= "\n";
    }

    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $output .= "COMMIT;\n";

    // Save file
    file_put_contents($filepath, $output);

    // Download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);

    // Clean up old backups (keep last 10)
    $files = glob($backup_dir . '*.sql');
    if (count($files) > 10) {
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        $to_delete = array_slice($files, 0, count($files) - 10);
        foreach ($to_delete as $file) {
            @unlink($file);
        }
    }

} catch (PDOException $e) {
    $_SESSION['error'] = 'ব্যাকআপ ত্রুটি: ' . $e->getMessage();
    header('Location: index.php');
    exit();
}

exit();
?>