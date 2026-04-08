<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Libris Mind Verse</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../assets/libris.png">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/profile-page.css">

</head>

<body>
    <!-- Updated Header - Same as user_feed -->
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

    <!-- Updated Container - Same as genres -->
    <div class="container">
        <a href="user_feed.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Feed
        </a>

        <h1 class="text-white mb-4"><i class="fas fa-user"></i> My Profile</h1>

        <!-- Profile Stats -->
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number" id="totalBooks">0</div>
                <div class="stat-label">Total Books</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number" id="approvedBooks">0</div>
                <div class="stat-label">Approved Books</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number" id="pendingBooks">0</div>
                <div class="stat-label">Pending Books</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number" id="rejectedBooks">0</div>
                <div class="stat-label">Rejected Books</div>
            </div>
        </div>

        <div class="profile-actions">
            <div class="action-card">
                <div class="action-info">
                    <div class="action-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <h3>Change Password</h3>
                        <p>Update your account password from your profile page to keep your account secure.</p>
                    </div>
                </div>
                <a href="../auth/change_password.php" class="action-btn">
                    <i class="fas fa-arrow-right"></i> Update Password
                </a>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="filterBooks('all')">All Books</div>
            <div class="tab" onclick="filterBooks('approved')">Approved</div>
            <div class="tab" onclick="filterBooks('pending')">Pending</div>
            <div class="tab" onclick="filterBooks('rejected')">Rejected</div>
        </div>

        <!-- Books Grid -->
        <div id="booksContainer" class="books-grid">
            <!-- Books will be loaded here -->
        </div>
    </div>

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
                        <input type="text" id="title" required placeholder="Title" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="author">Author *</label>
                        <input type="text" id="author" required placeholder="Author" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="publisher">Publisher *</label>
                        <input type="text" id="publisher" required placeholder="e.g., Penguin Books"
                            class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="genre">Genre *</label>
                        <select id="genre" required class="form-control">
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
                        <textarea id="description" required class="form-control"
                            placeholder="Describe what your book is about..."></textarea>
                    </div>

                    <!-- Simplified Pricing Section -->
                    <div class="form-group">
                        <label for="price">Book Price (PHP) *</label>
                        <input type="number" id="price" min="0" step="0.01" value="0" required class="form-control"
                            placeholder="Enter 0 for free book">
                        <small style="color: #666; display: block; margin-top: 5px;">
                            • <strong>Free</strong>: Set price to 0 (anyone can download)<br>
                            • <strong>Paid</strong>: Set price above 0 (users must purchase)
                        </small>
                        <div id="priceMessage" style="margin-top: 8px; font-weight: 500;"></div>
                    </div>

                    <div class="form-group">
                        <label for="pdf_file">Book PDF (Optional, max 10MB)</label>
                        <input type="file" id="pdf_file" accept=".pdf" class="form-control">
                    </div>

                    <button type="submit" class="book-action-btn view-btn w-100">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div class="modal" id="editBookModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Edit Book</div>
                <button class="close-modal" onclick="closeModal('editBookModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editBookForm" enctype="multipart/form-data">
                    <input type="hidden" id="editBookId" name="id">
                    <div class="form-group">
                        <label for="editTitle">Book Title *</label>
                        <input type="text" id="editTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="editAuthor">Author *</label>
                        <input type="text" id="editAuthor" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="editPublisher">Publisher *</label>
                        <input type="text" id="editPublisher" name="publisher" required>
                    </div>
                    <div class="form-group">
                        <label for="editGenre">Genre *</label>
                        <select id="editGenre" name="genre" required>
                            <option value="">Select Genre</option>
                            <option value="Fiction">Fiction</option>
                            <option value="Sci-Fi">Science Fiction</option>
                            <option value="Mystery">Mystery</option>
                            <option value="Romance">Romance</option>
                            <option value="Fantasy">Fantasy</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Biography">Biography</option>
                            <option value="History">History</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editDescription">Book Description *</label>
                        <textarea id="editDescription" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editPdfFile">Update PDF (Optional, max 10MB)</label>
                        <input type="file" id="editPdfFile" name="pdf_file" accept=".pdf">
                        <small>Leave empty to keep current PDF</small>
                    </div>
                    <button type="submit" class="book-action-btn view-btn w-100">
                        <i class="fas fa-save"></i> Update Book
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '../controller/crud.php';
        const CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;
        const CURRENT_USER_NAME = '<?php echo $_SESSION['fullname']; ?>';
        const CURRENT_USER_EMAIL = '<?php echo $_SESSION['email']; ?>';

        // Modal Functions
        function showModal(modalId) {
            $('#' + modalId).css('display', 'flex').hide().fadeIn(200);
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

        // Create Post Form Submission
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

            // Simplified pricing - just send the price
            const price = parseFloat($('#price').val()) || 0;
            formData.append('price', price);
            formData.append('is_free', price === 0 ? '1' : '0');

            // Validation
            if (!formData.get('title') || !formData.get('author') || !formData.get('publisher') ||
                !formData.get('genre') || !formData.get('description')) {
                Swal.fire('Error', 'Please fill all required fields', 'error');
                return;
            }

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
                url: API_URL,
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
                            loadUserBooks();
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

        // Price Input Real-time Feedback
        $(document).ready(function () {
            // Price input real-time feedback
            $('#price').on('input', function () {
                const price = parseFloat($(this).val()) || 0;
                const priceMessage = $('#priceMessage');

                if (price === 0) {
                    priceMessage.html('<span style="color: #4CAF50;">✓ This book will be <strong>FREE</strong> for everyone</span>');
                } else if (price < 0) {
                    priceMessage.html('<span style="color: #f44336;">✗ Price cannot be negative</span>');
                } else {
                    const authorEarning = (price * 1).toFixed(2);
                    priceMessage.html(`<span style="color: #2196F3;">
                ✓ This book will be <strong>PAID</strong><br>
                • Your earning: <strong>₱${authorEarning}</strong>
            </span>`);
                }
            });

            // Trigger initial price message
            $('#price').trigger('input');

            // Load user books
            loadUserBooks();
        });

        // Close modal when clicking outside
        $(window).click(function (e) {
            if ($(e.target).hasClass('modal')) {
                $(e.target).fadeOut();
            }
        });

        // Utility Functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' at ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function loadUserBooks() {
            $.ajax({
                url: '../controller/crud.php',
                method: 'GET',
                data: {
                    action: 'read',
                    user_id: <?php echo $_SESSION['user_id']; ?>
                },
                success: function (response) {
                    if (response.status === 'success') {
                        displayBooks(response.data);
                        updateBookStats(response.data);
                    } else {
                        showNoBooks();
                    }
                },
                error: function () {
                    showNoBooks();
                }
            });
        }

        function displayBooks(books) {
            if (!books || books.length === 0) {
                showNoBooks();
                return;
            }

            const container = $('#booksContainer');
            container.empty();

            books.forEach(book => {
                const statusClass = `status-${book.status.toLowerCase()}`;
                const card = `
                    <div class="book-card" data-status="${book.status.toLowerCase()}">
                        <div class="book-status ${statusClass}">
                            ${book.status.charAt(0).toUpperCase() + book.status.slice(1)}
                        </div>
                        <div class="book-title">${escapeHtml(book.title)}</div>
                        <div class="book-meta">
                            <div><i class="fas fa-user"></i> ${escapeHtml(book.author)}</div>
                            <div><i class="fas fa-building"></i> ${escapeHtml(book.publisher)}</div>
                            <div><i class="fas fa-tag"></i> ${escapeHtml(book.genre)}</div>
                            <div><i class="fas fa-calendar"></i> ${formatDate(book.publish_date || book.created_at)}</div>
                        </div>
                        <div class="book-description">${escapeHtml(book.description || 'No description available.')}</div>
                        <div class="book-actions">
                            ${book.pdf_path ?
                        `<a href="../controller/download.php?id=${book.id}" class="book-action-btn view-btn">
                                    <i class="fas fa-download"></i> Download PDF
                                </a>` :
                        ''
                    }
                            <button class="book-action-btn edit-btn" onclick="editBook(${book.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="book-action-btn delete-btn" onclick="deleteBook(${book.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        function editBook(bookId) {
            // Fetch book details and populate edit modal
            $.ajax({
                url: '../controller/crud.php',
                method: 'GET',
                data: { action: 'read', id: bookId },
                success: function (response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        const book = response.data[0];

                        // Populate edit form
                        $('#editBookId').val(book.id);
                        $('#editTitle').val(book.title);
                        $('#editAuthor').val(book.author);
                        $('#editPublisher').val(book.publisher);
                        $('#editGenre').val(book.genre);
                        $('#editDescription').val(book.description || '');

                        // Show edit modal
                        $('#editBookModal').fadeIn();
                    }
                }
            });
        }

        function deleteBook(bookId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../controller/crud.php',
                        method: 'POST',
                        data: {
                            action: 'delete_user_book',
                            id: bookId
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                );
                                loadUserBooks();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'An error occurred while deleting the book.', 'error');
                        }
                    });
                }
            });
        }

        function updateBookStats(books) {
            const stats = {
                total: books.length,
                approved: books.filter(b => b.status === 'approved').length,
                pending: books.filter(b => b.status === 'pending').length,
                rejected: books.filter(b => b.status === 'rejected').length
            };

            $('#totalBooks').text(stats.total);
            $('#approvedBooks').text(stats.approved);
            $('#pendingBooks').text(stats.pending);
            $('#rejectedBooks').text(stats.rejected);
        }

        function filterBooks(status) {
            $('.tab').removeClass('active');
            $('.tab').each(function () {
                if ($(this).text().toLowerCase().includes(status.toLowerCase())) {
                    $(this).addClass('active');
                }
            });

            if (status === 'all') {
                $('.book-card').show();
            } else {
                $('.book-card').hide();
                $(`.book-card[data-status="${status}"]`).show();
            }

            if ($('.book-card:visible').length === 0) {
                showNoBooks();
            }
        }

        function showNoBooks() {
            $('#booksContainer').html(`
                <div class="no-books">
                    <i class="fas fa-book-open fa-3x"></i>
                    <p>No books found in this category</p>
                </div>
            `);
        }

        // Handle edit form submission
        $('#editBookForm').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update_user_book');

            Swal.fire({
                title: 'Updating Book...',
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
                            closeModal('editBookModal');
                            loadUserBooks();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire('Error', 'An error occurred while updating the book.', 'error');
                }
            });
        });

        function closeModal(modalId) {
            $('#' + modalId).fadeOut();
            $('#' + modalId + ' form')[0].reset();
        }

        // Close modal when clicking outside
        $(window).click(function (e) {
            if ($(e.target).hasClass('modal')) {
                $(e.target).fadeOut();
                $('.modal form')[0].reset();
            }
        });
    </script>
</body>

</html>


