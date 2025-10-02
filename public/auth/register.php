<?php
// public/auth/register.php
session_start();

// correct path from /public/auth/ to project src/
require_once __DIR__ . '/../../src/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Server-side validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email.";
    }
    if (!preg_match('/^\d{7}$/', $student_number)) {
        $errors[] = "Student number must be exactly 7 digits.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        try {
            $pdo = getPDO();

            // Check existing student_number or email
            $check = $pdo->prepare("SELECT id FROM users WHERE student_number = ? OR email = ?");
            $check->execute([$student_number, $email]);
            if ($check->fetch()) {
                $errors[] = "Student number or email already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO users (role, student_number, email, password_hash, status)
                     VALUES ('student', ?, ?, ?, 'active')"
                );
                $stmt->execute([$student_number, $email, $hash]);

                $_SESSION['flash'] = "Registration successful! You may now log in.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "DB Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>T.I.P KainTeen! - Register</title>
  <link rel="stylesheet" href="../assets/css/signup.css">
</head>
<body>
  <video class="bg-video" autoplay loop muted>
    <source src="../assets/videos/bg.mp4" type="video/mp4">
  </video>

  <div class="container">
    <img src="../assets/images/logo.png" class="logo" alt="Logo">
    <h1>Create Account</h1>

    <?php if (!empty($errors)): ?>
      <div class="popup error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-group">
        <label for="email">TIP Email</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="student_number">Student Number</label>
        <input type="text" id="student_number" name="student_number" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
      </div>

      <button type="submit">Sign Up</button>
    </form>

    <p class="signin">Already have an account?
      <a href="login.php">Sign In</a>
    </p>
  </div>

  <script src="../assets/js/signup.js"></script>
</body>
</html>
