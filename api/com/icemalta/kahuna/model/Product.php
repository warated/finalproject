<?php
namespace com\icemalta\kahuna\model;


use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class Product implements JsonSerializable
{
    private static $db;
    private int|string $id = 0;
    private string $serial;
    private string $name;
    private int $warrantyLength = 0;

    public function __construct(string $serial, string $name, int $warrantyLength, int|string $id = 0)
    {
        $this->serial = $serial;
        $this->name = $name;
        $this->warrantyLength = $warrantyLength;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self

    {
        $this->id = $id;
        return $this;
    }

    public function getSerial(): string

    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self 
    {
        $this->name = $name;
        return $this;
    }

    public function getWarrantyLength(): int
    {
        return $this->warrantyLength;
    }
    public function setWarrantyLength(int $warrantyLength): self
    {
        $this->warrantyLength = $warrantyLength;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }


    public static function save(Product $product): Product
    {
        if ($product->getId() == 0) {
            // New product (insert)
            $sql = 'INSERT INTO Product(serial, name, warrantyLength) VALUES (:serial, :name, :warrantyLength)';
            $sth = self::$db->prepare($sql);
    }   else{
        //Update product(update)
        $sql = 'UPDATE Product SET serial = :serial, name = :name, warrantyLength = :warrantyLength WHERE id = :id';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('id', $product->getId());
    }
    $sth->bindValue('serial', $product->getSerial());
    $sth->bindValue('name', $product->getName());
    $sth->bindValue('warrantyLength', $product->getWarrantyLength());
    $sth->execute();

        if($sth->rowCount() > 0 && $product->getId() === 0){
            $product->setId(self::$db->lastInsertId());
        }

return $product;

    }
    public static function load(): array
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $sql ='SELECT serial, name, warrantyLength, id FROM Product';
        $sth = self::$db->prepare($sql);
        $sth->execute();
        $products = $sth->fetchAll(PDO::FETCH_FUNC, fn(...$fields) => new Product(...$fields));
        return $products;
    }


    //This will make sure that the serial number is unique and not registered.
    public static function existsBySerial(string $serial): bool
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $sql = 'SELECT COUNT(*) FROM Product WHERE serial = :serial';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('serial', $serial);
        $sth->execute();
        $count = $sth->fetchColumn(); 
        return $count > 0; 
    }

    //used ChatGPT Asisstance, this will handle the serial number strictly on what I added
    public static function isAllowedSerial(string $serial): bool 
    {
    
        $allowedSerialNumbers = ["KHWM8199911", "KHWM8199912", "KHMW789991", "KHWP890001", "KHWP890002", "KHSS988881", "KHSS988882", "KHSS988883", "KHHM89762", "KHSB0001"];
        return in_array($serial, $allowedSerialNumbers);
    }

  
}


