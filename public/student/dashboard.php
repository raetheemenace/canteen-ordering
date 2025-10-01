<?php
// public/student/dashboard.php
session_start();
require_once __DIR__ . '/../../src/database.php';

// base URL used in asset/endpoint references (adjust when public is document root)
$BASE = '/canteen-ordering/public';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header("Location: {$BASE}/auth/login.php");
    exit;
}

$pdo = getPDO();

// fetch categories and items
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$itemsStmt = $pdo->prepare("SELECT m.*, c.name AS category FROM menu_items m LEFT JOIN categories c ON m.category_id = c.id WHERE m.is_active = 1 ORDER BY m.name");
$itemsStmt->execute();
$menu_items = $itemsStmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Dashboard — TIP KainTeen</title>
<link rel="stylesheet" href="<?= $BASE ?>/assets/css/dashboard.css">
</head>
<body>
<video class="bg-video" autoplay muted loop>
  <source src="<?= $BASE ?>/assets/videos/bg.mp4" type="video/mp4">
</video>

<nav class="topbar">
  <div class="brand">TIP KainTeen</div>
  <div class="user">
    <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
    <a class="btn-ghost" href="<?= $BASE ?>/auth/logout.php">Logout</a>
  </div>
</nav>

<main class="layout">
  <aside class="sidebar">
    <div class="card">
      <h3>Categories</h3>
      <ul id="categories">
        <li data-cat="all" class="cat active">All</li>
        <?php foreach($categories as $cat): ?>
          <li data-cat="<?= $cat['id'] ?>" class="cat"><?= htmlspecialchars($cat['name']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="card cart-card">
      <h3>Your Cart</h3>
      <div id="cart-items">Loading...</div>
      <div class="cart-footer">
        <strong>Total: ₱<span id="cart-total">0.00</span></strong>
        <button id="checkoutBtn" class="btn-primary">Checkout</button>
      </div>
    </div>

    <div class="card">
      <h3>Order History</h3>
      <div id="order-history">(Your previous orders will appear here)</div>
    </div>
  </aside>

  <section class="content">
    <h2>Menu</h2>
    <div class="grid" id="menu-grid">
      <?php foreach($menu_items as $m): ?>
        <article class="menu-card" data-category="<?= $m['category_id'] ?>">
          <img src="<?= $BASE ?>/assets/images/<?= htmlspecialchars(basename($m['image_path'] ?? 'placeholder.png')) ?>" alt="<?= htmlspecialchars($m['name']) ?>">
          <div class="menu-body">
            <h4><?= htmlspecialchars($m['name']) ?></h4>
            <p class="muted"><?= htmlspecialchars($m['category']) ?></p>
            <div class="price">₱<?= number_format($m['price'],2) ?></div>
            <div class="actions">
              <input type="number" min="1" value="1" class="qty" />
              <button class="btn-add" data-id="<?= $m['id'] ?>" data-name="<?= htmlspecialchars($m['name']) ?>" data-price="<?= $m['price'] ?>">Add</button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<script>
const BASE = '<?= $BASE ?>';
</script>
<script src="<?= $BASE ?>/assets/js/student_dashboard.js"></script>
</body>
</html>
