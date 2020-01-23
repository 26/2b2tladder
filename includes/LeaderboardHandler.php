<?php

/**
 * Class LeaderboardHandler
 */
class LeaderboardHandler
{
    const LEADERBOARD_MOST_KILLS = 1;
    const LEADERBOARD_MOST_DEATHS = 2;
    const LEADERBOARD_MOST_JOINS = 3;
    const LEADERBOARD_MOST_LEAVES = 4;

    /**
     * @var CacheHandler
     */
    private $cache_handler;

    /**
     * @var IOHandler
     */
    private $io_handler;
    /**
     * @var HtmlRenderer
     */
    private $html_renderer;

    /**
     * LeaderboardHandler constructor.
     * @param CacheHandler $cache_handler
     * @param IOHandler $io_handler
     */
    public function __construct(CacheHandler &$cache_handler, IOHandler &$io_handler) {
        $this->cache_handler = $cache_handler;
        $this->io_handler = $io_handler;
    }

    /**
     * Loads the leaderboard data.
     * @param $type
     */
    public function loadLeaderboard($type) {
        if(!is_int($type) || $type < self::LEADERBOARD_MOST_KILLS || $type > self::LEADERBOARD_MOST_LEAVES) {
            throw new InvalidArgumentException("Leaderboard type is not valid.");
        }


    }

    /**
     * Renders the leaderboard.
     */
    public function renderLeaderboard() {
        if(!$this->is_loaded) {
            throw new LogicException("Tried to render leaderboard before it is loaded.");
        }

        $this->html_renderer = new HtmlRenderer();
    }
}