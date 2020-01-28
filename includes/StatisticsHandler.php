<?php

/**
 * Class StatisticsHandler
 *
 * Handles storing historical statistics.
 */
class StatisticsHandler {
    const KILLS_RANK_TYPE = 'kills';
    const DEATHS_RANK_TYPE = 'deaths';
    const JOINS_RANK_TYPE = 'joins';
    const LEAVES_RANK_TYPE = 'leaves';

    const ALLOWED_TYPES = [
        self::KILLS_RANK_TYPE,
        self::DEATHS_RANK_TYPE,
        self::JOINS_RANK_TYPE,
        self::LEAVES_RANK_TYPE
    ];

    /**
     * @var DatabaseHandler
     */
    private $database_handler;

    /**
     * StatisticsHandler constructor.
     */
    public function __construct() {
        $this->database_handler = DatabaseHandler::newFromConfig();
    }

    /**
     * @param $type
     * @param $value
     * @param $uuid
     */
    public function storeRecord($type, $value, $uuid) {
        if(!is_string($type) || !is_int($value) || !is_string($uuid)) {
            throw new InvalidArgumentException();
        }

        if(!in_array($type, self::ALLOWED_TYPES)) {
            throw new InvalidArgumentException("Type is invalid.");
        }

        $time = time();

        // TODO: Only store ranks when API cache expires

        $statement = $this->database_handler->getConnection()->prepare(
            "INSERT INTO `" . DatabaseHandler::STATISTICS_TABLE . "` (" .
            "`type`," .
            "`value`," .
            "`uuid`," .
            "`time`" .
            ") VALUES " .
            "(?, ?, ?, $time)"
        );

        $statement->execute([$type, $value, $uuid]);
    }
}