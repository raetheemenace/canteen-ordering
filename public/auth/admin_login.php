<?php
// public/auth/admin_login.php
session_start();
require_once __DIR__ . '/../../src/database.php';

$BASE = '/canteen-ordering/public';
$error = '';

// If already logged in as admin, go to dashboard
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
    header("Location: {$BASE}/admin/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($student_number === '' || $password === '') {
        $error = "Please enter both fields.";
    } else {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE student_number = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$student_number]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_number'] = $user['student_number'];
            $_SESSION['role'] = 'admin';

            // ✅ Redirect straight to dashboard
            header("Location: {$BASE}/admin/dashboard.php");
            exit;
        } else {
            $error = "Invalid admin credentials.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login — TIP KainTeen</title>
<link rel="stylesheet" href="<?= $BASE ?>/assets/css/signin.css">
</head>
<body>
<video class="bg-video" autoplay loop muted>
  <source src="<?= $BASE ?>/assets/videos/bg.mp4" type="video/mp4">
</video>

<div class="container">
  <img src="<?= $BASE ?>/assets/images/logo.png" class="logo" alt="Logo">
  <h1>Admin Sign In</h1>

  <?php if ($error): ?>
    <div class="popup error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="form-group">
      <label for="student-number">Admin ID (Student No.)</label>
      <input type="text" id="student-number" name="student_number" placeholder="7 Digit Admin No." required>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter Password" required>
    </div>

    <button type="submit">Sign In</button>
  </form>

  <p class="signin"><a href="<?= $BASE ?>/auth/login.php">Back to Student Login</a></p>
</div>
</body>
</html>
