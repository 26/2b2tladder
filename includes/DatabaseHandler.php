<?php

class DatabaseHandler
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * Connect to the database.
     *
     * @param $database
     * @param $user
     * @param $password
     * @param string $host
     * @return bool
     */
    public function __construct($database, $user, $password, $host = '127.0.0.1') {
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            $this->connection = new PDO($dsn, $user, $password, $options);
        } catch(PDOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Returns the connection.
     *
     * @return PDO
     */
    public function getConnection() {
        if($this->connection) {
            return $this->connection;
        }

        throw new LogicException("Tried to get database connection, without creating one first.");
    }

    /**
     * Closes the database connection.
     */
    public function closeConnection() {
        unset($this->connection);
    }
}