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
    <title>My Wallet - Libris Mind Verse</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/wallet-page.css">

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

        <div class="wallet-container">
            <div class="wallet-card">
                <h1 class="text-center mb-4">
                    <i class="fas fa-wallet"></i> My Wallet
                </h1>

                <div class="balance-display">
                    <div>Current Balance</div>
                    <div class="balance-amount" id="walletBalance">₱0.00</div>
                    <small>Available for book purchases</small>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-light" onclick="refreshWallet()" title="Refresh Balance">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn-wallet btn-cashin" onclick="showCashInModal()">
                        <i class="fas fa-plus-circle"></i> Cash In
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    const CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;
    const CURRENT_USER_NAME = '<?php echo $_SESSION['fullname']; ?>';
    const CURRENT_USER_EMAIL = '<?php echo $_SESSION['email']; ?>';
    
    $(document).ready(function() {
        loadWalletBalance();
        loadWalletStats();
        checkForSuccessMessage();
        
        // Refresh wallet data every 30 seconds
        setInterval(loadWalletBalance, 30000);
        setInterval(loadWalletStats, 30000);
    });

    function loadWalletBalance() {
        // Load balance
        $.ajax({
            url: '../controller/get_wallet_balance.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#walletBalance').text('₱' + parseFloat(response.balance).toFixed(2));
                    $('#currentBalance').text('₱' + parseFloat(response.balance).toFixed(2));
                } else {
                    console.error('Balance load error:', response.message);
                    $('#walletBalance').text('₱0.00');
                    $('#currentBalance').text('₱0.00');
                }
            },
            error: function(xhr, status, error) {
                console.error('Balance AJAX error:', error);
                $('#walletBalance').text('₱0.00');
                $('#currentBalance').text('₱0.00');
            }
        });
    }

    function loadWalletStats() {
        // Load wallet statistics
        $.ajax({
            url: '../controller/get_wallet_stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#totalDeposits').text('₱' + parseFloat(response.total_deposits || 0).toFixed(2));
                    $('#totalSpent').text('₱' + parseFloat(response.total_spent || 0).toFixed(2));
                } else {
                    console.error('Stats load error:', response.message);
                    $('#totalDeposits').text('₱0.00');
                    $('#totalSpent').text('₱0.00');
                }
            },
            error: function(xhr, status, error) {
                console.error('Stats AJAX error:', error);
                $('#totalDeposits').text('₱0.00');
                $('#totalSpent').text('₱0.00');
            }
        });
    }

    function showCashInModal() {
        Swal.fire({
            title: 'Cash In to Wallet',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label">Amount (PHP)</label>
                        <input type="number" id="cashInAmount" class="form-control" min="50" step="0.01" placeholder="Minimum ₱50" value="100">
                        <div class="form-text">Minimum amount: ₱50.00</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" id="cashInName" class="form-control" value="${CURRENT_USER_NAME}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Email</label>
                        <input type="email" id="cashInEmail" class="form-control" value="${CURRENT_USER_EMAIL}" readonly>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You'll be redirected to PayMongo for payment
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Proceed to Payment',
            confirmButtonColor: '#667eea',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const amount = parseFloat(document.getElementById('cashInAmount').value);
                const name = document.getElementById('cashInName').value;
                const email = document.getElementById('cashInEmail').value;

                if (!amount || amount < 50) {
                    Swal.showValidationMessage('Minimum amount is ₱50');
                    return false;
                }

                if (!name || !email) {
                    Swal.showValidationMessage('Please fill in all fields');
                    return false;
                }

                return { amount, name, email };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                processCashIn(result.value);
            }
        });
    }

    function processCashIn(data) {
        Swal.fire({
            title: "Processing Payment...",
            text: "Please wait while we prepare your payment",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const payload = {
            customer_name: data.name,
            customer_email: data.email,
            amount_php: data.amount,
            type: 'cash_in',
            user_id: CURRENT_USER_ID,
            payment_method: 'paymongo'
        };

        $.ajax({
            url: "../controller/wallet_controller.php",
            method: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json",
            dataType: "json",
            success: function (res) {
                Swal.close();
                
                if (res && res.checkout_url) {
                    Swal.fire({
                        title: "Redirecting to Payment",
                        html: `
                            <div class="text-center">
                                <i class="fas fa-external-link-alt fa-3x text-primary mb-3"></i>
                                <p>You will be redirected to PayMongo to complete your payment.</p>
                                <small class="text-muted">Amount: ₱${data.amount.toFixed(2)}</small>
                            </div>
                        `,
                        icon: "info",
                        showCancelButton: true,
                        confirmButtonText: 'Continue to Payment',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#667eea'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = res.checkout_url;
                        }
                    });
                } else {
                    Swal.fire({
                        title: "Payment Error",
                        text: res.message || "Failed to create payment session",
                        icon: "error",
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                let msg = "Payment processing failed";
                
                try {
                    const response = xhr.responseJSON || JSON.parse(xhr.responseText);
                    msg = response.message || "Payment processing failed";
                } catch (e) {
                    msg = xhr.responseText || "Payment processing failed";
                }
                
                Swal.fire({
                    title: "Payment Error",
                    text: msg,
                    icon: "error",
                    confirmButtonText: 'OK'
                });
                
                console.error('Cash in error:', error, xhr.responseText);
            }
        });
    }

    function refreshWallet() {
        loadWalletBalance();
        loadWalletStats();
        loadTransactionHistory();
        
        // Show refresh feedback
        const balanceElement = $('#walletBalance');
        balanceElement.css('opacity', '0.7');
        
        setTimeout(() => {
            balanceElement.css('opacity', '1');
        }, 1000);
    }

    function checkForSuccessMessage() {
        const urlParams = new URLSearchParams(window.location.search);
        const purchaseSuccess = urlParams.get('purchase_success');
        const orderCreated = urlParams.get('order_created');
        
        if (purchaseSuccess === 'true' || orderCreated === 'true') {
            // Force reload wallet data
            loadWalletBalance();
            loadWalletStats();
            loadTransactionHistory();
            
            Swal.fire({
                icon: 'success',
                title: 'Purchase Successful!',
                text: 'Your book has been added to your library and transaction has been recorded.',
                confirmButtonColor: '#667eea'
            });
            
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' at ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl + R to refresh wallet (but prevent browser refresh)
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            refreshWallet();
        }
        
        // F5 to refresh
        if (e.key === 'F5') {
            e.preventDefault();
            refreshWallet();
        }
    });
    </script>
</body>
</html>


