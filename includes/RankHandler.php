<?php

class RankHandler {
    const KILLS_RANK = 1;
    const DEATHS_RANK = 2;
    const JOINS_RANK = 3;
    const LEAVES_RANK = 4;

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

    public function __construct() {
        $this->database_handler = DatabaseHandler::newFromConfig();
    }

    /**
     * @param Result $user_result
     */
    public function loadRanksFrom(Result $user_result) {
        $this->user_result = $user_result;

        $this->num_of_records = $this->getTotalRecords();

        $this->kills_rank = $this->getKillsRank();
        $this->deaths_rank = $this->getDeathsRank();
        $this->joins_rank = $this->getJoinsRank();
        $this->leaves_rank = $this->getLeavesRank();

        $this->kills_rank_percentage = $this->calculatePercentage($this->kills_rank);
        $this->deaths_rank_percentage = $this->calculatePercentage($this->deaths_rank);
        $this->joins_rank_percentage = $this->calculatePercentage($this->joins_rank);
        $this->leaves_rank_percentage = $this->calculatePercentage($this->leaves_rank);
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
}