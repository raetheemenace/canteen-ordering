<?php
// public/auth/auth_test.php -- one-off debug script
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../src/database.php';

$student = '0000000';          // change if you want to test another
$tryPassword = 'AdminPass123!';// change to the password you're testing

echo "<h2>Auth test</h2>";

try {
    $pdo = getPDO();
} catch (Exception $e) {
    echo "<p style='color:red'>DB connect failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT id, role, username, student_number, email, password_hash, status FROM users WHERE student_number = ? LIMIT 1");
$stmt->execute([$student]);
$user = $stmt->fetch();

echo "<h3>DB row (user)</h3><pre>" . htmlspecialchars(var_export($user, true)) . "</pre>";

if (!$user) {
    echo "<p style='color:red'>No user found for student_number = {$student}</p>";
    exit;
}

$verify = password_verify($tryPassword, $user['password_hash']) ? 'TRUE' : 'FALSE';
echo "<p>Password verify with '{$tryPassword}': <strong>{$verify}</strong></p>";

echo "<h3>PHP session info</h3>";
echo "<pre>session_start? " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NOT ACTIVE') . "</pre>";
echo "<p>To test redirect behaviour, POST to /public/auth/admin_login.php using curl - see instructions.</p>";
