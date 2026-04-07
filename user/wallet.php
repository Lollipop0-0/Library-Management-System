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
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #d4af37;
            --light-color: #f8f5e6;
            --dark-color: #1a1a1a;
            --card-bg: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

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
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            color: #f0f0f0;
            background: rgba(255, 255, 255, 0.3);
        }

        .wallet-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .wallet-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .balance-display {
            text-align: center;
            padding: 3rem;
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            color: white;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .balance-amount {
            font-size: 3.5rem;
            font-weight: 700;
            margin: 1.5rem 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .btn-wallet {
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .btn-cashin {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-cashin:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-outline-light {
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            background: transparent;
            transition: all 0.3s;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .transaction-history {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .transaction-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .transaction-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
        }

        .transaction-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .transaction-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 1rem;
            border-radius: var(--border-radius);
            background: #f8f9fa;
            transition: background 0.3s;
        }

        .transaction-item:hover {
            background: #e9ecef;
        }

        .transaction-info {
            flex: 1;
        }

        .transaction-type {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.3rem;
        }

        .transaction-date {
            font-size: 0.8rem;
            color: #666;
        }

        .transaction-amount {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .amount-positive {
            color: #4CAF50;
        }

        .amount-negative {
            color: #f44336;
        }

        .no-transactions {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-transactions i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .wallet-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
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

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
            }

            .search-bar {
                margin: 0;
                order: 3;
                width: 100%;
            }

            .nav-menu {
                order: 2;
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .profile-header {
                order: 1;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-wallet {
                width: 100%;
                justify-content: center;
            }

            .balance-amount {
                font-size: 2.5rem;
            }

            .wallet-stats {
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