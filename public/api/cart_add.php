<?php
// public/api/cart_add.php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['id'])) {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit;
}
$id = (int)$input['id'];
$qty = max(1, (int)($input['qty'] ?? 1));

// optionally check DB for item existence/price
require_once __DIR__ . '/../../src/database.php';
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id, name, price FROM menu_items WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) {
    echo json_encode(['success'=>false,'error'=>'Item not found']); exit;
}

$cart = $_SESSION['cart'] ?? [];
if (isset($cart[$id])) $cart[$id]['qty'] += $qty;
else $cart[$id] = ['id'=>$id,'name'=>$item['name'],'qty'=>$qty,'price'=>$item['price']];
$_SESSION['cart'] = $cart;

echo json_encode(['success'=>true,'cartCount'=>count($cart)]);
