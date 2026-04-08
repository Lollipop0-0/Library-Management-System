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
    <title>Browse Genres - Libris Mind Verse</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../assets/libris.png">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/genres-page.css">

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
        <a href="../user/user_feed.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Feed
        </a>

        <h1 class="text-white mb-4"><i class="fas fa-tags"></i> Browse Genres</h1>

        <!-- Genre Stats -->
        <div class="genre-stats" id="genreStats">
            <!-- Genre stats will be loaded here -->
        </div>

        <!-- Tabs -->
        <div class="tabs" id="genreTabs">
            <!-- Tabs will be loaded here -->
        </div>

        <!-- Books Grid -->
        <div id="booksContainer" class="books-grid">
            <!-- Books will be loaded here -->
        </div>
    </div>

    <script>
        const CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;
        const CURRENT_USER_NAME = '<?php echo $_SESSION['fullname']; ?>';
        const CURRENT_USER_EMAIL = '<?php echo $_SESSION['email']; ?>';

        $(document).ready(function () {
            loadGenreStats();
            loadGenreTabs();

            // Load all books by default
            setTimeout(() => {
                filterBooksByGenre('all');
            }, 100);

            // Search functionality
            $('.search-input').on('input', function () {
                const searchTerm = $(this).val().toLowerCase();
                filterBooksBySearch(searchTerm);
            });

            $('.input-submit').on('click', function (e) {
                e.preventDefault();
                const searchTerm = $('.search-input').val().toLowerCase();
                filterBooksBySearch(searchTerm);
            });
        });

        function loadGenreStats() {
            $.ajax({
                url: '../controller/get_genres.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        displayGenreStats(response.data);
                    } else {
                        console.error('Error loading genre stats:', response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading genre stats:', error);
                }
            });
        }

        function displayGenreStats(genres) {
            const container = $('#genreStats');
            let html = '';

            // Add "All Genres" stat card
            const totalBooks = genres.reduce((sum, genre) => sum + parseInt(genre.count), 0);
            html += `
            <div class="stat-card active" onclick="filterBooksByGenre('all')">
                <div class="stat-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-number">${totalBooks}</div>
                <div class="stat-label">All Genres</div>
            </div>
        `;

            genres.forEach(genre => {
                const icon = getGenreIcon(genre.genre);
                html += `
                <div class="stat-card" onclick="filterBooksByGenre('${escapeHtml(genre.genre)}')">
                    <div class="stat-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="stat-number">${genre.count}</div>
                    <div class="stat-label">${escapeHtml(genre.genre)}</div>
                </div>
            `;
            });

            container.html(html);
        }

        function loadGenreTabs() {
            $.ajax({
                url: '../controller/get_genres.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        displayGenreTabs(response.data);
                    } else {
                        console.error('Error loading genre tabs:', response.message);
                        displayGenreTabs([
                            { genre: 'Fiction', count: 0 },
                            { genre: 'Sci-Fi', count: 0 },
                            { genre: 'Mystery', count: 0 },
                            { genre: 'Romance', count: 0 },
                            { genre: 'Fantasy', count: 0 }
                        ]);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading genre tabs:', error);
                    displayGenreTabs([
                        { genre: 'Fiction', count: 0 },
                        { genre: 'Sci-Fi', count: 0 },
                        { genre: 'Mystery', count: 0 },
                        { genre: 'Romance', count: 0 },
                        { genre: 'Fantasy', count: 0 }
                    ]);
                }
            });
        }

        function displayGenreTabs(genres) {
            const container = $('#genreTabs');
            const totalBooks = genres.reduce((sum, genre) => sum + parseInt(genre.count), 0);

            let html = `<div class="tab active" data-genre="all">All Genres <span class="badge bg-secondary">${totalBooks}</span></div>`;

            genres.forEach(genre => {
                html += `
                <div class="tab" data-genre="${escapeHtml(genre.genre)}">
                    ${escapeHtml(genre.genre)} <span class="badge bg-secondary">${genre.count}</span>
                </div>
            `;
            });

            container.html(html);

            // Add click event listeners to tabs
            $('.tab').on('click', function () {
                const genre = $(this).data('genre');
                filterBooksByGenre(genre);
            });
        }

        let allBooks = [];
        let currentFilter = 'all';

        function filterBooksByGenre(genre) {
            console.log('Filtering by genre:', genre);
            currentFilter = genre;

            $('#booksContainer').html(`
            <div class="no-books">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p>Loading books...</p>
            </div>
        `);

            $('.tab').removeClass('active');
            $(`.tab[data-genre="${genre}"]`).addClass('active');

            $('.stat-card').removeClass('active');
            if (genre === 'all') {
                $('.stat-card').first().addClass('active');
            } else {
                $(`.stat-card:contains('${genre}')`).addClass('active');
            }

            const params = { action: 'read' };
            if (genre !== 'all') {
                params.genre = genre;
            }

            $.ajax({
                url: '../controller/crud.php',
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function (response) {
                    console.log('Books response:', response);
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        allBooks = response.data;
                        displayBooks(response.data);
                    } else {
                        console.log('No books found for genre:', genre);
                        allBooks = [];
                        showNoBooks(genre);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading books:', error);
                    console.error('Response text:', xhr.responseText);
                    allBooks = [];
                    showNoBooks(genre);
                }
            });
        }

        function filterBooksBySearch(searchTerm) {
            if (!searchTerm.trim()) {
                displayBooks(allBooks);
                return;
            }

            const filteredBooks = allBooks.filter(book => {
                const title = (book.title || '').toLowerCase();
                const author = (book.author || '').toLowerCase();
                const genre = (book.genre || '').toLowerCase();
                const description = (book.description || '').toLowerCase();

                return title.includes(searchTerm) ||
                    author.includes(searchTerm) ||
                    genre.includes(searchTerm) ||
                    description.includes(searchTerm);
            });

            if (filteredBooks.length > 0) {
                displayBooks(filteredBooks);
            } else {
                showNoBooks('search', searchTerm);
            }
        }

        function displayBooks(books) {
            const container = $('#booksContainer');

            if (!books || books.length === 0) {
                showNoBooks(currentFilter);
                return;
            }

            let html = '';
            books.forEach(book => {
                const statusClass = `status-${book.status ? book.status.toLowerCase() : 'approved'}`;
                const isFree = book.is_free == 1 || (book.price && parseFloat(book.price) <= 0);
                const price = parseFloat(book.price || 0);
                const isOwner = book.user_id == CURRENT_USER_ID;

                html += `
                <div class="book-card">
                    <div class="book-status ${statusClass}">
                        ${book.status ? book.status.charAt(0).toUpperCase() + book.status.slice(1) : 'Approved'}
                    </div>
                    <div class="book-title">${escapeHtml(book.title || 'Untitled')}</div>
                    <div class="book-meta">
                        <div><i class="fas fa-user"></i> ${escapeHtml(book.author || 'Unknown Author')}</div>
                        <div><i class="fas fa-building"></i> ${escapeHtml(book.publisher || 'Unknown Publisher')}</div>
                        <div><i class="fas fa-tag"></i> ${escapeHtml(book.genre || 'Uncategorized')}</div>
                        <div><i class="fas fa-calendar"></i> ${formatDate(book.publish_date || book.created_at)}</div>
                        <div>
                            ${!isFree ? `<span class="price-badge"><i class="fas fa-tag"></i> ₱${price.toFixed(2)}</span>` : `<span class="free-badge">FREE</span>`}
                        </div>
                    </div>
                    <div class="book-description">${escapeHtml(book.description || 'No description available.')}</div>
                    <div class="book-actions">
                        ${book.pdf_path ? `
                            ${isFree || isOwner ?
                            `<a href="../controller/download.php?id=${book.id}" class="book-action-btn view-btn">
                                    <i class="fas fa-download"></i> Download PDF
                                </a>` :
                            `<button class="book-action-btn buy-btn" 
                                    data-book-id="${book.id}" 
                                    data-book-title="${escapeHtml(book.title)}" 
                                    data-amount="${price}">
                                    <i class="fas fa-shopping-cart"></i> Buy - ₱${price.toFixed(2)}
                                </button>`
                        }
                        ` : '<span class="text-muted">No PDF available</span>'}
                    </div>
                </div>
            `;
            });

            container.html(html);

            $('.buy-btn').on('click', function (e) {
                e.preventDefault();
                const bookId = $(this).data('book-id');
                const bookTitle = $(this).data('book-title');
                const amount = $(this).data('amount');
                initiateBookPurchase(bookId, bookTitle, amount);
            });
        }

        function showNoBooks(filterType = 'all', searchTerm = '') {
            let message = '';

            if (filterType === 'search') {
                message = `No books found matching "${searchTerm}"`;
            } else if (filterType === 'all') {
                message = 'No books found in the library';
            } else {
                message = `No books found in the ${filterType} genre`;
            }

            $('#booksContainer').html(`
            <div class="no-books">
                <i class="fas fa-book-open fa-3x"></i>
                <p>${message}</p>
                ${filterType !== 'all' ? '<button class="btn btn-primary mt-3" onclick="filterBooksByGenre(\'all\')">Show All Books</button>' : ''}
            </div>
        `);
        }

        function getGenreIcon(genre) {
            const iconMap = {
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
            return iconMap[genre] || 'fa-book';
        }

        function formatDate(dateString) {
            try {
                const date = new Date(dateString);
                return isNaN(date.getTime()) ? 'Unknown date' : date.toLocaleDateString();
            } catch (e) {
                return 'Unknown date';
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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
                },
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
                    // Reload the current genre view
                    filterBooksByGenre(currentFilter);
                }
            });
        }
    </script>
</body>

</html>


