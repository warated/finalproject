<?php

namespace com\icemalta\kahuna\model;


use \JsonSerializable;
use \PDO;
use com\icemalta\kahuna\model\DBConnect;

class CustomerSupport implements JsonSerializable
{
    private static $db;
    private int $id;
    private $email;
    private $password;
    private $accessLevel = 'admin';

    public function __construct(string $email, string $password, ?string $accessLevel = 'admin', ?int $id = 0) 
    {
        $this->email = $email;
        $this->password = $password;
        $this->accessLevel = $accessLevel;
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getAccessLevel(): string
    {
        return $this->accessLevel;
    }

    public function setAccessLevel(string $accessLevel): self
    {
        $this->accessLevel = $accessLevel;
        return $this;
    }

    public function jsonSerialize():array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'accessLevel' => $this->accessLevel
        ];
        }

        public static function save(CustomerSupport $CustomerSupport): CustomerSupport
    {
        $hashed = password_hash($CustomerSupport->password, PASSWORD_DEFAULT);
        if ($CustomerSupport->getId() === 0) {
            // Insert
            $sql = 'INSERT INTO CustomerSupport(email, password, accessLevel) VALUES (:email, :password, :accessLevel)';
            $sth = self::$db->prepare($sql);
        } else {
            // Update
            $sql = 'UPDATE CustomerSupport SET email = :email, password = :password, accessLevel = :accessLevel WHERE id = :id';
            $sth = self::$db->prepare($sql);
            $sth->bindValue('id', $CustomerSupport->getId());
        }
        $sth->bindValue('email', $CustomerSupport->getEmail());
        $sth->bindValue('password', $hashed);
        $sth->bindValue('accessLevel', $CustomerSupport->accessLevel);
        $sth->execute();

        if ($sth->rowCount() > 0 && $CustomerSupport->getId() === 0) {
            $CustomerSupport->setId(self::$db->lastInsertId());
        }
        return $CustomerSupport;
    }
    public static function authenticate(CustomerSupport $CustomerSupport): ?CustomerSupport
    {
        $sql = 'SELECT * FROM CustomerSupport WHERE email = :email';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('email',$CustomerSupport->email);
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result && password_verify($CustomerSupport->password, $result->password)) {
            return new $CustomerSupport(
                $result->email,
                $result->password, 
                $result->accessLevel,
                $result->id
            );
        }
        return null;
    }

    public static function delete(CustomerSupport $CustomerSupport): bool
    {
        $sql = 'DELETE FROM CustomerSupport WHERE id = :id';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('id', $CustomerSupport->getId());
        $sth->execute();
        return $sth->rowCount() > 0;
    }
    }