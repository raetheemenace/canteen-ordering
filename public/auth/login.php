<?php
// public/auth/login.php
session_start();
require_once __DIR__ . '/../../src/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Adjust this base path to match your actual project folder under htdocs
$BASE = '/canteen-ordering/public';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE student_number = ?");
    $stmt->execute([$student_number]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['student_number'] = $user['student_number'];

        if ($user['role'] === 'student') {
    header("Location: http://localhost{$BASE}/student/dashboard.php");
    exit;
} elseif ($user['role'] === 'admin') {
    header("Location: http://localhost{$BASE}/admin/dashboard.php");
    exit;
}

    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>T.I.P KainTeen! - Login</title>
  <link rel="stylesheet" href="<?= $BASE ?>/assets/css/signin.css">
</head>
<body>
  <video class="bg-video" autoplay loop muted>
    <source src="<?= $BASE ?>/assets/videos/bg.mp4" type="video/mp4">
  </video>

  <div class="container">
    <img src="<?= $BASE ?>/assets/images/logo.png" class="logo" alt="Logo">
    <h1>Sign In</h1>

    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="popup"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="popup error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label for="student_number">Student Number</label>
        <input type="text" id="student_number" name="student_number" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit">Sign In</button>
    </form>

    <p class="signin">Don’t have an account? 
      <a href="<?= $BASE ?>/auth/register.php">Register</a>
    </p>
  </div>
</body>
</html>
