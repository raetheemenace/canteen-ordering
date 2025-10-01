<?php
// public/admin/dashboard.php
session_start();
require_once __DIR__ . '/../../src/database.php';
$BASE = '/canteen-ordering/public';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: {$BASE}/auth/login.php");
    exit;
}

$pdo = getPDO();
// quick stats
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalSales = $pdo->query("SELECT IFNULL(SUM(total_amount),0) FROM orders WHERE payment_status='paid'")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM inventory WHERE stock <= threshold")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard — TIP KainTeen</title>
<link rel="stylesheet" href="<?= $BASE ?>/assets/css/dashboard.css">
</head>
<body>
<video class="bg-video" autoplay muted loop>
  <source src="<?= $BASE ?>/assets/videos/bg.mp4" type="video/mp4">
</video>

<nav class="topbar">
  <div class="brand">TIP KainTeen — Admin</div>
  <div class="user">
    <span>Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
    <a class="btn-ghost" href="<?= $BASE ?>/auth/logout.php">Logout</a>
  </div>
</nav>

<main class="layout admin-layout">
  <aside class="sidebar admin-sidebar">
    <div class="card">
      <h3>Admin Menu</h3>
      <ul class="admin-nav">
        <li data-tab="dashboard" class="tab active">Overview</li>
        <li data-tab="orders" class="tab">Orders</li>
        <li data-tab="menu" class="tab">Menu Items</li>
        <li data-tab="inventory" class="tab">Inventory</li>
        <li data-tab="earnings" class="tab">Earnings</li>
      </ul>
    </div>
  </aside>

  <section class="content admin-content">
    <div id="tab-dashboard" class="tab-pane active">
      <h2>Overview</h2>
      <div class="stats">
        <div class="stat card">
          <h3>Total Orders</h3>
          <div class="stat-number"><?= (int)$totalOrders ?></div>
        </div>
        <div class="stat card">
          <h3>Total Sales</h3>
          <div class="stat-number">₱<?= number_format((float)$totalSales,2) ?></div>
        </div>
        <div class="stat card">
          <h3>Low Stock Items</h3>
          <div class="stat-number"><?= (int)$lowStock ?></div>
        </div>
      </div>
    </div>

    <div id="tab-orders" class="tab-pane">
      <h2>Orders</h2>
      <div id="orders-table">Loading orders...</div>
    </div>

    <div id="tab-menu" class="tab-pane">
      <h2>Menu Items</h2>
      <p>Use the Admin → Menu management pages (CRUD) — (placeholders for full CRUD)</p>
      <div id="menu-management">(Implement add/edit/delete pages)</div>
    </div>

    <div id="tab-inventory" class="tab-pane">
      <h2>Inventory</h2>
      <div id="inventory-list">Loading inventory...</div>
    </div>

    <div id="tab-earnings" class="tab-pane">
      <h2>Earnings</h2>
      <div id="earnings-report">Report generation coming here.</div>
    </div>

  </section>
</main>

<script>const BASE = '<?= $BASE ?>';</script>
<script src="<?= $BASE ?>/assets/js/admin_dashboard.js"></script>
</body>
</html>
