<?php
session_start();
include '../db/db_conn.php';

$session_id = $_GET['session_id'] ?? '';
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'] ?? null;
$amount = floatval($_GET['amount'] ?? 0);

if (!$user_id || $amount <= 0) {
    header('Location: ../user/user_feed.php');
    exit;
}

$success_message = "";
$new_balance = 0;

try {
    $conn->begin_transaction();
    
    // 1. Add amount to user's wallet
    $update_sql = "UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("di", $amount, $user_id);
    $update_stmt->execute();
    
    // 2. Record transaction
    $trans_sql = "INSERT INTO wallet_transactions (user_id, type, amount, description, payment_id, status) 
                  VALUES (?, 'cash_in', ?, 'Wallet top-up via PayMongo', ?, 'completed')";
    $trans_stmt = $conn->prepare($trans_sql);
    $trans_stmt->bind_param("ids", $user_id, $amount, $session_id);
    $trans_stmt->execute();
    
    // 3. Get new balance
    $balance_sql = "SELECT wallet_balance FROM users WHERE id = ?";
    $balance_stmt = $conn->prepare($balance_sql);
    $balance_stmt->bind_param("i", $user_id);
    $balance_stmt->execute();
    $result = $balance_stmt->get_result();
    $user_data = $result->fetch_assoc();
    $new_balance = $user_data['wallet_balance'];
    
    $conn->commit();
    $success_message = "Successfully added ₱" . number_format($amount, 2) . " to your wallet!";
    
} catch (Exception $e) {
    $conn->rollback();
    $success_message = "Error: " . $e->getMessage();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash-In Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body text-center p-5">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                <h2 class="mt-3">Cash-In Successful!</h2>
                <p class="lead"><?= $success_message ?></p>
                
                <div class="wallet-info mt-4 p-4 bg-light rounded">
                    <h4>Wallet Balance</h4>
                    <div class="display-4 text-success">₱<?= number_format($new_balance, 2) ?></div>
                </div>
                
                <div class="action-buttons mt-5">
                    <a href="../user/wallet.php" class="btn btn-primary me-3">
                        <i class="fas fa-wallet me-2"></i> View Wallet
                    </a>
                    <a href="../user/user_feed.php" class="btn btn-secondary">
                        <i class="fas fa-book me-2"></i> Browse Books
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>