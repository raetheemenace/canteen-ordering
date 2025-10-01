<?php
// public/api/place_order.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']); exit;
}
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo json_encode(['success'=>false,'error'=>'Cart is empty']); exit;
}

require_once __DIR__ . '/../../src/database.php';
$pdo = getPDO();

try {
    $pdo->beginTransaction();
    $total = 0;
    foreach($cart as $it) $total += $it['qty'] * $it['price'];

    // create order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, status, payment_status, created_at) VALUES (?, ?, ?, 'pending', 'unpaid', NOW())");
    $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(1000,9999);
    $stmt->execute([$_SESSION['user_id'], $orderNumber, $total]);
    $orderId = $pdo->lastInsertId();

    // insert order items & decrement inventory
    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, qty, unit_price, subtotal, created_at) VALUES (?,?,?,?,?,NOW())");
    $invStmt = $pdo->prepare("UPDATE inventory SET stock = stock - ? WHERE menu_item_id = ? AND stock >= ?");
    foreach($cart as $it) {
        $subtotal = $it['qty'] * $it['price'];
        $itemStmt->execute([$orderId, $it['id'], $it['qty'], $it['price'], $subtotal]);
        // try to update inventory if exists; ignore if no inventory row
        $invStmt->execute([$it['qty'], $it['id'], $it['qty']]);
        if ($invStmt->rowCount() === 0) {
            // either no inventory or insufficient stock - we won't fail for now, but you can enforce
        }
    }

    $pdo->commit();
    // clear session cart
    unset($_SESSION['cart']);
    echo json_encode(['success'=>true,'order_number'=>$orderNumber,'order_id'=>$orderId]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
