<?php
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
ini_set('display_errors', 0);
class EmailNotification{
    public static function sendEmail($subject, $body, $email_title, $email){
        $projectRoot = dirname(__DIR__);
        Dotenv::createImmutable($projectRoot)->safeLoad();

        $mailUsername = $_ENV['MAIL_USERNAME'] ?? '';
        $mailPassword = $_ENV['MAIL_PASSWORD'] ?? '';
        $mailHost = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mailPort = (int) ($_ENV['MAIL_PORT'] ?? 465);
        $mailEncryption = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_SMTPS;

        $recipient = $email;
        $subject = $subject;
        $body = $body;
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $mailHost;
            $mail->SMTPAuth = true;
            $mail->Username = $mailUsername;
            $mail->Password = $mailPassword;
            $mail->SMTPSecure = $mailEncryption;
            $mail->Port = $mailPort;

            $mail->setFrom($mailUsername, $email_title);
            $mail->addAddress($recipient);
    
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
    
            $mail->send();
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send email.',
                'error' => $mail->ErrorInfo
            ]);
        }
    }
}
?>
