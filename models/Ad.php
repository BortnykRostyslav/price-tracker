<?php

class Ad
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByUrl($url)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ads WHERE url = ?");
        $stmt->execute([$url]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($url, $price)
    {
        $stmt = $this->pdo->prepare("INSERT INTO ads (url, price) VALUES (?, ?)");
        $stmt->execute([$url, $price]);
        return $this->pdo->lastInsertId();
    }

    public function updatePrice($id, $newPrice)
    {
        $stmt = $this->pdo->prepare("UPDATE ads SET price = ? WHERE id = ?");
        return $stmt->execute([$newPrice, $id]);
    }
}
