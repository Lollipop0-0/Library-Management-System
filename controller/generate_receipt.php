<?php
session_start();
require_once '../db/db_conn.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access');
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    die('Order ID is required');
}

// Get order details
$sql = "SELECT 
            o.id as order_id,
            o.book_id,
            o.amount,
            o.payment_status,
            o.payment_id,
            o.payment_method,
            o.created_at,
            o.author_earning,
            o.platform_fee,
            b.title as book_title,
            b.author as book_author,
            b.publisher,
            b.genre,
            b.price,
            seller.fullname as seller_name,
            seller.email as seller_email,
            buyer.fullname as buyer_name,
            buyer.email as buyer_email
        FROM orders o
        JOIN books b ON o.book_id = b.id
        JOIN users seller ON b.user_id = seller.id
        JOIN users buyer ON o.user_id = buyer.id
        WHERE o.id = ? AND o.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die('Order not found or access denied');
}

$conn->close();

// Generate receipt HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $order['order_id']; ?> - Libris Mind Verse</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .receipt-header h1 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .receipt-header .subtitle {
            color: #666;
            font-size: 0.9rem;
        }
        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-section h3 {
            color: #333;
            font-size: 1rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-section p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 5px;
        }
        .receipt-details {
            margin-bottom: 30px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #666;
            font-weight: 600;
        }
        .detail-value {
            color: #333;
            font-weight: 600;
        }
        .book-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .book-details h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .book-meta {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            color: #666;
        }
        .book-meta strong {
            color: #333;
        }
        .amount-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        .amount-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
            font-size: 1.5rem;
            font-weight: 700;
        }
        .receipt-footer {
            text-align: center;
            color: #999;
            font-size: 0.85rem;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-completed {
            background: #4CAF50;
            color: white;
        }
        .status-pending {
            background: #FF9800;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .action-button:hover {
            background: #764ba2;
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        .action-button.print {
            background: #4CAF50;
        }
        .action-button.print:hover {
            background: #45a049;
        }
        .action-button.books {
            background: #FF9800;
        }
        .action-button.books:hover {
            background: #f57c00;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                padding: 20px;
            }
            .action-buttons {
                display: none;
            }
        }
        @media (max-width: 600px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            .action-button {
                width: 200px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>📚 Libris Mind Verse</h1>
            <p class="subtitle">Digital Book Purchase Receipt</p>
        </div>

        <div class="receipt-info">
            <div class="info-section">
                <h3>Receipt Information</h3>
                <p><strong>Order ID:</strong> #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['payment_id']); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </p>
            </div>

            <div class="info-section">
                <h3>Buyer Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['buyer_email']); ?></p>
            </div>
        </div>

        <div class="book-details">
            <h3>Book Details</h3>
            <div class="book-meta">
                <strong>Title:</strong>
                <span><?php echo htmlspecialchars($order['book_title']); ?></span>
                
                <strong>Author:</strong>
                <span><?php echo htmlspecialchars($order['book_author']); ?></span>
                
                <strong>Publisher:</strong>
                <span><?php echo htmlspecialchars($order['publisher']); ?></span>
                
                <strong>Genre:</strong>
                <span><?php echo htmlspecialchars($order['genre']); ?></span>
                
                <strong>Seller:</strong>
                <span><?php echo htmlspecialchars($order['seller_name']); ?></span>
            </div>
        </div>

        <div class="amount-section">
            <div class="amount-row">
                <span>Book Price:</span>
                <span>₱<?php echo number_format($order['price'], 2); ?></span>
            </div>
            <?php if ($order['platform_fee'] > 0): ?>
            <?php endif; ?>
            <div class="amount-total">
                <span>Total Paid:</span>
                <span>₱<?php echo number_format($order['amount'], 2); ?></span>
            </div>
        </div>

        <div class="receipt-footer">
            <p><strong>Thank you for your purchase!</strong></p>
            <p>This is an official receipt for your digital book purchase.</p>
            <p>You can access your book anytime from your library.</p>
            <p style="margin-top: 15px; font-size: 0.75rem;">
                Libris Mind Verse - Digital Book Platform<br>
                For support, please contact: support@librismindverse.com
            </p>
        </div>

        <div class="action-buttons">
            <button class="action-button print" onclick="window.print()">
                🖨️ Print Receipt
            </button>
            <a href="../user/my_library.php" class="action-button books">
                📚 Back to My Books
            </a>
        </div>
    </div>

    <script>
        // Auto-focus for printing
        window.onload = function() {
            // Optional: Auto-print when page loads
            // window.print();
        };
    </script>
</body>
</html>