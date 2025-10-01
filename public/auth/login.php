<?php
// public/auth/login.php
session_start();
require_once __DIR__ . '/../../src/database.php';

$BASE = '/canteen-ordering/public';

// If already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') {
        header("Location: {$BASE}/student/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: {$BASE}/admin/dashboard.php");
        exit;
    }
}

$error = "";

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($student_number && $password) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE student_number = ? LIMIT 1");
        $stmt->execute([$student_number]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_number'] = $user['student_number'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect URL
            $redirectUrl = $user['role'] === 'admin'
                ? "{$BASE}/admin/dashboard.php"
                : "{$BASE}/student/dashboard.php";

            // AJAX → plain text
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo "success|$redirectUrl";
                exit;
            }

            // Normal POST → redirect
            header("Location: $redirectUrl");
            exit;
        } else {
            $error = "Invalid student number or password.";
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo $error;
                exit;
            }
        }
    } else {
        $error = "Please fill in all fields.";
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo $error;
            exit;
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
  <link rel="shortcut icon" href="<?= $BASE ?>/assets/images/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="<?= $BASE ?>/assets/css/signin.css">
</head>
<body>
  <video class="bg-video" autoplay loop muted>
    <source src="<?= $BASE ?>/assets/videos/bg.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <div class="container">
    <img src="<?= $BASE ?>/assets/images/logo.png" class="logo" alt="Logo">
    <p class="subtitle">From Click to Kain in No Time!</p>
    <h1>Sign In</h1>

    <?php if ($error && empty($_SERVER['HTTP_X_REQUESTED_WITH'])): ?>
      <div class="popup error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" id="login-form">
      <div class="form-group">
        <label for="student-number">Student Number</label>
        <input type="text" id="student-number" name="student_number" placeholder="7 Digit Student No." required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter Password" required>
      </div>
      
      <p class="forgot"><a href="#">Forgot Password?</a></p>

      <button type="submit">Sign In</button>
    </form>

    <p class="signin">Don't have an account? <a href="<?= $BASE ?>/auth/register.php">Sign Up here</a>.</p>
  </div>

  <script src="<?= $BASE ?>/assets/js/signin.js"></script>
</body>
</html>
