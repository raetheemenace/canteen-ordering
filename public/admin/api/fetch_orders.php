<?php
// public/admin/api/fetch_orders.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['error'=>'unauthorized']); exit;
}
require_once __DIR__ . '/../../../src/database.php';
$pdo = getPDO();

$q = $pdo->query("SELECT o.*, u.username, u.full_name FROM orders o LEFT JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT 200");
$rows = $q->fetchAll();
echo json_encode($rows);
