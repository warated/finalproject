<?php
namespace com\icemalta\kahuna\model;


use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class ReplyTicket implements JsonSerializable
{
    private static $db;

    private int|string $ticket_id;
    private string $description;

    private int|string $id = 0;
    public function __construct(int|string $ticket_id, string $description, int|string $id = 0)
    {
        $this->ticket_id = $ticket_id;
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

    public function getTicketId(): int

    {
        return $this->ticket_id;
    }

    public function setTicketId(int $ticket_id): self
    {
        $this->ticket_id = $ticket_id;
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

    public static function save(ReplyTicket $replyTicket): ReplyTicket
    {
        if ($replyTicket->getId() == 0) {
            // New ReplyTicket (insert)
            $sql = 'INSERT INTO ReplyTicket(ticket_id, description) VALUES (:ticket_id, :description)';
            $sth = self::$db->prepare($sql);
    }   else{
        //Update ReplyTicket(update)
        $sql = 'UPDATE ReplyTicket SET ticket_id = :ticket_id, description = :description WHERE id = :id';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('id', $replyTicket->getId());
    }

    $sth->bindValue('ticket_id', $replyTicket->getTicketId());
    $sth->bindValue('description', $replyTicket->getDescription());
    $sth->execute();

        if($sth->rowCount() > 0 && $replyTicket->getId() === 0){
            $replyTicket->setId(self::$db->lastInsertId());
        }

return $replyTicket;

    }
    public static function load(): array
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $sql ='SELECT ticket_id, description, id FROM ReplyTicket';
        $sth = self::$db->prepare($sql);
        $sth->execute();
        $ticket = $sth->fetchAll(PDO::FETCH_FUNC, fn(...$fields) => new ReplyTicket(...$fields));
        return $ticket;
    }

}