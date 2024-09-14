<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Завантажуємо змінні середовища з .env файлу
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Отримуємо значення з .env
$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $db :" . $e->getMessage());
}

