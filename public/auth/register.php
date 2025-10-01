<?php
// public/auth/register.php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/database.php';

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

    // Validate passwords
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

                $stmt = $pdo->prepare("INSERT INTO users (role, student_number, username, email, password_hash, status, created_at) VALUES ('student', ?, ?, ?, ?, 'active', NOW())");
                // we store student_number and also set username to student_number for compatibility
                $stmt->execute([
                    $student_number,
                    $student_number,    // username column kept for legacy if present
                    $email,
                    $hash
                ]);

                $success = "Registration successful — you may now log in.";
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Register — TIP KainTeen</title>
  <link rel="stylesheet" href="<?= rtrim(BASE_URL, '/') ?>/assets/css/signup.css">
</head>
<body>
  <video class="bg-video" autoplay loop muted>
    <source src="<?= rtrim(BASE_URL, '/') ?>/assets/videos/bg.mp4" type="video/mp4">
  </video>

  <div class="container">
    <img src="<?= rtrim(BASE_URL, '/') ?>/assets/images/logo.png" class="logo" alt="Logo">
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
        <input type="email" id="email" name="email" placeholder="Enter TIP Email" required value="<?= htmlspecialchars($email ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="student_number">Student Number</label>
        <input type="text" id="student_number" name="student_number" placeholder="7 Digit Student No." required pattern="\d{7}" value="<?= htmlspecialchars($student_number ?? '') ?>">
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

      <button type="submit">Sign Up</button>
    </form>

    <p class="signin">Already have an account? <a href="<?= rtrim(BASE_URL, '/') ?>/auth/login.php">Sign In here</a>.</p>
  </div>

  <script src="<?= rtrim(BASE_URL, '/') ?>/assets/js/signup.js"></script>
</body>
</html>
