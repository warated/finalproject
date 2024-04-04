<?php
namespace com\icemalta\kahuna\model;


use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class SupportTicket implements JsonSerializable
{
    private static $db;

    private string $name;
    private int|string $id = 0;
    private string $description;
    public function __construct(string $description, string $name, int|string $id = 0)
    {
        $this->name = $name;
        $this->description = $description;
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

    public function getName(): string

    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->description = $name;
        return $this;
    }

    public function getDescription(): string

    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public static function save(SupportTicket $SupportTicket): SupportTicket
    {
        if ($SupportTicket->getId() == 0) {
            // New SupportTicket (insert)
            $sql = 'INSERT INTO SupportTicket(name, description) VALUES (:name, :description)';
            $sth = self::$db->prepare($sql);
    }   else{
        //Update SupportTicket(update)
        $sql = 'UPDATE SupportTicket SET name = :name, description = :description WHERE id = :id';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('id', $SupportTicket->getId());
    }

    $sth->bindValue('name', $SupportTicket->getName());
    $sth->bindValue('description', $SupportTicket->getDescription());
    $sth->execute();

        if($sth->rowCount() > 0 && $SupportTicket->getId() === 0){
            $SupportTicket->setId(self::$db->lastInsertId());
        }

return $SupportTicket;

    }
    public static function load(): array
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $sql ='SELECT name, description, id FROM SupportTicket';
        $sth = self::$db->prepare($sql);
        $sth->execute();
        $products = $sth->fetchAll(PDO::FETCH_FUNC, fn(...$fields) => new SupportTicket(...$fields));
        return $products;
    }

}