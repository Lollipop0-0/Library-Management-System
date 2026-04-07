$(document).ready(function () {
  $("#checkoutForm").on("submit", function (e) {
    e.preventDefault();
    const data = {
      customer_name: $("[name=customer_name]").val(),
      customer_email: $("[name=customer_email]").val(),
      amount_php: parseFloat($("[name=amount_php]").val() || 0),
    };

    if (!data.amount_php || data.amount_php <= 0) {
      Swal.fire(
        "Invalid amount",
        "Please enter a valid amount in PHP.",
        "error"
      );
      return;
    }

    Swal.fire({
      title: "Creating checkout...",
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
        if (res && res.checkout_url) {
          Swal.fire({
            title: "Redirecting to checkout...",
            icon: "success",
            timer: 1200,
            showConfirmButton: false,
          }).then(() => {
            window.location.href = res.checkout_url;
          });
        } else {
          Swal.fire(
            "Error",
            res.message || "No checkout URL returned from server.",
            "error"
          );
        }
      },
      error: function (xhr) {
        Swal.close();
        let msg = "Server error";
        try {
          msg =
            xhr.responseJSON && xhr.responseJSON.message
              ? xhr.responseJSON.message
              : xhr.responseText;
        } catch (e) {}
        Swal.fire("Error", msg, "error");
      },
    });
  });
});
