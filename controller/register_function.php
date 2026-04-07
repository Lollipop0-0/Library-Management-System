<?php
    header('Content-Type: application/json');
    include_once 'EmailNotification.php';
    include '../db/db_conn.php';

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $verificationLink = "http://localhost/admin/success/verify_success.php";
    $emailBody = <<<EOT
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <title>Email Verification</title>
        </head>
        <body style="margin:0; padding:0; background-color:#f5f7fa; font-family: Arial, sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa; padding:20px;">
            <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                <tr>
                    <td style="background:#4f46e5; padding:20px; text-align:center;">
                    <h1 style="margin:0; color:#ffffff; font-size:24px;">Verify Your Email</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px;">
                    <p style="font-size:16px; color:#333333; margin-bottom:20px;">
                        Hi <strong>{$fullname} </strong>,
                    </p>
                    <p style="font-size:16px; color:#333333; line-height:1.5; margin-bottom:20px;">
                        Thank you for signing up! Please verify your email address by clicking the button below.
                    </p>
                    <p style="text-align:center; margin:30px 0;">
                        <a href="{$verificationLink}?otp={$otp}" 
                        style="display:inline-block; padding:12px 24px; font-size:16px; font-weight:bold; 
                                color:#ffffff; background:#4f46e5; text-decoration:none; border-radius:6px;">
                        Verify Email
                        </a>
                    </p>
                    <p style="font-size:14px; color:#777777; line-height:1.5;">
                        If the button above doesn’t work, copy and paste this link into your browser:
                        <br>
                        <a href="{$verificationLink}" style="color:#4f46e5;">{$verificationLink}</a>
                    </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f0f0f0; text-align:center; padding:15px; font-size:12px; color:#888888;">
                    &copy; {date('Y')} Your Company. All rights reserved.
                    </td>
                </tr>
                </table>
            </td>
            </tr>
        </table>
        </body>
        </html>
    EOT;

    EmailNotification::sendEmail("VERIFY EMAIL", $emailBody, 'VERIFY YOUR EMAIL', $email);
    echo json_encode(['message' => 'SUCCESS']);
    exit;
    ?>