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
    <style>
         :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #d4af37;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
            --dark-color: #2c3e50;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            color: var(--dark-color);
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            cursor: pointer;
        }

        .logo img {
            height: 45px;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .nav-link {
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-publish {
            background: var(--danger-color);
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .btn-publish:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
            color: white;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: color 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            color: #f0f0f0;
            background: rgba(255, 255, 255, 0.3);
        }

        /* Rest of your existing my_library.php styles */
        .library-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .book-card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
            border-top: 4px solid var(--primary-color);
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .book-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .book-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .purchase-badge {
            background: var(--primary-gradient);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .book-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .book-meta div {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .book-meta i {
            width: 20px;
            color: var(--primary-color);
        }

        .book-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-action {
            min-width: 130px;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: block;
            font-size: 0.9rem;
        }

        .btn-download {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-receipt {
            background: var(--primary-gradient);
            color: white;
            opacity: 0.9;
        }

        .btn-receipt:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
            opacity: 1;
        }

        .no-books {
            background: white;
            padding: 60px;
            border-radius: 8px;
            text-align: center;
            box-shadow: var(--card-shadow);
            grid-column: 1 / -1;
        }

        .no-books i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-books p {
            color: #666;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .header-container {
                flex-wrap: wrap;
            }

            .nav-links {
                display: none;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }

            .library-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
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