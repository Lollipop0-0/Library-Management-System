<?php
session_start();
require_once '../db/db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$books = [];
$totalSpent = 0;

// Get books from user_library with order information
$query = "SELECT 
            b.id, b.title, b.author, b.publisher, b.genre, b.description, 
            b.pdf_path, b.price, b.is_free,
            u.fullname as author_name,
            ul.added_at as purchased_at,
            ul.order_id,
            o.payment_id,
            o.payment_method,
            o.amount as paid_amount
          FROM user_library ul
          JOIN books b ON ul.book_id = b.id 
          JOIN users u ON b.user_id = u.id 
          LEFT JOIN orders o ON ul.order_id = o.id
          WHERE ul.user_id = ? 
          ORDER BY ul.added_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);

// Calculate total spent from orders
$spent_sql = "SELECT SUM(amount) as total FROM orders WHERE user_id = ? AND payment_status = 'completed'";
$spent_stmt = $conn->prepare($spent_sql);
$spent_stmt->bind_param("i", $user_id);
$spent_stmt->execute();
$spent_result = $spent_stmt->get_result();
$spent_data = $spent_result->fetch_assoc();
$totalSpent = $spent_data['total'] ?? 0;

$conn->close();

// Create the libraryData array that your HTML expects
$libraryData = [
    'books' => $books,
    'totalBooks' => count($books),
    'totalSpent' => $totalSpent
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library - Libris Mind Verse</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../assets/libris.png">
    <link rel="stylesheet" href="../css/my-library-page.css">

</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo" onclick="location.href='user_feed.php'">
                <img src="../assets/libris.png" alt="Libris">
                <span>LIBRIS</span>
            </div>

            <nav class="nav-links">
                <a href="../user/user_feed.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="../user/genres.php" class="nav-link"><i class="fas fa-th-large"></i> Browse</a>
                <a href="../user/my_library.php" class="nav-link"><i class="fas fa-book-reader"></i> Purchased Books</a>
                <a href="../user/wallet.php" class="nav-link"><i class="fas fa-wallet"></i> Wallet</a>
                <a href="../user/profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
                <a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>

            <div class="header-actions">
                <a href="../user/profile.php" class="btn-publish">
                    <i class="fas fa-plus-circle"></i> Publish Book
                </a>

                <div class="user-profile" onclick="location.href='profile.php'">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="user_feed.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Feed
        </a>

        <div class="library-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?php echo $libraryData['totalBooks']; ?></div>
                <div class="stat-label">Books Owned</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-number">₱<?php echo number_format($libraryData['totalSpent'], 2); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>

        <div id="books-container">
            <?php if (!empty($libraryData['books'])): ?>
            <div class="books-grid">
                <?php foreach($libraryData['books'] as $book): ?>
                <div class="book-card">
                    <div class="book-header">
                        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                        <div class="purchase-badge"><i class="fas fa-check"></i> Owned</div>
                    </div>
                    
                    <div class="book-meta">
                        <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($book['author_name']); ?></div>
                        <div><i class="fas fa-building"></i> <?php echo htmlspecialchars($book['publisher']); ?></div>
                        <div><i class="fas fa-tag"></i> <?php echo htmlspecialchars($book['genre']); ?></div>
                        <div><i class="fas fa-calendar"></i> Purchased: <?php echo date('M j, Y', strtotime($book['purchased_at'])); ?></div>
                        <?php if(isset($book['price']) && floatval($book['price']) > 0): ?>
                        <div><i class="fas fa-receipt"></i> Price: ₱<?php echo number_format($book['price'], 2); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-description">
                        <?php echo htmlspecialchars($book['description']); ?>
                    </div>
                    
                    <div class="book-actions">
                        <?php if(!empty($book['pdf_path'])): ?>
                        <a href="../controller/download.php?id=<?php echo $book['id']; ?>" class="btn-action btn-download">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                        <?php else: ?>
                        <button class="btn-action" style="background: #ccc; cursor: not-allowed;" disabled>
                            <i class="fas fa-file-pdf"></i> No PDF Available
                        </button>
                        <?php endif; ?>
                        
                        <?php if(!empty($book['order_id'])): ?>
                        <a href="../controller/generate_receipt.php?order_id=<?php echo $book['order_id']; ?>" 
                           class="btn-action btn-receipt" 
                           target="_blank">
                            <i class="fas fa-file-invoice"></i> Download Receipt
                        </a>
                        <?php else: ?>
                        <button class="btn-action" style="background: #ccc; cursor: not-allowed;" disabled>
                            <i class="fas fa-file-invoice"></i> No Receipt
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-books">
                <i class="fas fa-book-open"></i>
                <p>You haven't purchased any books yet.</p>
                <a href="user_feed.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600; margin-top: 15px; display: inline-block;">
                    Browse Available Books <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


