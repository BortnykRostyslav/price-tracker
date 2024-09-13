<?php

class Subscription
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Створюємо підписку з токеном верифікації
    public function create($adId, $email, $verificationToken)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO subscriptions (ad_id, email, verification_token, email_verified) 
            VALUES (?, ?, ?, 0)
        ");
        return $stmt->execute([$adId, $email, $verificationToken]);
    }

    // Пошук підписок за ID оголошення (тільки підтверджені email)
    public function findByAdId($adId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions 
            WHERE ad_id = ? AND email_verified = 1
        ");
        $stmt->execute([$adId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Підтвердження email за токеном
    public function verifyEmail($token)
    {
        $stmt = $this->pdo->prepare("
            UPDATE subscriptions 
            SET email_verified = 1 
            WHERE verification_token = ? AND email_verified = 0
        ");
        return $stmt->execute([$token]);
    }

    // Отримання підписки за токеном (для перевірки токена)
    public function findByToken($token)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions 
            WHERE verification_token = ?
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
