$(document).ready(function () {
    $("#forgotPasswordForm").on("submit", function (e) {
        e.preventDefault();
        $.ajax({
            url: "../controller/forgot_password_process.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                console.log("Success:", response);
                if (response.message == "SUCCESS") {
                    Swal.fire({
                        icon: "success",
                        title: "New Password Sent",
                        text: "Please check your email for your new password.",
                    });
                    $("#registerForm")[0].reset();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "forgot password Failed",
                        text: response.error || "An error occurred.",
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log("Error response:", xhr.responseText);
                console.log("Status:", status);
                console.log("Error:", error);
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "forgot password Failed",
                        text: response.error || "An error occurred.",
                    });
                } catch (e) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops!",
                        text: "Something went wrong. Please try again.",
                    });
                }
            },
        });
    });
});