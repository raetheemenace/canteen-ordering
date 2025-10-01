<?php
// public/auth/login.php (robust include + debug)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

// Locate src/database.php reliably
$possible_db_paths = [
    __DIR__ . '/../../src/database.php', 
    __DIR__ . '/../src/database.php',
    __DIR__ . '/../../../src/database.php',
];

$db_file = null;
foreach ($possible_db_paths as $p) {
    if (file_exists($p)) { $db_file = $p; break; }
}
if (!$db_file) {
    http_response_code(500);
    echo "<h2>Configuration error</h2>";
    echo "<p>Could not find <code>src/database.php</code> — tried paths:</p><pre>" . htmlspecialchars(implode("\n", $possible_db_paths)) . "</pre>";
    exit;
}
require_once $db_file;
if (!function_exists('getPDO')) {
    http_response_code(500);
    echo "<h2>Configuration error</h2><p><code>getPDO()</code> not found in {$db_file}.</p>";
    exit;
}

$errors = [];
$isAjax = (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^\d{7}$/', $student_number)) {
        $errors[] = "Student number must be exactly 7 digits.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid TIP email.";
    }
    if ($password === '') {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE student_number = ? AND email = ? LIMIT 1");
            $stmt->execute([$student_number, $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['student_number'] = $user['student_number'] ?? $user['username'] ?? '';
                $_SESSION['role'] = $user['role'] ?? 'student';

                $redirect = ($_SESSION['role'] === 'admin') ? '/canteen-ordering/public/admin/dashboard.php' : '/canteen-ordering/public/student/dashboard.php';

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success'=>true,'redirect'=>$redirect]);
                    exit;
                } else {
                    header("Location: $redirect");
                    exit;
                }
            } else {
                $errors[] = "Invalid credentials.";
            }
        } catch (Exception $e) {
            $errors[] = "DB error: " . $e->getMessage();
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success'=>false,'errors'=>$errors]);
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign In - TIP KainTeen</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/canteen-ordering/public/assets/css/signin.css">
</head>
<body>
  <video class="bg-video" autoplay loop muted>
    <source src="/canteen-ordering/public/assets/videos/bg.mp4" type="video/mp4">
  </video>

  <div class="container">
    <img src="/canteen-ordering/public/assets/images/logo.png" class="logo" alt="Logo">
    <p class="subtitle">From Click to Kain in No Time!</p>
    <h1>Sign In</h1>

    <?php if (!empty($errors)): ?>
      <div class="popup error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="form-group">
        <label for="student_number">Student Number</label>
        <input type="text" id="student_number" name="student_number" placeholder="7 Digit Student No." required pattern="\d{7}">
      </div>

      <div class="form-group">
        <label for="email">TIP Email</label>
        <input type="email" id="email" name="email" placeholder="Enter TIP Email" required>
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
