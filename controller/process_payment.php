<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

use Dotenv\Dotenv;

require_once '../vendor/autoload.php';
require_once '../db/db_conn.php';

$projectRoot = dirname(__DIR__);
Dotenv::createImmutable($projectRoot)->safeLoad();

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid JSON body']);
    exit;
}

$name = trim($data['customer_name'] ?? '');
$email = trim($data['customer_email'] ?? '');
$amountPhp = floatval($data['amount_php'] ?? 0);
$type = $data['type'] ?? 'book_purchase';
$book_id = $data['book_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? $data['user_id'] ?? null;
$payment_method = $data['payment_method'] ?? 'paymongo';

// For book purchases, validate user_id and book_id
if ($type === 'book_purchase' && (!$user_id || !$book_id)) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing user_id or book_id for book purchase']);
    exit;
}

if (!$name || !$email || $amountPhp <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing/invalid fields (name, email, amount_php)']);
    exit;
}

// Check if user already owns the book
$check_ownership_sql = "SELECT * FROM user_library WHERE user_id = ? AND book_id = ?";
$check_stmt = $conn->prepare($check_ownership_sql);
$check_stmt->bind_param("ii", $user_id, $book_id);
$check_stmt->execute();
$existing_ownership = $check_stmt->get_result()->fetch_assoc();

if ($existing_ownership) {
    http_response_code(400);
    echo json_encode(['message' => 'You already own this book!']);
    exit;
}

// Handle wallet payment
// Handle WALLET payment (only for book purchases)
if ($payment_method === 'wallet' && $type === 'book_purchase') {
    // Check wallet balance
    $wallet_sql = "SELECT wallet_balance FROM users WHERE id = ?";
    $wallet_stmt = $conn->prepare($wallet_sql);
    $wallet_stmt->bind_param("i", $user_id);
    $wallet_stmt->execute();
    $wallet_result = $wallet_stmt->get_result();
    $user = $wallet_result->fetch_assoc();

    if (!$user || $user['wallet_balance'] < $amountPhp) {
        http_response_code(400);
        echo json_encode(['message' => 'Insufficient wallet balance']);
        exit;
    }

    // Process wallet payment
    try {
        $conn->begin_transaction();

        // Get book details
        $book_sql = "SELECT * FROM books WHERE id = ?";
        $book_stmt = $conn->prepare($book_sql);
        $book_stmt->bind_param("i", $book_id);
        $book_stmt->execute();
        $book = $book_stmt->get_result()->fetch_assoc();

        if (!$book) {
            throw new Exception('Book not found');
        }

        $author_earning = $amountPhp * 0.90;
        $platform_fee = $amountPhp * 0.10;

        // 1. Deduct from buyer's wallet
        $update_wallet_sql = "UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_wallet_sql);
        $update_stmt->bind_param("di", $amountPhp, $user_id);
        $update_stmt->execute();

        // 2. Record buyer's wallet transaction
        $wallet_transaction_sql = "INSERT INTO wallet_transactions (user_id, type, amount, description, status) 
                                   VALUES (?, 'purchase', ?, ?, 'completed')";
        $wallet_trans_stmt = $conn->prepare($wallet_transaction_sql);
        $desc = 'Book purchase: ' . $book['title'];
        $wallet_trans_stmt->bind_param("ids", $user_id, $amountPhp, $desc);
        $wallet_trans_stmt->execute();

        // 3. Create order - FIXED LINE
        $order_sql = "INSERT INTO orders (book_id, user_id, amount, payment_method, payment_status, payment_id, author_earning, platform_fee) 
                      VALUES (?, ?, ?, 'wallet', 'completed', ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $payment_id = 'WALLET_' . time() . '_' . $user_id;
        $order_stmt->bind_param("iidsdd", $book_id, $user_id, $amountPhp, $payment_id, $author_earning, $platform_fee);
        $order_stmt->execute();
        $order_id = $conn->insert_id;

        // 4. Add to user library
        $library_sql = "INSERT INTO user_library (user_id, book_id, order_id) VALUES (?, ?, ?)";
        $library_stmt = $conn->prepare($library_sql);
        $library_stmt->bind_param("iii", $user_id, $book_id, $order_id);
        $library_stmt->execute();

        // 5. Pay author (add to author's wallet)
        $author_sql = "UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?";
        $author_stmt = $conn->prepare($author_sql);
        $author_stmt->bind_param("di", $author_earning, $book['user_id']);
        $author_stmt->execute();

        // 6. Record author earning transaction
        $earning_sql = "INSERT INTO wallet_transactions (user_id, type, amount, description, status) 
                        VALUES (?, 'earning', ?, ?, 'completed')";
        $earning_stmt = $conn->prepare($earning_sql);
        $earning_desc = 'Book sale: ' . $book['title'];
        $earning_stmt->bind_param("ids", $book['user_id'], $author_earning, $earning_desc);
        $earning_stmt->execute();

        // 7. Update book total earnings
        $update_earning_sql = "UPDATE books SET total_earnings = total_earnings + ? WHERE id = ?";
        $update_earning_stmt = $conn->prepare($update_earning_sql);
        $update_earning_stmt->bind_param("di", $author_earning, $book_id);
        $update_earning_stmt->execute();

        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Payment completed successfully using wallet',
            'redirect_url' => '../user/my_library.php'
        ]);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['message' => 'Wallet payment failed: ' . $e->getMessage()]);
        exit;
    }
}

// Handle PayMongo payment
$amountInCentavos = intval(round($amountPhp * 100));
$secretKey = $_ENV['PAYMONGO_SECRET_KEY'] ?? '';

// Dynamic success URLs
$success_url = 'http://localhost/admin/success/payment_success.php?type=' . $type . '&book_id=' . $book_id . '&user_id=' . $user_id . '&session_id={CHECKOUT_SESSION_ID}&payment_method=paymongo';
$description = $type === 'book_purchase' ? 'Book Purchase: ' . ($data['book_title'] ?? 'Unknown Book') : 'Payment';

$payload = [
    'data' => [
        'attributes' => [
            'amount' => $amountInCentavos,
            'currency' => 'PHP',
            'line_items' => [
                [
                    'name' => $description,
                    'quantity' => 1,
                    'currency' => 'PHP',
                    'amount' => $amountInCentavos
                ]
            ],
            'payment_method_types' => ["gcash", "card", "grab_pay", "paymaya"],
            'success_url' => $success_url,
            'cancel_url' => 'http://localhost/user/payment_cancel.php',
            'metadata' => [
                'customer_name' => $name,
                'customer_email' => $email,
                'payment_type' => $type,
                'book_id' => $book_id,
                'user_id' => $user_id,
                'amount_php' => $amountPhp,
                'payment_method' => 'paymongo'
            ]
        ]
    ]
];

$ch = curl_init('https://api.paymongo.com/v1/checkout_sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ':');

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['message' => 'Request error: ' . $curlErr]);
    exit;
}

$resJson = json_decode($response, true);

if ($httpcode >= 200 && $httpcode < 300 && isset($resJson['data']['attributes']['checkout_url'])) {
    echo json_encode([
        'checkout_url' => $resJson['data']['attributes']['checkout_url'],
        'payment_method' => 'paymongo'
    ]);
    exit;
} else {
    http_response_code(max($httpcode, 500));
    $message = $resJson['errors'][0]['detail'] ?? $resJson['message'] ?? 'Unknown error from PayMongo';
    echo json_encode(['message' => 'PayMongo error: ' . $message]);
    exit;
}
?>
