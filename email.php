<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendEmail($to, $adUrl, $newPrice)
{
    $mail = new PHPMailer(true);

    try {
        // Налаштування SMTP для Mailtrap
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '9196ec3cbfc384';
        $mail->Password = 'c8ec1ffc7e6ad3';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;

        // Відправник і одержувач
        $mail->setFrom('noreply@example.com', 'Price Tracker');
        $mail->addAddress($to);

        // Вміст листа
        $mail->isHTML(true);
        $mail->Subject = 'Price change detected!';
        $mail->Body = "The price for the ad at <a href='$adUrl'>$adUrl</a> has changed to $newPrice.";

        $mail->send();
        echo "Message has been sent to $to\n";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
    }
}

