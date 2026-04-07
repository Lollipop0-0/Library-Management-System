<?php
session_start();
require_once '../db/db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT wallet_balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo json_encode([
        'status' => 'success',
        'balance' => floatval($user['wallet_balance'])
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}

$stmt->close();
$conn->close();
?>