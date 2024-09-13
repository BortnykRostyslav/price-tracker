<?php

class Subscription
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($adId, $email)
    {
        $stmt = $this->pdo->prepare("INSERT INTO subscriptions (ad_id, email) VALUES (?, ?)");
        return $stmt->execute([$adId, $email]);
    }

    public function findByAdId($adId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM subscriptions WHERE ad_id = ?");
        $stmt->execute([$adId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
