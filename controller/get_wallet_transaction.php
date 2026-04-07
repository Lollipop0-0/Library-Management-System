<?php
// get_wallet_transactions.php
session_start();
require_once '../db/db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get wallet transactions with proper type mapping
    $sql = "SELECT 
                id,
                user_id,
                amount,
                type,
                description,
                reference_id,
                created_at,
                payment_id,
                status
            FROM wallet_transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        // Determine the transaction type based on description and amount
        $type = $row['type'];
        $description = $row['description'];
        
        // If type is empty, infer it from description
        if (empty($type)) {
            if (strpos($description, 'top-up') !== false || strpos($description, 'topup') !== false) {
                $type = 'topup';
            } elseif (strpos($description, 'sale') !== false) {
                $type = 'earning';
            } elseif (strpos($description, 'purchase') !== false) {
                $type = 'purchase';
            } else {
                $type = 'topup'; // default
            }
        }
        
        $transactions[] = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'type' => $type,
            'amount' => floatval($row['amount']),
            'description' => $description,
            'reference_id' => $row['reference_id'],
            'created_at' => $row['created_at'],
            'payment_id' => $row['payment_id'],
            'status' => $row['status']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'transactions' => $transactions
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to load transactions: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>