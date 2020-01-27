<?php

class OutputPage
{
    const DEBUG = false;

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
     * @var LeaderboardHandler
     */
    private $leaderboard_handler;

    /**
     * OutputPage constructor.
     *
     * @throws Exception
     */
    public function __construct() {
        try {
            $this->html_renderer = new HtmlRenderer();
        } catch(Exception $e) {
            http_response_code(500);

            // I should probably make this more user-friendly.
            die("Unable to load page... Please reload.");
        }

        try {
            $this->cache_handler = new CacheHandler();
            $this->io_handler = new IOHandler();
            $this->leaderboard_handler = new LeaderboardHandler($this->cache_handler, $this->io_handler);
        } catch(Exception $e) {
            if(self::DEBUG) {
                die(nl2br($e));
            }

            $this->renderError(500, "Something went wrong while trying to load this page.");
        }
    }

    /**
     * @throws Exception
     */
    public function render() {
        $uri = $_SERVER['REQUEST_URI'];

        list($namespace, $parameter) = explode('/', trim($uri, '/'));

        try {
            switch($namespace) {
                case '':
                case 'home':
                    $this->html_renderer->outputPage(
                        "2b2tladder",
                        $this->html_renderer->renderHeader("home"),
                        $this->html_renderer->renderHomePage(
                            $this->html_renderer->renderHomePageSearch(),
                            $this->html_renderer->renderWrapper(
                                $this->renderStats(),
                                $this->leaderboard_handler
                                    ->loadLeaderboard(LeaderboardHandler::LEADERBOARD_MOST_KILLS)
                                    ->renderLeaderboard(),
                                $this->html_renderer->renderFooter()
                            )
                        )
                    );

                    return;
                case 'search':
                    $search_handler = new SearchHandler();

                    if(!isset($_POST['search'])) {
                        $search_term = '';
                    } else {
                        $search_term = $_POST['search'];
                    }

                    $this->html_renderer->outputPage(
                        "2b2tladder • Search results",
                        $this->html_renderer->renderHeader(),
                        $this->html_renderer->renderWrapper(
                            $this->html_renderer->renderSearch(
                                $this->html_renderer->renderText(
                                    "Search results for '" . $search_term . "'"
                                ),
                                $search_handler
                                    ->doSearch($search_term)
                                    ->renderSearch()
                            ),
                            $this->html_renderer->renderFooter()
                        )
                    );

                    return;
                case 'ladder':
                    switch($parameter) {
                        case 'kills':
                            $leaderboard = LeaderboardHandler::LEADERBOARD_MOST_KILLS;
                            break;
                        case 'deaths':
                            $leaderboard = LeaderboardHandler::LEADERBOARD_MOST_DEATHS;
                            break;
                        case 'joins':
                            $leaderboard = LeaderboardHandler::LEADERBOARD_MOST_JOINS;
                            break;
                        case 'leaves':
                            $leaderboard = LeaderboardHandler::LEADERBOARD_MOST_LEAVES;
                            break;
                        default:
                            $this->renderError(404, "Page not found.");
                            return;
                    }

                    $this->html_renderer->outputPage(
                        "2b2tladder • Most $parameter",
                        $this->html_renderer->renderHeader(),
                        $this->html_renderer->renderWrapper(
                            $this->html_renderer->renderTag(
                                'br',
                                []
                            ),
                            $this->leaderboard_handler
                                ->loadLeaderboard($leaderboard)
                                ->renderLeaderboard(),
                            $this->html_renderer->renderFooter()
                        )
                    );

                    return;
                case 'profile':
                    if(!isset($parameter)) {
                        $this->renderError(400, "Invalid profile name", "The profile name must not be empty.");
                    }

                    (new UserPage())->loadUserPage($parameter)->render();

                    return;
                case 'more':
                    switch($parameter) {
                        case 'discord':
                            header('Location: https://discord.gg/DeexSGT');
                            return;
                        case 'faq':
                            $this->html_renderer->outputPage(
                                "2b2tladder • FAQ",
                                $this->html_renderer->renderHeader(),
                                $this->html_renderer->renderWrapper(
                                    $this->html_renderer->renderFAQ(),
                                    $this->html_renderer->renderFooter()
                                )
                            );
                            return;
                        default:
                            $this->renderError(404, "Page not found");
                            return;
                    }
                default:
                    $this->renderError(404, "Page not found");
            }
        } catch(Exception $e) {
            if(self::DEBUG) {
                die(nl2br($e));
            }

            $this->renderError(500, "Something went wrong", "Oh no... Something went (terribly) wrong while trying to load this page.");
        }
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderStats() {
        $query = new ApiQuery("https://mcapi.us/", "server/status", ["ip" => "2b2t.org"], "usersonline");
        $result = $this->cache_handler->doQuery($this->io_handler, $query);

        if(!$result) {
            return $this->html_renderer->renderEmptyTag('div', []);
        }

        $current_online = $result->getResult()->getNow();
        $current_max = $result->getResult()->getMax();

        return $this->html_renderer->renderTag(
            'div',
            ['class' => 'stats'],
            $this->html_renderer->renderTag(
                'table',
                [],
                $this->html_renderer->renderTag(
                    'tr',
                    [],
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$current_online
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            (string)$current_max
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
                            "Currently online"
                        )
                    ),
                    $this->html_renderer->renderTag(
                        'td',
                        [],
                        $this->html_renderer->renderText(
                            "Maximum online"
                        )
                    )
                )
            )
        );
    }

    /**
     * @param $code
     * @param $message
     * @param null $description
     * @throws Exception
     */
    public static function renderError($code, $message, $description =  null) {
        if(!is_int($code) || !is_string($message) || ($description && !is_string($description))) {
            throw new InvalidArgumentException();
        }

        http_response_code($code);

        $html_renderer = new HtmlRenderer();

        $html_renderer->outputPage( // Render default page
            "2b2tladder • " . $message, // The title of the page
            $html_renderer->renderHeader(), // Default header
            $html_renderer->renderWrapper(
                $html_renderer->renderErrorPage( // Error page
                    $html_renderer->renderErrorMessage($message, $code), // Error page message
                    $html_renderer->renderErrorDescription($description)
                ),
                $html_renderer->renderFooter() // Default footer
            )
        );

        die();
    }
}