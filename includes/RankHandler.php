<?php

class RankHandler {
    const KILLS_RANK_TYPE = 'kills';
    const DEATHS_RANK_TYPE = 'deaths';
    const JOINS_RANK_TYPE = 'joins';
    const LEAVES_RANK_TYPE = 'leaves';

    public $kills_rank;
    public $deaths_rank;
    public $joins_rank;
    public $leaves_rank;

    public $kills_rank_percentage;
    public $deaths_rank_percentage;
    public $joins_rank_percentage;
    public $leaves_rank_percentage;

    private $num_of_records;

    /**
     * @var DatabaseHandler
     */
    private $database_handler;
    /**
     * @var UserResult
     */
    private $user_result;

    /**
     * @var DatabaseHandler
     */
    private $database;

    public function __construct() {
        $this->database_handler = DatabaseHandler::newFromConfig();
    }

    /**
     * @param Result $user_result
     */
    public function loadRanksFrom(Result $user_result) {
        $this->user_result = $user_result;

        $this->database = DatabaseHandler::newFromConfig();
        $this->num_of_records = $this->getTotalRecords();

        $this->kills_rank = $this->getKillsRank();
        $this->deaths_rank = $this->getDeathsRank();
        $this->joins_rank = $this->getJoinsRank();
        $this->leaves_rank = $this->getLeavesRank();

        $this->kills_rank_percentage = $this->calculatePercentage($this->kills_rank);
        $this->deaths_rank_percentage = $this->calculatePercentage($this->deaths_rank);
        $this->joins_rank_percentage = $this->calculatePercentage($this->joins_rank);
        $this->leaves_rank_percentage = $this->calculatePercentage($this->leaves_rank);

        try {
            $this->storeRanks();
        } catch(Exception $e) {}
    }

    /**
     * @param $type
     * @return string
     */
    public function getBestRank($type) {
        if(!$this->user_result) {
            throw new LogicException("Tried accessing rank data before loading user");
        }

        if(!is_string($type)) {
            throw new InvalidArgumentException("Type must be of type string");
        }

        if($type !== self::LEAVES_RANK_TYPE && $type !== self::JOINS_RANK_TYPE && $type !== self::DEATHS_RANK_TYPE && $type !== self::KILLS_RANK_TYPE) {
            throw new InvalidArgumentException("Type is not a valid constant");
        }

        $statement = $this->database->getConnection()->prepare("SELECT MAX(`rank`) AS maximum FROM " . DatabaseHandler::RANKS_TABLE . " WHERE `uuid` = ? AND `type` = ?");
        $statement->execute([$this->user_result->getUUID(), $type]);

        return $statement->fetch()['maximum'];
    }

    public function getTotalRecords() {
        $statement = $this->database_handler->getConnection()->prepare("SELECT COUNT(*) AS `count` FROM " . DatabaseHandler::USER_CACHE_TABLE );
        $statement->execute([]);

        return $statement->fetch()['count'];
    }

    /**
     * @return int
     */
    public function getDeathsRank() {
        $statement = $this->database_handler->getConnection()->prepare("SELECT COUNT(*) AS `count` FROM " . DatabaseHandler::USER_CACHE_TABLE . " WHERE `deaths` > ?");
        $statement->execute([$this->user_result->getDeaths()]);

        return $statement->fetch()['count'] + 1;
    }

    /**
     * @return int
     */
    public function getKillsRank() {
        $statement = $this->database_handler->getConnection()->prepare("SELECT COUNT(*) AS `count` FROM " . DatabaseHandler::USER_CACHE_TABLE . " WHERE `kills` > ?");
        $statement->execute([$this->user_result->getKills()]);

        return $statement->fetch()['count'] + 1;
    }

    /**
     * @return int
     */
    public function getJoinsRank() {
        $statement = $this->database_handler->getConnection()->prepare("SELECT COUNT(*) AS `count` FROM " . DatabaseHandler::USER_CACHE_TABLE . " WHERE `joins` > ?");
        $statement->execute([$this->user_result->getJoins()]);

        return $statement->fetch()['count'] + 1;
    }

    /**
     * @return int
     */
    public function getLeavesRank() {
        $statement = $this->database_handler->getConnection()->prepare("SELECT COUNT(*) AS `count` FROM " . DatabaseHandler::USER_CACHE_TABLE . " WHERE `leaves` > ?");
        $statement->execute([$this->user_result->getLeaves()]);

        return $statement->fetch()['count'] + 1;
    }

    /**
     * @param $rank
     * @return float
     */
    private function calculatePercentage($rank) {
        return $rank / $this->num_of_records;
    }

    /**
     * Stores the calculated ranks.
     */
    private function storeRanks() {
        if(!$this->user_result) {
            throw new LogicException("Tried accessing rank data before loading user");
        }

        $uuid = $this->user_result->getUUID();
        $time = time();

        // TODO: Only store ranks when API cache expires

        $statement = $this->database->getConnection()->prepare(
            "INSERT INTO `" . DatabaseHandler::RANKS_TABLE . "` (" .
                "`type`," .
                "`rank`," .
                "`uuid`," .
                "`time`" .
            ") VALUES " .
            "('" . self::KILLS_RANK_TYPE  . "', ?, ?, $time)," .
            "('" . self::DEATHS_RANK_TYPE . "', ?, ?, $time)," .
            "('" . self::JOINS_RANK_TYPE  . "', ?, ?, $time)," .
            "('" . self::LEAVES_RANK_TYPE . "', ?, ?, $time)"
        );

        $statement->execute(
            [
                $this->kills_rank, $uuid,
                $this->deaths_rank, $uuid,
                $this->joins_rank, $uuid,
                $this->leaves_rank, $uuid
            ]
        );
    }
}