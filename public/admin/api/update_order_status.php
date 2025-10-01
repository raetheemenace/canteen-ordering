<?php
// public/admin/api/update_order_status.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success'=>false,'error'=>'unauthorized']); exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$status = $data['status'] ?? '';

if (!$id || !$status) { echo json_encode(['success'=>false,'error'=>'invalid']); exit; }

require_once __DIR__ . '/../../../src/database.php';
$pdo = getPDO();
$stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
$res = $stmt->execute([$status, $id]);
echo json_encode(['success'=> (bool)$res]);
