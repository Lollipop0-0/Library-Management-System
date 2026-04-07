<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PayMongo Checkout Demo</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3">Pay with PayMongo (Checkout)</h4>

            <form id="checkoutForm">
              <div class="mb-3">
                <label class="form-label">Customer name</label>
                <input name="customer_name" class="form-control" required placeholder="Juan Dela Cruz">
              </div>

              <div class="mb-3">
                <label class="form-label">Customer email</label>
                <input name="customer_email" type="email" class="form-control" required placeholder="email@example.com">
              </div>

              <div class="mb-3">
                <label class="form-label">Amount (PHP)</label>
                <input name="amount_php" type="number" min="1" class="form-control" required value="1000"
                  placeholder="Amount in PHP (e.g. 1000)">
                <div class="form-text">Amount will be converted to centavos for PayMongo (e.g. 1000 → 100000).</div>
              </div>

              <button id="payBtn" type="submit" class="btn btn-primary w-100">
                Pay now
              </button>
            </form>

            <hr>
            <small class="text-muted">
              This demo uses PayMongo’s hosted Checkout (server creates a Checkout Session then you get redirected).
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
<script src="../js/payment.js"></script>

</html>