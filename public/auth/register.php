<?php
// public/auth/register.php
session_start();

require_once __DIR__ . '/../../src/database.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid TIP email.";
    }

    if (!preg_match('/^\d{7}$/', $student_number)) {
        $errors[] = "Student number must be 7 digits.";
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

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "An account with this email already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (role, username, email, password_hash, full_name, status) 
                                       VALUES ('student', ?, ?, ?, ?, 'active')");
                $stmt->execute([
                    $student_number,
                    $email,
                    $hash,
                    "Student $student_number"
                ]);

                $success = "Registration successful! You may now log in.";
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>T.I.P KainTeen! - Sign Up</title>
  <link rel="shortcut icon" href="/assets/images/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="/assets/css/signup.css">
</head>
<body>
  <video class="bg-video" autoplay loop muted>
    <source src="/assets/videos/bg.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <div class="container">
    <img src="/assets/images/logo.png" class="logo" alt="Logo">
    <p class="subtitle">From Click to Kain in No Time!</p>
    <h1>Create Your Account</h1>

    <?php if (!empty($errors)): ?>
      <div class="popup error"><?= implode('<br>', $errors) ?></div>
    <?php elseif ($success): ?>
      <div class="popup"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="form-group">
        <label for="email">TIP Email</label>
        <input type="text" id="email" name="email" placeholder="Enter TIP Email" required>
      </div>

      <div class="form-group">
        <label for="student_number">Student Number</label>
        <input type="text" id="student_number" name="student_number" placeholder="7 Digit Student No." required>
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

    <p class="signin">Already have an account? <a href="/auth/login.php">Sign In here</a>.</p>
  </div>

  <script src="/assets/js/signup.js"></script>
</body>
</html>
