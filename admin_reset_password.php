<?php
// admin_reset_password.php - run once to set admin password
include("config/db.php");
$username = 'admin';
$new = 'admin123'; // set to strong password then delete file
$hash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->execute([$hash, $username]);
echo "Password updated for $username\n";
?>