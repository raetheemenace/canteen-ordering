<?php
// public/auth/register.php (robust include + debug)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

// Try multiple candidate paths to locate src/database.php reliably
$possible_db_paths = [
    __DIR__ . '/../../src/database.php', // from public/auth => project/src
    __DIR__ . '/../src/database.php',    // from public => project/src (if used there)
    __DIR__ . '/../../../src/database.php', // extra fallback
];

$db_file = null;
foreach ($possible_db_paths as $p) {
    if (file_exists($p)) { $db_file = $p; break; }
}
if (!$db_file) {
    // helpful error if include missing
    http_response_code(500);
    echo "<h2>Configuration error</h2>";
    echo "<p>Could not find <code>src/database.php</code> using tried paths:</p><pre>" . htmlspecialchars(implode("\n", $possible_db_paths)) . "</pre>";
    exit;
}
require_once $db_file;

// ensure function exists
if (!function_exists('getPDO')) {
    http_response_code(500);
    echo "<h2>Configuration error</h2><p><code>getPDO()</code> not found in {$db_file}. Open that file and ensure it defines getPDO().</p>";
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid TIP email.";
    }

    // Validate student number (7 digits)
    if (!preg_match('/^\d{7}$/', $student_number)) {
        $errors[] = "Student number must be exactly 7 digits.";
    }

    // Validate password
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        try {
            $pdo = getPDO();

            // Check uniqueness: student_number and email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE student_number = ? OR email = ? LIMIT 1");
            $stmt->execute([$student_number, $email]);
            if ($stmt->fetch()) {
                $errors[] = "Student number or email already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // Insert — adapt to your exact users schema: here we assume columns exist: student_number,email,password_hash,role,status,created_at
                $stmt = $pdo->prepare("INSERT INTO users (role, student_number, email, password_hash, status, created_at) VALUES ('student', ?, ?, ?, 'active', NOW())");
                $stmt->execute([$student_number, $email, $hash]);
                $success = "Registration successful — you may now log in.";
            }
        } catch (Exception $e) {
            $errors[] = "DB error: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register - TIP KainTeen</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/canteen-ordering/public/assets/css/signup.css">
</head>
<body>
  <video class="bg-video" autoplay loop muted>
    <source src="/canteen-ordering/public/assets/videos/bg.mp4" type="video/mp4">
  </video>

  <div class="container">
    <img src="/canteen-ordering/public/assets/images/logo.png" class="logo" alt="Logo">
    <p class="subtitle">From Click to Kain in No Time!</p>
    <h1>Create Your Account</h1>

    <?php if (!empty($errors)): ?>
      <div class="popup error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php elseif ($success): ?>
      <div class="popup"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="form-group">
        <label for="email">TIP Email</label>
        <input type="email" id="email" name="email" placeholder="Enter TIP Email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
      </div>

      <div class="form-group">
        <label for="student_number">Student Number</label>
        <input type="text" id="student_number" name="student_number" placeholder="7 Digit Student No." required pattern="\d{7}" value="<?= isset($student_number) ? htmlspecialchars($student_number) : '' ?>">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter Password" required>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter Password" required>
      </div>

      <div class="terms">
        <input type="checkbox" id="terms" required>
        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</label>
      </div>

      <button type="submit">Sig
