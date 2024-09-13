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

$ads = $pdo->query("SELECT * FROM ads")->fetchAll(PDO::FETCH_ASSOC);

foreach ($ads as $ad) {
    try {
        // Отримуємо поточну ціну з OLX API
        $currentPrice = getAdPrice($ad['url']);

        // Якщо ціна змінилася, оновлюємо її в базі та надсилаємо повідомлення підписникам
        if ($currentPrice !== null && $currentPrice != $ad['price']) {
            $adModel->updatePrice($ad['id'], $currentPrice);

            $subscriptions = $subscriptionModel->findByAdId($ad['id']);

            foreach ($subscriptions as $subscription) {
                sendEmail($subscription['email'], $ad['url'], $currentPrice);
            }
        }
    } catch (Exception $e) {
        // Логування помилки або повідомлення
        error_log("Error processing ad with URL " . $ad['url'] . ": " . $e->getMessage());
    }
}

function getAdPrice($url)
{
    $html = file_get_contents($url);

    if ($html === false) {
        echo "Failed to fetch ad page from URL: $url\n";
        return null;
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Щоб уникнути помилок парсингу
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // Оновлений XPath для класу css-90xrc0
    $priceNode = $xpath->query("//h3[contains(@class, 'css-90xrc0')]");

    if ($priceNode->length > 0) {
        $priceText = $priceNode->item(0)->textContent;
        // Очищення тексту ціни (видалення пробілів та символів валют)
        $price = preg_replace('/[^0-9]/', '', $priceText);
        echo "Ad Price: $price\n";
        return $price;
    } else {
        echo "Price not found in HTML page.\n";
        return null;
    }
}

function extractAdId($url)
{
    preg_match('/-ID([a-zA-Z0-9]+)\.html/', $url, $matches);

    if (isset($matches[1])) {
        return $matches[1];
    } else {
        error_log("Failed to extract ad ID from URL: $url");
        throw new Exception("Could not extract ad ID from the URL.");
    }
}






