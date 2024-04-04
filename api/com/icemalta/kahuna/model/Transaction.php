<?php
namespace com\icemalta\kahuna\model;


use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class Transaction implements JsonSerializable
{
    private static $db;
    private int $transactionId;
    private int $userId;
    private int $productId;
    private string $warranty_start_date; 
    private string $warranty_end_date;   
    private string $purchase_date;      

    public function __construct(int $transactionId, int $userId, int $productId, string $warranty_start_date, string $warranty_end_date, string $purchase_date)
    {
        $this->transactionId = $transactionId;
        $this->userId = $userId;
        $this->productId = $productId;
        $this->warranty_start_date = $warranty_start_date;
        $this->warranty_end_date = $warranty_end_date;
        $this->purchase_date = $purchase_date;
        self::$db = DBConnect::getInstance()->getConnection();

    }
    // Getters for all properties
    public function getTransactionId(): int
    {
        return $this->transactionId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getWarrantyStartDate(): string
    {
        return $this->warranty_start_date;
    }

    public function getWarrantyEndDate(): string
    {
        return $this->warranty_end_date;
    }

    public function getPurchaseDate(): string
    {
        return $this->purchase_date;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function setWarrantyStartDate(string $warranty_start_date): void
    {
        $this->warranty_start_date = $warranty_start_date;
    }

    public function setWarrantyEndDate(string $warranty_end_date): void
    {
        $this->warranty_end_date = $warranty_end_date;
    }

    public function setPurchaseDate(string $purchase_date): void
    {
        $this->purchase_date = $purchase_date;
    }
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
    public static function buy(Transaction $transaction): bool
    {
        self::$db = DBConnect::getInstance()->getConnection();

        $sql = "INSERT INTO Transaction (user_id, product_id, warranty_start_date, warranty_end_date, purchase_date) VALUES (:userId, :productId, :warranty_start_date, :warranty_end_date, :purchase_date)";
        $stmt = self::$db->prepare($sql);

        // Assigning values to variables first to avoid passing by reference issue
        $userId = $transaction->getUserId();
        $productId = $transaction->getProductId();
        $warrantyStartDate = $transaction->getWarrantyStartDate();
        $warrantyEndDate = $transaction->getWarrantyEndDate();
        $purchaseDate = $transaction->getPurchaseDate();

        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':warranty_start_date', $warrantyStartDate, PDO::PARAM_STR);
        $stmt->bindParam(':warranty_end_date', $warrantyEndDate, PDO::PARAM_STR);
        $stmt->bindParam(':purchase_date', $purchaseDate, PDO::PARAM_STR);

        try {
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Purchase failed: " . $e->getMessage());
            return false;
        }
}

function calculateWarrantyEndDate(int $productId): string
{
    $db = DBConnect::getInstance()->getConnection();

    $sql = "SELECT warrantyLength FROM Product WHERE id = :productId";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $warrantyLength = (int) $row['warrantyLength'];
    } else {
        error_log("Warranty length not found for product with ID: " . $productId);
        return "0000-00-00";
    }

    $purchaseDate = date('Y-m-d');
    $warrantyEndDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $warrantyLength . ' years'));

    return $warrantyEndDate;
}

public static function load(): array
{
    self::$db = DBConnect::getInstance()->getConnection();
    // Assuming productId is also selected from the database but was omitted earlier
    $sql ='SELECT transaction_id, purchase_date, user_id, product_id, warranty_start_date, warranty_end_date FROM Transaction';
    $sth = self::$db->prepare($sql);
    $sth->execute();
    $transactions = $sth->fetchAll(PDO::FETCH_FUNC, function($transactionId, $purchaseDate, $userId, $productId, $warrantyStartDate, $warrantyEndDate) {
        // Explicitly cast the values to match the expected types in the Transaction constructor
        $transactionId = (int) $transactionId;
        $userId = (int) $userId;
        $productId = (int) $productId; // Correctly casting productId to int
        // Assuming warranty_start_date, warranty_end_date, and purchase_date are strings and do not need casting
        return new Transaction($transactionId, $userId, $productId, $warrantyStartDate, $warrantyEndDate, $purchaseDate);
    });
    return $transactions;
}

}