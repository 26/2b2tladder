<?php

class DatabaseHandler
{
    const HOST = '127.0.0.1';

    const USER_CACHE_TABLE = 'user_cache';
    const LASTKILL_CACHE_TABLE = 'lastkill_cache';
    const LASTDEATH_CACHE_TABLE = 'lastdeath_cache';
    const SKIN_CACHE_TABLE = 'skin_cache';
    const USERSONLINE_CACHE_TABLE = 'usersonline_cache';
    const RENDEREDSKIN_CACHE_TABLE = 'renderedskin_cache';
    const RANKS_TABLE = 'ranks';
    const STATISTICS_TABLE = 'statistics';

    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var bool Set to true create non-existent tables.
     */
    private $REBUILD_TABLES = false;

    /**
     * Connect to the database.
     *
     * @param $database
     * @param $user
     * @param $password
     * @param string $host
     */
    public function __construct($database, $user, $password, $host = '127.0.0.1') {
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        $this->connection = new PDO($dsn, $user, $password, $options);

        if($this->REBUILD_TABLES) {
            $this->getConnection()->query("CREATE TABLE IF NOT EXISTS " . self::USER_CACHE_TABLE . " (`id` INT NOT NULL, `username` VARCHAR(255) NOT NULL, `uuid` VARCHAR(63) NOT NULL, `kills` INT NOT NULL, `deaths` INT NOT NULL, `joins` INT NOT NULL, `leaves` INT NOT NULL, `adminlevel` TINYINT NOT NULL, `cache_url` VARCHAR(255) NOT NULL, `cache_endpoint` VARCHAR(255) NOT NULL, `cache_query` VARCHAR(511) NOT NULL, `cache_time` BIGINT NOT NULL, PRIMARY KEY (id))");
            $this->getConnection()->query("CREATE TABLE IF NOT EXISTS " . self::LASTDEATH_CACHE_TABLE . " (`id` INT NOT NULL, `username` VARCHAR(255) NOT NULL, `date` CHAR(10) NOT NULL, `time` CHAR(8) NOT NULL, `message` VARCHAR(255) NOT NULL, `cache_url` VARCHAR(255) NOT NULL, `cache_endpoint` VARCHAR(255) NOT NULL, `cache_query` VARCHAR(511) NOT NULL, `cache_time` BIGINT NOT NULL, PRIMARY KEY (id))");
            $this->getConnection()->query("CREATE TABLE IF NOT EXISTS " . self::LASTKILL_CACHE_TABLE . " (`id` INT NOT NULL, `username` VARCHAR(255) NOT NULL, `date` CHAR(10) NOT NULL, `time` CHAR(8) NOT NULL, `message` VARCHAR(255) NOT NULL, `cache_url` VARCHAR(255) NOT NULL, `cache_endpoint` VARCHAR(255) NOT NULL, `cache_query` VARCHAR(511) NOT NULL, `cache_time` BIGINT NOT NULL, PRIMARY KEY (id))");
            $this->getConnection()->query("CREATE TABLE IF NOT EXISTS " . self::SKIN_CACHE_TABLE . " (`uuid` CHAR(64) NOT NULL, `skin` VARCHAR(4096) NOT NULL, `cache_time` BIGINT NOT NULL, PRIMARY KEY (uuid))");
            $this->getConnection()->query("CREATE TABLE IF NOT EXISTS " . self::USERSONLINE_CACHE_TABLE . " (`now` INT NOT NULL, `max` INT NOT NULL, `cache_url` VARCHAR(255) NOT NULL, `cache_endpoint` VARCHAR(255) NOT NULL, `cache_query` VARCHAR(511) NOT NULL, `cache_time` BIGINT NOT NULL)");
            $this->getConnection()->query("CREATE TABLE IF NOT EXISTS " . self::RENDEREDSKIN_CACHE_TABLE . " (`skin` BLOB NOT NULL, `username` CHAR(64) NOT NULL,  `cache_time` BIGINT NOT NULL, PRIMARY KEY (username))");
            $this->getConnection()->query("CREATE TABLE IF NOT EXISTS " . self::RANKS_TABLE . " (`type` CHAR(6) NOT NULL, `rank` INT NOT NULL, `uuid` CHAR(128) NOT NULL, `time` BIGINT NOT NULL)");
            $this->getConnection()->query("CREATE TABLE IF NOT EXISTS " . self::STATISTICS_TABLE . " (`type` CHAR(6) NOT NULL, `value` INT NOT NULL, `uuid` CHAR(128) NOT NULL, `time` BIGINT NOT NULL)");
        }
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

    public static function newFromConfig() {
        $config = [];
        require('/etc/2b2t/config.php');

        $database = $config['db']['database'];
        $user = $config['db']['user'];
        $password = $config['db']['pass'];

        $database_handler = new DatabaseHandler($database, $user, $password, self::HOST);

        unset($config);

        return $database_handler;
    }
}