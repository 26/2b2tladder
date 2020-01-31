<?php

class UserPage
{
    const PERCENTAGE_PRECISION = 2;
    /**
     * @var HtmlRenderer
     */
    private $html_renderer;

    /**
     * @var SkinRenderHandler
     */
    private $skin_renderer;

    /**
     * @var DatabaseHandler
     */
    private $database;

    /**
     * @var CacheHandler
     */
    private $cache_handler;

    /**
     * @var IOHandler
     */
    private $io_handler;

    /**
     * @var bool
     */
    private $user_exists = false;

    /**
     * @var string
     */
    private $username;

    /**
     * @var ApiResult
     */
    private $user_result;

    /**
     * @var ApiResult
     */
    private $last_kill_result;

    /**
     * @var ApiResult
     */
    private $last_death_result;

    /**
     * @var string
     */
    private $skin_render;

    /**
     * @var bool
     */
    private $legacy_username = false;

    /**
     * @var bool|string
     */
    private $skin_icon_base64;

    /**
     * @var RankHandler
     */
    private $rank_handler;

    /**
     * @var bool
     */
    private $time_cached;

    /**
     * @var Tag
     */
    private $leaves_chart;

    /**
     * @var Tag
     */
    private $joins_chart;

    /**
     * @var Tag
     */
    private $deaths_chart;

    /**
     * @var Tag
     */
    private $kills_chart;

    /**
     * @var Tag
     */
    private $rank_kills_chart;

    /**
     * @var Tag
     */
    private $rank_deaths_chart;

    /**
     * @var Tag
     */
    private $rank_joins_chart;

    /**
     * @var Tag
     */
    private $rank_leaves_chart;

    /**
     * UserPage constructor.
     */
    public function __construct() {
        $this->cache_handler = new CacheHandler();
        $this->database = DatabaseHandler::newFromConfig();
        $this->skin_renderer = new SkinRenderHandler($this->database);
        $this->html_renderer = new HtmlRenderer();
        $this->io_handler = new IOHandler();
        $this->rank_handler = new RankHandler();
    }

    /**
     * @param $username
     * @return $this
     * @throws Exception
     */
    public function loadUserPage($username) {
        if(!is_string($username)) {
            throw new InvalidArgumentException("Username must be of type string.");
        }

        if(!$this->loadExternalData($username)) {
            return $this;
        }

        $this->username = $username;
        $this->skin_render = $this->skin_renderer->getSkin($this->username);

        // Refresh skin cache if necessary.
        if(!$this->cache_handler->isSkinCached($this->user_result->getResult()->getUUID()) || $this->cache_handler->skinCachedFor($this->user_result->getResult()->getUUID()) > CacheHandler::CACHE_INVALIDATION_TIME_LIMIT * 4) {
            $this->skin_icon_base64 = $this->io_handler->getSkinAsBase64($this->user_result->getResult()->getUUID());

            $this->cache_handler->clearCacheSkin($this->user_result->getResult()->getUUID());
            $this->cache_handler->cacheSkin($this->user_result->getResult()->getUUID(), $this->skin_icon_base64);

            $statistics_handler = new StatisticsHandler();

            $statistics_handler->storeRecord(StatisticsHandler::KILLS_RANK_TYPE, $this->user_result->getResult()->getKills(), $this->user_result->getResult()->getUUID());
            $statistics_handler->storeRecord(StatisticsHandler::DEATHS_RANK_TYPE, $this->user_result->getResult()->getDeaths(), $this->user_result->getResult()->getUUID());
            $statistics_handler->storeRecord(StatisticsHandler::JOINS_RANK_TYPE, $this->user_result->getResult()->getJoins(), $this->user_result->getResult()->getUUID());
            $statistics_handler->storeRecord(StatisticsHandler::LEAVES_RANK_TYPE, $this->user_result->getResult()->getLeaves(), $this->user_result->getResult()->getUUID());
        } else {
            $this->skin_icon_base64 = $this->cache_handler->getCachedSkin($this->user_result->getResult()->getUUID())['skin'];
        }

        $this->rank_handler->loadRanksFrom($this->user_result->getResult());

        try {
            $this->rank_handler->storeRanks();
        } catch(Exception $e) {}

        $chart_factory = new ChartFactory();

        $this->kills_chart = $chart_factory->drawChart(
            "kills",
            $this->getStatisticsHistory($this->user_result->getResult()->getUUID(), StatisticsHandler::KILLS_RANK_TYPE)
        );

        $this->deaths_chart = $chart_factory->drawChart(
            "deaths",
            $this->getStatisticsHistory($this->user_result->getResult()->getUUID(), StatisticsHandler::DEATHS_RANK_TYPE)
        );

        $this->joins_chart = $chart_factory->drawChart(
            "joins",
            $this->getStatisticsHistory($this->user_result->getResult()->getUUID(), StatisticsHandler::JOINS_RANK_TYPE)
        );

        $this->leaves_chart = $chart_factory->drawChart(
            "leaves",
            $this->getStatisticsHistory($this->user_result->getResult()->getUUID(), StatisticsHandler::LEAVES_RANK_TYPE)
        );

        return $this;
    }

    /**
     * @throws Exception
     */
    public function render() {
        if(!$this->user_exists) {
            // Page was not loaded or user does not exist.
            OutputPage::renderError(
                404,
                "User not found",
                "The user you are looking for was not found. This could mean you misspelled their name, they haven't joined in a while or their username changed."
            );
        }

        $chart = new ChartFactory();

        $this->html_renderer->outputPage(
            "2b2tladder • $this->username",
            $this->html_renderer->renderHeader(),
            $this->html_renderer->renderWrapper(
                $this->html_renderer->renderTag(
                    'div',
                    ['class' => 'user-page container'],
                    $this->html_renderer->renderTag(
                        'div',
                        ['class' => 'user-page-header'],
                        $this->html_renderer->renderTag(
                            'div',
                            ['class' => 'skin-container'],
                            $this->html_renderer->renderTag(
                                'img',
                                [
                                    'class' => 'skin-render',
                                    'src' => 'data:image/png;base64,' . $this->skin_render,
                                    'alt' => ''
                                ]
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'div',
                            ['class' => 'user-page-title'],
                            $this->html_renderer->renderTag(
                                'h1',
                                [],
                                $this->html_renderer->renderText(
                                    $this->user_result->getResult()->getUsername()
                                )
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'div',
                            ['class' => 'last-refreshed'],
                            $this->html_renderer->renderTag(
                                'p',
                                [],
                                $this->html_renderer->renderText(
                                    $this->getLastUpdatedMessage()
                                )
                            )
                        )
                    ),
                    $this->html_renderer->renderEmptyTag(
                        'div',
                        ['class' => 'line']
                    ),
                    $this->html_renderer->renderTag(
                        'div',
                        ['class' => 'user-page-content'],
                        $this->html_renderer->renderTag(
                            'div',
                            ['class' => 'row'],
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box'],
                                $this->html_renderer->renderTag(
                                    'h1',
                                    [],
                                    $this->html_renderer->renderText(
                                        'Profile Info'
                                    )
                                ),
                                $this->renderProfileInfo()
                            ),
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box'],
                                $this->html_renderer->renderTag(
                                    'h1',
                                    [],
                                    $this->html_renderer->renderText(
                                        'Kill stats'
                                    )
                                ),
                                $this->renderKillInfo()
                            ),
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box'],
                                $this->html_renderer->renderTag(
                                    'h1',
                                    [],
                                    $this->html_renderer->renderText(
                                        'Death stats'
                                    )
                                ),
                                $this->renderDeathInfo()
                            ),
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box'],
                                $this->html_renderer->renderTag(
                                    'h1',
                                    [],
                                    $this->html_renderer->renderText(
                                        'Join stats'
                                    )
                                ),
                                $this->renderJoinInfo()
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'div',
                            ['class' => 'row'],
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box tab-container'],
                                $this->html_renderer->renderTag(
                                    'div',
                                    ['class' => 'tab'],
                                    $this->html_renderer->renderTag(
                                        'button',
                                        ['class' => 'tablink active', 'onclick' => 'openTab(event, \'kills-chart\')'],
                                        $this->html_renderer->renderText(
                                            "Total kills"
                                        )
                                    ),
                                    $this->html_renderer->renderTag(
                                        'button',
                                        ['class' => 'tablink', 'onclick' => 'openTab(event, \'deaths-chart\')'],
                                        $this->html_renderer->renderText(
                                            "Total deaths"
                                        )
                                    ),
                                    $this->html_renderer->renderTag(
                                        'button',
                                        ['class' => 'tablink', 'onclick' => 'openTab(event, \'joins-chart\')'],
                                        $this->html_renderer->renderText(
                                            "Total joins"
                                        )
                                    ),
                                    $this->html_renderer->renderTag(
                                        'button',
                                        ['class' => 'tablink', 'onclick' => 'openTab(event, \'leaves-chart\')'],
                                        $this->html_renderer->renderText(
                                            "Total leaves"
                                        )
                                    )
                                ),
                                $this->html_renderer->renderTag(
                                    'div',
                                    ['class' => 'tab-content', 'id' => 'kills-chart'],
                                    $this->kills_chart
                                ),
                                $this->html_renderer->renderTag(
                                    'div',
                                    ['class' => 'tab-content', 'id' => 'deaths-chart'],
                                    $this->deaths_chart
                                ),
                                $this->html_renderer->renderTag(
                                    'div',
                                    ['class' => 'tab-content', 'id' => 'joins-chart'],
                                    $this->joins_chart
                                ),
                                $this->html_renderer->renderTag(
                                    'div',
                                    ['class' => 'tab-content', 'id' => 'leaves-chart'],
                                    $this->leaves_chart
                                )
                            )
                        )
                    )
                ),
                $this->html_renderer->renderFooter()
            )
        );

    }

    /**
     * @throws Exception
     */
    private function renderKillInfo() {
        return $this->html_renderer->renderTag(
            'table',
            [],
            $this->html_renderer->renderTag(
                'tbody',
                [],
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Kills'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->user_result->getResult()->getKills()
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'World Rank'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->rank_handler->kills_rank . ' (' . round($this->rank_handler->kills_rank_percentage * 100, self::PERCENTAGE_PRECISION) . '%)'
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Best Rank'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->rank_handler->getBestRank(RankHandler::KILLS_RANK_TYPE)
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'K/D ratio'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->renderKDRatio()
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Last kill'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        ['class' => 'skin-icon'],
                        $this->renderLastKillMessage()
                    )
                )
            )
        );
    }

    /**
     * @throws Exception
     */
    private function renderDeathInfo() {
        return $this->html_renderer->renderTag(
            'table',
            [],
            $this->html_renderer->renderTag(
                'tbody',
                [],
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Deaths'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->user_result->getResult()->getDeaths()
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'World Rank'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->rank_handler->deaths_rank . ' (' . round($this->rank_handler->deaths_rank_percentage * 100, self::PERCENTAGE_PRECISION) . '%)'
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Best Rank'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->rank_handler->getBestRank(RankHandler::DEATHS_RANK_TYPE)
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Last death'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        ['class' => 'skin-icon'],
                        $this->renderLastDeathMessage()
                    )
                )
            )
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderKDRatio() {
        if($this->user_result->getResult()->getDeaths() === 0) {
            return $this->html_renderer->renderInlineError(
                'Not available'
            );
        }

        $kd_ratio = round($this->user_result->getResult()->getKills() / $this->user_result->getResult()->getDeaths(), 2);

        return $this->html_renderer->renderText(
            (string)$kd_ratio
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderLastKillMessage() {
        if(!$this->last_kill_result) {
            return $this->html_renderer->renderInlineError(
                "Not available"
            );
        }

        return $this->html_renderer->renderTag(
            'span',
            ['title' => $this->last_kill_result->getResult()->getDate() . " " . $this->last_kill_result->getResult()->getTime() . " (" . $this->last_kill_result->getResult()->getMessage() . ")"],
            $this->html_renderer->renderText(
                $this->last_kill_result->getResult()->getDate() . " " . $this->last_kill_result->getResult()->getTime()
            )
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderLastDeathMessage() {
        if(!$this->last_death_result) {
            return $this->html_renderer->renderInlineError(
                "Not available"
            );
        }

        return $this->html_renderer->renderTag(
            'span',
            ['title' => $this->last_death_result->getResult()->getDate() . " " . $this->last_death_result->getResult()->getTime() . " (" . $this->last_death_result->getResult()->getMessage() . ")"],
            $this->html_renderer->renderText(
                $this->last_death_result->getResult()->getDate() . " " . $this->last_death_result->getResult()->getTime()
            )
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderProfileInfo() {
        if(!$this->user_result) {
            return $this->html_renderer->renderError("Unable to get profile info");
        }

        return $this->html_renderer->renderTag(
            'table',
            [],
            $this->html_renderer->renderTag(
                'tbody',
                [],
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Username'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            $this->user_result->getResult()->getUsername()
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'UUID'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            $this->user_result->getResult()->getUUID()
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Admin status'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderBooleanIcon($this->user_result->getResult()->getAdminStatus())
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Skin icon'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        ['class' => 'skin-icon'],
                        $this->html_renderer->renderTag(
                            'img',
                            [
                                'src' => 'data:image/png;base64,' . $this->skin_icon_base64,
                                'alt' => ''
                            ]
                        )
                    )
                )
            )
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderJoinInfo(){
        return $this->html_renderer->renderTag(
            'table',
            [],
            $this->html_renderer->renderTag(
                'tbody',
                [],
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Joins'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->user_result->getResult()->getJoins()
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Leaves'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->user_result->getResult()->getLeaves()
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Rank (joins)'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->rank_handler->joins_rank . ' (' . round($this->rank_handler->joins_rank_percentage * 100, self::PERCENTAGE_PRECISION) . '%)'
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            'Rank (leaves)'
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$this->rank_handler->leaves_rank . ' (' . round($this->rank_handler->leaves_rank_percentage * 100, self::PERCENTAGE_PRECISION) . '%)'
                        )
                    )
                )
            )
        );
    }

    /**
     * @param $username
     * @return bool
     * @throws Exception
     */
    private function loadExternalData($username) {
        $user_query = new ApiQuery('https://api.2b2t.dev/', 'stats', ['username' => $username], 'username');
        $lastkill_query = new ApiQuery('https://api.2b2t.dev/', 'stats', ['lastkill' => $username], 'lastkill');
        $lastdeath_query = new ApiQuery('https://api.2b2t.dev/', 'stats', ['lastdeath' => $username], 'lastdeath');

        if(!$this->user_result = $this->cache_handler->doQuery($this->io_handler, $user_query)) {
            return false;
        }

        $this->time_cached = $this->cache_handler->isCachedFor($user_query);

        $this->user_exists = true;

        $this->last_kill_result = $this->cache_handler->doQuery($this->io_handler, $lastkill_query);
        $this->last_death_result = $this->cache_handler->doQuery($this->io_handler, $lastdeath_query);

        return true;
    }

    /**
     * @return string
     */
    private function getLastUpdatedMessage() {
        $updated_minutes = floor($this->time_cached / 60);

        if($updated_minutes < 3) return "Last updated just now";
        return "Last updated $updated_minutes minutes ago";
    }

    /**
     * @param $uuid
     * @param $type
     * @return array
     */
    private function getStatisticsHistory($uuid, $type) {
        $statement = $this->database->getConnection()->prepare("SELECT `time`, `value` FROM " . DatabaseHandler::STATISTICS_TABLE . " WHERE `uuid` = ? AND `type` = ? ORDER BY `time`");
        $statement->execute([$uuid, $type]);

        return $statement->fetchAll();
    }

    private function getRankHistory($uuid, $rank) {
        $statement = $this->database->getConnection()->prepare("SELECT `time`, `rank` FROM " . DatabaseHandler::RANKS_TABLE . " WHERE `uuid` = ? AND `type` = ? ORDER BY `time`");
        $statement->execute([$uuid, $rank]);

        return $statement->fetchAll();
    }
}