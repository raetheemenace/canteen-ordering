<?php
// public/auth/login.php
session_start();
require_once __DIR__ . '/../../src/database.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^\d{7}$/', $student_number)) {
        $errors[] = "Student number must be 7 digits.";
    }

    if (empty($errors)) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'student' LIMIT 1");
            $stmt->execute([$student_number]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: /canteen-ordering/public/admin/dashboard.php");
                } else {
                    header("Location: /canteen-ordering/public/student/dashboard.php");
                }
                exit;
            } else {
                $errors[] = "Invalid student number or password.";
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
  <title>T.I.P KainTeen! - Sign In</title>

  <!-- Fixed asset references -->
  <link rel="shortcut icon" href="/canteen-ordering/public/assets/images/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="/canteen-ordering/public/assets/css/signin.css">
</head>
<body>
  <video class="bg-video" autoplay loop muted>
    <source src="/canteen-ordering/public/assets/videos/bg.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <div class="container">
    <img src="/canteen-ordering/public/assets/images/logo.png" class="logo" alt="Logo">
    <p class="subtitle">From Click to Kain in No Time!</p>
    <h1>Sign In</h1>

    <?php if (!empty($errors)): ?>
      <div class="popup error"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="form-group">
        <label for="student_number">Student Number</label>
        <input type="text" id="student_number" name="student_number" placeholder="7 Digit Student No." required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter Password" required>
      </div>

      <p class="forgot"><a href="#">Forgot Password?</a></p>

      <button type="submit">Sign In</button>
    </form>

    <p class="signin">Don't have an account? <a href="/canteen-ordering/public/auth/register.php">Sign Up here</a>.</p>
  </div>

  <script src="/canteen-ordering/public/assets/js/signin.js"></script>
</body>
</html>
