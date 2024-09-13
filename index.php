<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';
require 'models/Ad.php';
require 'models/Subscription.php';
require 'email.php'; // Логіка відправки email

$adModel = new Ad($pdo);
$subscriptionModel = new Subscription($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $url = $_POST['url'];
    $email = $_POST['email'];

    // Перевіряємо, чи є оголошення в базі
    $ad = $adModel->findByUrl($url);
    if (!$ad) {
        // Отримуємо ціну шляхом парсингу сторінки OLX
        try {
            $price = scrapeAdPrice($url); // Використовуємо функцію для парсингу
            $adId = $adModel->create($url, $price);
        } catch (Exception $e) {
            // Якщо сталася помилка під час парсингу
            echo "Error: " . $e->getMessage();
            exit();
        }
    } else {
        $adId = $ad['id'];
    }

    // Генеруємо токен підтвердження
    $verificationToken = bin2hex(random_bytes(16));

    // Створюємо підписку
    if ($subscriptionModel->create($adId, $email, $verificationToken)) {
        $confirmationLink = "http://localhost:8000/verify.php?token=$verificationToken";

        // Надсилаємо email підтвердження
        sendEmailConfirmation($email, $confirmationLink);

        echo "Subscribed successfully! Please check your email to confirm your subscription.";
    } else {
        echo "Error creating subscription.";
    }
}

// Функція для отримання ціни оголошення через парсинг HTML сторінки
function scrapeAdPrice($url)
{
    // Ініціалізація cURL
    $ch = curl_init();

    // Встановлюємо URL для запиту
    curl_setopt($ch, CURLOPT_URL, $url);

    // Симулюємо браузер через User-Agent
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

    // Вимикаємо SSL верифікацію
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    // Повертаємо відповідь як рядок замість прямого виведення
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Виконуємо запит
    $response = curl_exec($ch);

    // Перевіряємо, чи немає помилок
    if (curl_errno($ch)) {
        throw new Exception("Could not fetch the OLX page. Error: " . curl_error($ch));
    }

    // Закриваємо cURL
    curl_close($ch);

    // Використовуємо регулярний вираз для пошуку ціни в HTML-коді сторінки
    preg_match('/<h3 class="css-90xrc0">([^<]+)<\/h3>/', $response, $matches);

    // Якщо ціна знайдена, обробляємо її
    if (isset($matches[1])) {
        $priceString = trim($matches[1]);

        // Видаляємо всі нечислові символи, крім крапки
        $priceString = preg_replace('/[^\d.,]/', '', $priceString);

        // Заміна коми на крапку для десяткового розділювача, якщо потрібно
        $priceString = str_replace(',', '.', $priceString);

        // Перетворюємо в числове значення
        $price = (float) $priceString;

        return $price;
    } else {
        throw new Exception("Could not find the price on the OLX page.");
    }
}
