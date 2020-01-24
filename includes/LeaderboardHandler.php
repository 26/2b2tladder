<?php

/**
 * Class LeaderboardHandler
 */
class LeaderboardHandler
{
    const LEADERBOARD_MOST_KILLS = "kills";
    const LEADERBOARD_MOST_DEATHS = "deaths";
    const LEADERBOARD_MOST_JOINS = "joins";
    const LEADERBOARD_MOST_LEAVES = "leaves";

    const ALLOWED_LEADERBOARDS = [
        self::LEADERBOARD_MOST_KILLS,
        self::LEADERBOARD_MOST_DEATHS,
        self::LEADERBOARD_MOST_JOINS,
        self::LEADERBOARD_MOST_LEAVES
    ];

    /**
     * @var int
     */
    public $leaderboard_type;

    /**
     * @var bool
     */
    public $is_loaded = false;

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
     * @var DatabaseHandler
     */
    private $database;

    /**
     * @var array
     */
    private $leaderboard;

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
     * @return LeaderboardHandler
     */
    public function loadLeaderboard($type) {
        if($this->is_loaded) {
            throw new LogicException('Tried to load leaderboard twice.');
        }

        if(!in_array($type, self::ALLOWED_LEADERBOARDS)) {
            throw new InvalidArgumentException("Leaderboard type is not valid.");
        }

        $this->database = DatabaseHandler::newFromConfig();
        $this->leaderboard = $this->loadLeaderboardFromCache($type);
        $this->is_loaded = true;

        return $this;
    }

    /**
     * Renders the leaderboard.
     *
     * @throws Exception
     * @return Tag
     */
    public function renderLeaderboard() {
        if(!$this->leaderboard || !$this->is_loaded) {
            throw new LogicException("Tried to render leaderboard before it is loaded.");
        }

        $this->html_renderer = new HtmlRenderer();

        return $this->html_renderer->renderTag(
            'div',
            [
                'class' => 'leaderboard'
            ],
            $this->html_renderer->renderTag(
                'table',
                ['class' => 'leaderboard-table'],
                $this->html_renderer->renderTag(
                    'thead',
                    ['class' => 'leaderboard-header'],
                    $this->html_renderer->renderTag(
                        'tr',
                        [],
                        $this->html_renderer->renderTag(
                            'th',
                            ['class' => 'leaderboard-header-row-index'],
                            $this->html_renderer->renderText(
                                '#'
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'th',
                            [],
                            $this->html_renderer->renderText(
                                ''
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'th',
                            [],
                            $this->html_renderer->renderText(
                                'Username'
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'th',
                            [],
                            $this->html_renderer->renderText(
                                'Kills'
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'th',
                            [],
                            $this->html_renderer->renderText(
                                'Deaths'
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'th',
                            [],
                            $this->html_renderer->renderText(
                                'Joins'
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'th',
                            [],
                            $this->html_renderer->renderText(
                                'Leaves'
                            )
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'tbody',
                        [],
                        ...$this->renderLeaderboardRows()
                    )
                )
            )
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    private function renderLeaderboardRows() {
        if(!$this->leaderboard || !$this->is_loaded) {
            throw new LogicException("Tried to render leaderboard before it is loaded.");
        }

        if(!$this->html_renderer) {
            $this->html_renderer = new HtmlRenderer();
        }

        $rows = [];
        $idx_user = 1;

        foreach($this->leaderboard as $row) {
            $rows[] = $this->html_renderer->renderTag(
                'tr',
                [
                    'class' => 'leaderboard-item',
                    'data-username' => $row['username']
                ],
                $this->html_renderer->renderTag(
                    'td',
                    [],
                    $this->html_renderer->renderText(
                        (string)$idx_user
                    )
                ),
                $this->html_renderer->renderTag(
                    'td',
                    ['class' => 'skin-image'],
                    $this->html_renderer->renderSkin(
                        $this->cache_handler->loadSkin($row['uuid'])
                    )
                ),
                $this->html_renderer->renderTag(
                    'td',
                    [],
                    $this->html_renderer->renderText(
                        $row['username']
                    )
                ),
                $this->html_renderer->renderTag(
                    'td',
                    [],
                    $this->html_renderer->renderText(
                        (string)$row['kills']
                    )
                ),
                $this->html_renderer->renderTag(
                    'td',
                    [],
                    $this->html_renderer->renderText(
                        (string)$row['deaths']
                    )
                ),
                $this->html_renderer->renderTag(
                    'td',
                    [],
                    $this->html_renderer->renderText(
                        (string)$row['joins']
                    )
                ),
                $this->html_renderer->renderTag(
                    'td',
                    [],
                    $this->html_renderer->renderText(
                        (string)$row['leaves']
                    )
                )
            );

            $idx_user++;
        }

        return $rows;
    }

    /**
     * @param $type
     * @return array
     */
    private function loadLeaderboardFromCache($type) {
        if(!in_array($type, self::ALLOWED_LEADERBOARDS)) {
            throw new InvalidArgumentException("Leaderboard type is not valid.");
        }

        $this->leaderboard_type = $type;

        $database_connection = $this->database->getConnection();
        $statement = $database_connection->prepare("SELECT * FROM " . DatabaseHandler::USER_CACHE_TABLE . " ORDER BY " . $type . " DESC LIMIT 250");
        $statement->execute();

        return $statement->fetchAll();
    }
}