<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['is_admin']) {
    header('Location: ../admin/admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libris Mind Verse - Digital Library</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../assets/libris.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        /* Hero Section */
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            position: relative;
        }

        .book-preview {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .book-preview i {
            font-size: 5rem;
            color: var(--primary-color);
        }

        .book-info h3 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .book-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .price-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 2px solid var(--light-bg);
        }

        .price-info {
            display: flex;
            flex-direction: column;
        }

        .price-label {
            font-size: 0.8rem;
            color: #666;
        }

        .price-amount {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--danger-color);
        }

        .monthly-price {
            font-size: 0.9rem;
            color: #666;
        }

        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 2rem;
            background: transparent;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: bold;
            color: white;
        }

        .view-all-btn {
            color: white;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .view-all-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        /* Books Grid */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .book-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--hover-shadow);
        }

        .book-image {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #667eea30 0%, #764ba230 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .book-image i {
            font-size: 4rem;
            color: var(--primary-color);
        }

        .badge-container {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            color: white;
            width: fit-content;
        }

        .badge-release {
            background: var(--warning-color);
        }

        .badge-category {
            background: var(--primary-color);
        }

        .badge-free {
            background: var(--success-color);
        }

        .badge-paid {
            background: var(--danger-color);
        }

        .book-content {
            padding: 1.5rem;
        }

        .book-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-author {
            color: #666;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .book-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
        }

        .book-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .book-price {
            display: flex;
            flex-direction: column;
        }

        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--danger-color);
        }

        .price-per-month {
            font-size: 0.8rem;
            color: #666;
        }

        .book-action {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .book-action:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .book-action.free {
            background: var(--success-color);
        }

        .book-action.free:hover {
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }

        /* Categories Section */
        .categories-section {
            background: white;
            padding: 3rem 2rem;
            margin: 3rem 0;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
        }

        .categories-section .section-title {
            color: var(--dark-color);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
        }

        .category-card {
            background: var(--light-bg);
            padding: 2rem 1rem;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .category-card:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-5px);
        }

        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .category-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .category-count {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Benefits Section */
        .benefits-section {
            background: var(--dark-color);
            color: white;
            padding: 4rem 2rem;
            margin: 3rem 0;
            border-radius: 15px;
        }

        .benefits-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .benefit-card {
            text-align: center;
        }

        .benefit-icon {
            width: 70px;
            height: 70px;
            background: var(--danger-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
        }

        .benefit-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        /* Modal Styles - Updated from profile.php */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: bold;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #444;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            transition: border 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .price-message {
            margin-top: 0.5rem;
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-container {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }

            .header-container {
                flex-wrap: wrap;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
        }

        .loading {
            text-align: center;
            padding: 3rem;
            color: white;
            grid-column: 1 / -1;
        }

        .loading i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .no-books {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            grid-column: 1 / -1;
        }

        .no-books i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
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
                <button class="btn-publish" onclick="showModal('publishModal')">
                    <i class="fas fa-plus-circle"></i> Publish Book
                </button>

                <div class="user-profile" onclick="location.href='profile.php'">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Read to Own Collections</h1>
                <p>Acquire your dream books with a Same-Day Process & immediate access. Featuring a read till you own
                    offer.</p>
                <button class="btn-publish" onclick="location.href='genres.php'" style="width: fit-content;">
                    Go to Book Listing <i class="fas fa-arrow-right"></i>
                </button>
            </div>

            <div class="hero-card" id="featuredBookCard">
                <div class="featured-badge">
                    <i class="fas fa-star"></i> Same Day Release
                </div>
                <div class="book-preview">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="book-info">
                    <h3>Loading...</h3>
                    <div class="book-meta">
                        <span><i class="fas fa-book"></i> Genre • <i class="fas fa-user"></i> Author</span>
                    </div>
                </div>
                <div class="price-section">
                    <div class="price-info">
                        <span class="price-label">Downpayment</span>
                        <span class="price-amount">₱0.00</span>
                    </div>
                    <div class="price-info text-end">
                        <span class="price-label">Monthly Access</span>
                        <span class="monthly-price">₱0.00</span>
                        <small>Unlimited Reading</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Latest Units -->
        <div class="section-header">
            <h2 class="section-title">Latest Books</h2>
            <a href="genres.php" class="view-all-btn">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="books-grid" id="latestBooks">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading latest books...</p>
            </div>
        </div>

        <!-- Categories Section -->
        <div class="categories-section">
            <div class="section-header">
                <h2 class="section-title">Browse by Genre</h2>
            </div>
            <div class="categories-grid" id="categoriesGrid">
                <!-- Categories will load here -->
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="benefits-container">
            <h2 class="section-title text-center mb-4">Why Choose Libris Mind Verse?</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="benefit-title">One Day Process</div>
                    <p>Get instant access to books with our streamlined approval system</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-clock"></i></div>
                    <div class="benefit-title">Same Day Release</div>
                    <p>Published books are available immediately after approval</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="benefit-title">No Bank Approval</div>
                    <p>Simple wallet-based payment system with no credit checks</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-sparkles"></i></div>
                    <div class="benefit-title">Brand New Content</div>
                    <p>Fresh books from talented authors worldwide</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-headset"></i></div>
                    <div class="benefit-title">After-Sales Support</div>
                    <p>24/7 customer support for all your reading needs</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Publish Book Modal -->
    <div class="modal" id="publishModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Publish New Book</div>
                <button class="close-modal" onclick="closeModal('publishModal')">&times;</button>
            </div>

            <div class="modal-body">
                <form id="bookForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Book Title *</label>
                        <input type="text" id="title" class="form-control" required placeholder="Enter book title">
                    </div>

                    <div class="form-group">
                        <label for="author">Author *</label>
                        <input type="text" id="author" class="form-control" required placeholder="Enter author name">
                    </div>

                    <div class="form-group">
                        <label for="publisher">Publisher *</label>
                        <input type="text" id="publisher" class="form-control" required
                            placeholder="e.g., Penguin Books">
                    </div>

                    <div class="form-group">
                        <label for="genre">Genre *</label>
                        <select id="genre" class="form-control" required>
                            <option value="">Select Genre</option>
                            <option value="Fiction">Fiction</option>
                            <option value="Sci-Fi">Science Fiction</option>
                            <option value="Mystery">Mystery</option>
                            <option value="Romance">Romance</option>
                            <option value="Fantasy">Fantasy</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Biography">Biography</option>
                            <option value="History">History</option>
                            <option value="Thriller">Thriller</option>
                            <option value="Horror">Horror</option>
                            <option value="Young Adult">Young Adult</option>
                            <option value="Children">Children</option>
                            <option value="Poetry">Poetry</option>
                            <option value="Drama">Drama</option>
                            <option value="Comedy">Comedy</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Book Description *</label>
                        <textarea id="description" class="form-control" required
                            placeholder="Describe what your book is about..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Book Price (PHP) *</label>
                        <input type="number" id="price" class="form-control" min="0" step="0.01" value="0" required
                            placeholder="Enter 0 for free book">
                        <small style="color: #666; display: block; margin-top: 5px;">
                            • <strong>Free</strong>: Set price to 0 (anyone can download)<br>
                            • <strong>Paid</strong>: Set price above 0 (users must purchase)
                        </small>
                        <div id="priceMessage" class="price-message"></div>
                    </div>

                    <div class="form-group">
                        <label for="pdf_file">Book PDF (Optional, max 10MB)</label>
                        <input type="file" id="pdf_file" class="form-control" accept=".pdf">
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    const CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;
    const CURRENT_USER_NAME = '<?php echo $_SESSION['fullname']; ?>';
    const CURRENT_USER_EMAIL = '<?php echo $_SESSION['email']; ?>';

    // Modal Functions
    function showModal(modalId) {
        $('#' + modalId).fadeIn();
    }

    function closeModal(modalId) {
        $('#' + modalId).fadeOut();
        clearForm();
    }

    function clearForm() {
        $('#bookForm')[0].reset();
        $('#price').val('0');
        $('#price').trigger('input');
    }

    $(document).ready(function() {
        loadAllBooks();
        loadCategories();
        
        // Price input feedback
        $('#price').on('input', function() {
            const price = parseFloat($(this).val()) || 0;
            const priceMessage = $('#priceMessage');
            if (price === 0) {
                priceMessage.html('<span style="color: #2ecc71;"><i class="fas fa-check-circle"></i> This book will be <strong>FREE</strong> for everyone</span>');
                priceMessage.css('background', '#d4edda');
            } else if (price < 0) {
                priceMessage.html('<span style="color: #e74c3c;"><i class="fas fa-times-circle"></i> Price cannot be negative</span>');
                priceMessage.css('background', '#f8d7da');
            } else {
                const authorEarning = (price * 1).toFixed(2);
                priceMessage.html(`<span style="color: #667eea;">
                    <i class="fas fa-info-circle"></i> This book will be <strong>PAID</strong><br>
                    • Your earning: <strong>₱${authorEarning}</strong><br>
                </span>`);
                priceMessage.css('background', '#e3f2fd');
            }
        });
        $('#price').trigger('input');
    });

    $('#bookForm').submit(function (e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('title', $('#title').val());
        formData.append('author', $('#author').val());
        formData.append('publisher', $('#publisher').val());
        formData.append('genre', $('#genre').val());
        formData.append('description', $('#description').val());
        formData.append('pdf_file', $('#pdf_file')[0].files[0]);

        const price = parseFloat($('#price').val()) || 0;
        formData.append('price', price);
        formData.append('is_free', price === 0 ? '1' : '0');

        if (price < 0) {
            Swal.fire('Error', 'Price cannot be negative', 'error');
            return;
        }

        Swal.fire({
            title: 'Publishing Book...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '../controller/crud.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        closeModal('publishModal');
                        loadAllBooks();
                        loadCategories();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function () {
                Swal.close();
                Swal.fire('Error', 'An error occurred while publishing the book.', 'error');
            }
        });
    });

    function loadAllBooks() {
        $.ajax({
            url: '../controller/crud.php',
            method: 'GET',
            data: { action: 'read' },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success' && response.data) {
                    const books = response.data;

                    if (books.length > 0) {
                        displayFeaturedHero(books[0]);
                    }

                    displayLatestBooks(books.slice(0, 6));
                    displayFeaturedBooks(books.slice(6, 12));
                } else {
                    showEmptyState('#latestBooks');
                    showEmptyState('#featuredBooks');
                }
            },
            error: function () {
                showEmptyState('#latestBooks');
                showEmptyState('#featuredBooks');
            }
        });
    }

    function displayFeaturedHero(book) {
        const isFree = book.is_free == 1 || parseFloat(book.price || 0) <= 0;
        const price = parseFloat(book.price || 0);

        $('#featuredBookCard').html(`
            <div class="featured-badge">
                <i class="fas fa-star"></i> ${isFree ? 'Free Book' : 'Premium Book'}
            </div>
            <div class="book-preview">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="book-info">
                <h3>${escapeHtml(book.title)}</h3>
                <div class="book-meta">
                    <span><i class="fas fa-tag"></i> ${escapeHtml(book.genre)} • <i class="fas fa-user"></i> ${escapeHtml(book.author)}</span>
                </div>
            </div>
            <div class="price-section">
                <div class="price-info">
                    <span class="price-label">${isFree ? 'Cost' : 'Downpayment'}</span>
                    <span class="price-amount">${isFree ? 'FREE' : '₱' + price.toFixed(2)}</span>
                </div>
                <div class="price-info text-end">
                    <span class="price-label">Access</span>
                    <span class="monthly-price">${isFree ? 'Free Forever' : 'One-time Payment'}</span>
                    <small>${isFree ? 'No hidden fees' : 'Unlimited reading'}</small>
                </div>
            </div>
        `);
    }

    function displayLatestBooks(books) {
        const container = $('#latestBooks');

        if (!books || books.length === 0) {
            showEmptyState(container);
            return;
        }

        let html = '';
        books.forEach(book => {
            html += createBookCard(book, true);
        });

        container.html(html);
        attachBookEvents();
    }

    function createBookCard(book, isLatest) {
        const isFree = book.is_free == 1 || parseFloat(book.price || 0) <= 0;
        const price = parseFloat(book.price || 0);
        const isOwner = book.user_id == CURRENT_USER_ID;

        return `
            <div class="book-card" data-book-id="${book.id}">
                <div class="book-image">
                    <i class="fas fa-book"></i>
                    <div class="badge-container">
                        ${isLatest ? '<span class="badge badge-release">New Release</span>' : ''}
                        <span class="badge badge-category">${escapeHtml(book.genre)}</span>
                        <span class="badge ${isFree ? 'badge-free' : 'badge-paid'}">
                            ${isFree ? 'FREE' : '₱' + price.toFixed(2)}
                        </span>
                    </div>
                </div>
                <div class="book-content">
                    <h3 class="book-title">${escapeHtml(book.title)}</h3>
                    <div class="book-author">
                        <i class="fas fa-user"></i>
                        ${escapeHtml(book.author)}
                    </div>
                    <p class="book-description">${escapeHtml(book.description || 'No description available.')}</p>
                    <div class="book-footer">
                        <div class="book-price">
                            ${isFree ?
                '<span class="price-tag">FREE</span>' :
                `<span class="price-tag">₱${price.toFixed(2)}</span>
                                 <span class="price-per-month">One-time</span>`
            }
                        </div>
                        ${book.pdf_path ?
                (isFree || isOwner ?
                    `<a href="../controller/download.php?id=${book.id}" class="book-action free">
                                <i class="fas fa-download"></i> Download
                            </a>` :
                    `<button class="book-action buy-btn" 
                                data-book-id="${book.id}" 
                                data-book-title="${escapeHtml(book.title)}" 
                                data-amount="${price}">
                                <i class="fas fa-shopping-cart"></i> Buy Now
                            </button>`
                ) :
                '<button class="book-action" disabled style="opacity: 0.5;">No PDF</button>'
            }
                    </div>
                </div>
            </div>
        `;
    }

    function loadCategories() {
        $.ajax({
            url: '../controller/get_genres.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success' && response.data) {
                    displayCategories(response.data);
                }
            },
            error: function () {
                console.error('Error loading categories');
            }
        });
    }

    function displayCategories(genres) {
        const container = $('#categoriesGrid');
        const icons = {
            'Fiction': 'fa-book',
            'Sci-Fi': 'fa-rocket',
            'Mystery': 'fa-search',
            'Romance': 'fa-heart',
            'Fantasy': 'fa-dragon',
            'Non-Fiction': 'fa-file-alt',
            'Biography': 'fa-user',
            'History': 'fa-landmark',
            'Thriller': 'fa-running',
            'Horror': 'fa-ghost',
            'Young Adult': 'fa-users',
            'Children': 'fa-child',
            'Poetry': 'fa-feather',
            'Drama': 'fa-theater-masks',
            'Comedy': 'fa-laugh'
        };

        let html = '';
        genres.forEach(genre => {
            const icon = icons[genre.genre] || 'fa-book';
            html += `
                <div class="category-card" onclick="location.href='genres.php?genre=${encodeURIComponent(genre.genre)}'">
                    <div class="category-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="category-name">${escapeHtml(genre.genre)}</div>
                    <div class="category-count">${genre.count} books</div>
                </div>
            `;
        });

        container.html(html);
    }

    function attachBookEvents() {
        $('.buy-btn').off('click').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const bookId = $(this).data('book-id');
            const bookTitle = $(this).data('book-title');
            const amount = $(this).data('amount');
            confirmBookPurchase(bookId, bookTitle, amount);
        });
    }

    function confirmBookPurchase(bookId, bookTitle, amount) {
        Swal.fire({
            title: 'Confirm Purchase',
            html: `
                <div class="text-start">
                    <p><strong>Book:</strong> ${bookTitle}</p>
                    <p><strong>Price:</strong> ₱${parseFloat(amount).toFixed(2)}</p>
                    <p><strong>Payment Method:</strong> Wallet</p>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> 
                        This amount will be deducted from your wallet balance.
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-shopping-cart me-2"></i>Confirm Purchase',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                initiateBookPurchase(bookId, bookTitle, amount);
            }
        });
    }

    function initiateBookPurchase(bookId, bookTitle, amount) {
        const userData = {
            customer_name: CURRENT_USER_NAME,
            customer_email: CURRENT_USER_EMAIL,
            amount_php: parseFloat(amount),
            type: 'book_purchase',
            book_id: bookId,
            book_title: bookTitle,
            user_id: CURRENT_USER_ID,
            payment_method: 'wallet'
        };

        checkWalletBalance().then(walletBalance => {
            if (walletBalance >= amount) {
                processPayment(userData);
            } else {
                showInsufficientBalancePrompt(bookTitle, amount, walletBalance);
            }
        }).catch(error => {
            showInsufficientBalancePrompt(bookTitle, amount, 0);
        });
    }

    function checkWalletBalance() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '../controller/get_wallet_balance.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        resolve(response.balance);
                    } else {
                        reject('Failed to get wallet balance');
                    }
                },
                error: function () {
                    reject('Error fetching wallet balance');
                }
            });
        });
    }

    function showInsufficientBalancePrompt(bookTitle, amount, walletBalance) {
        const neededAmount = (amount - walletBalance).toFixed(2);
        Swal.fire({
            title: 'Insufficient Wallet Balance',
            html: `
                <div class="text-start">
                    <p><strong>Book:</strong> ${bookTitle}</p>
                    <p><strong>Price:</strong> ₱${parseFloat(amount).toFixed(2)}</p>
                    <p><strong>Your Balance:</strong> ₱${walletBalance.toFixed(2)}</p>
                    <p class="text-danger"><strong>Additional Needed:</strong> ₱${neededAmount}</p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i> 
                        You don't have enough balance to purchase this book.
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-wallet me-2"></i>Cash In Now',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'wallet.php';
            }
        });
    }

    function processPayment(data) {
        Swal.fire({
            title: "Processing payment...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: "../controller/process_payment.php",
            method: "POST",
            data: JSON.stringify(data),
            contentType: "application/json",
            dataType: "json",
            success: function (res) {
                Swal.close();
                if (res.status === 'success') {
                    showPaymentReceipt(res.receipt_data || {
                        book_title: data.book_title,
                        amount: data.amount_php,
                        payment_method: 'wallet',
                        transaction_id: 'WALLET_' + Date.now(),
                        date: new Date().toLocaleString(),
                        customer_name: data.customer_name,
                        customer_email: data.customer_email
                    });
                } else {
                    Swal.fire("Error", res.message || "Payment processing failed.", "error");
                }
            },
            error: function (xhr) {
                Swal.close();
                let msg = "Server error";
                try {
                    const response = xhr.responseJSON || JSON.parse(xhr.responseText);
                    msg = response.message || "Payment processing failed";
                } catch (e) {
                    msg = xhr.responseText || "Payment processing failed";
                }
                Swal.fire("Error", msg, "error");
            }
        });
    }

    function showPaymentReceipt(receiptData) {
        const receiptHtml = `
            <div class="receipt-container text-start" style="max-width: 100%;">
                <div class="receipt-header text-center mb-3">
                    <h4 class="text-success mb-2"><i class="fas fa-receipt"></i> Payment Successful!</h4>
                    <p class="text-muted">Your purchase has been completed successfully</p>
                </div>
                
                <div class="receipt-details" style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                    <div class="row">
                        <div class="col-6">
                            <strong>Transaction ID:</strong><br>
                            <small class="text-muted">${receiptData.transaction_id || 'N/A'}</small>
                        </div>
                        <div class="col-6 text-end">
                            <strong>Date:</strong><br>
                            <small class="text-muted">${receiptData.date || new Date().toLocaleString()}</small>
                        </div>
                    </div>
                </div>

                <table class="table table-bordered table-sm mb-3">
                    <tbody>
                        <tr>
                            <td><strong>Book Title:</strong></td>
                            <td>${escapeHtml(receiptData.book_title)}</td>
                        </tr>
                        <tr>
                            <td><strong>Customer:</strong></td>
                            <td>${escapeHtml(receiptData.customer_name)}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>${escapeHtml(receiptData.customer_email)}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td>
                                <span class="badge bg-primary">
                                    <i class="fas fa-wallet me-1"></i>
                                    ${receiptData.payment_method?.toUpperCase() || 'WALLET'}
                                </span>
                            </td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>Amount Paid:</strong></td>
                            <td><strong>₱${parseFloat(receiptData.amount).toFixed(2)}</strong></td>
                        </tr>
                        ${receiptData.author_earning ? `
                        <tr>
                            <td><strong>Author Earning:</strong></td>
                            <td>₱${parseFloat(receiptData.author_earning).toFixed(2)}</td>
                        </tr>
                        ` : ''}
                    </tbody>
                </table>

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    The book has been added to your library. You can download it anytime.
                </div>
            </div>
        `;

        Swal.fire({
            title: "Payment Receipt",
            html: receiptHtml,
            icon: "success",
            showCancelButton: true,
            confirmButtonText: 'View My Library',
            cancelButtonText: 'Continue Browsing',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'my_library.php';
            } else {
                loadAllBooks();
            }
        });
    }

    function showEmptyState(container) {
        $(container).html(`
            <div class="no-books">
                <i class="fas fa-book-open"></i>
                <p>No books available at the moment</p>
            </div>
        `);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close modal when clicking outside
    $(window).click(function (e) {
        if ($(e.target).hasClass('modal')) {
            $(e.target).fadeOut(300);
        }
    });
</script>
</body>

</html>