<?php
// public/api/cart_get.php
session_start();
header('Content-Type: application/json');

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0.0;
foreach ($cart as $id => $it) {
    $subtotal = $it['qty'] * $it['price'];
    $items[] = ['id'=>$id,'name'=>$it['name'],'qty'=>$it['qty'],'unit_price'=>$it['price'],'subtotal'=>$subtotal];
    $total += $subtotal;
}
echo json_encode(['items'=>$items,'total'=>$total]);
