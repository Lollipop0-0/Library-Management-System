$(document).ready(function () {
    $("#registerForm").on("submit", function (e) {
        e.preventDefault();
        $.ajax({
            url: "../controller/register_function.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                console.log("Success:", response);
                if (response.message == "SUCCESS") {
                    Swal.fire({
                        icon: "success",
                        title: "Registration Successful",
                        text: "Please check your email to verify your account.",
                    });
                    $("#registerForm")[0].reset();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Registration Failed",
                        text: response.error || "An error occurred.",
                    });
                }
            },
            error: function (xhr, status, error) {
                console.log("Error response:", xhr.responseText);
                console.log("Status:", status);
                console.log("Error:", error);
                
                // Try to parse the response anyway
                try {
                    const response = JSON.parse(xhr.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "Registration Failed",
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