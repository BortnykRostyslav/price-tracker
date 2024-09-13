<?php

require 'db.php';
require 'models/Subscription.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Створюємо екземпляр моделі Subscription
    $subscriptionModel = new Subscription($pdo);

    try {
        // Перевіряємо наявність токена в базі даних
        $subscription = $subscriptionModel->findByToken($token);

        if ($subscription) {
            // Оновлюємо статус підтвердження в базі даних
            $updated = $subscriptionModel->verifyEmail($token);

            if ($updated) {
                echo "Email successfully verified!";
            } else {
                echo "Failed to verify email.";
            }
        } else {
            echo "Invalid or expired token.";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No token provided.";
}

