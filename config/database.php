<?php

class Database {
    private string $host     = 'localhost';
    private string $dbname   = 'ticket_api';
    private string $username = 'root';
    private string $password = 'mysql';
    private ?PDO   $conn     = null;

    public function __construct() {}

    public function connect(): PDO {
        if ($this->conn === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        }
        return $this->conn;
    }
}
