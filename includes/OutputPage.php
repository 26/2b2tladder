<?php

class OutputPage
{
    const DEBUG = true;

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

        $uri_parts = explode('/', trim($uri, '/'));

        try {
            if($uri_parts[0] === 'profile') {
                if(count($uri_parts) > 2 || !isset($uri_parts[1]) || !ctype_alnum($uri_parts[1])) {
                    $this->renderError(400, "Invalid profile name");
                }

                (new UserPage())->loadUserPage($uri_parts[1])->render();

                return;
            }

            switch($uri) {
                case '/':
                case '/home':
                    $this->html_renderer->outputPage(
                        "2b2t Ladder • Leaderboard",
                        $this->html_renderer->renderHeader("home"),
                        $this->html_renderer->renderHomePage(
                            $this->html_renderer->renderHomePageSearch(),
                            $this->html_renderer->renderWrapper(
                                $this->renderStats(),
                                $this->leaderboard_handler
                                    ->loadLeaderboard(LeaderboardHandler::LEADERBOARD_MOST_KILLS)
                                    ->renderLeaderboard()
                            )
                        )
                    );

                    return;
                case '/search':
                    $search_handler = new SearchHandler();

                    if(!isset($_POST['search'])) {
                        $search_term = '';
                    } else {
                        $search_term = $_POST['search'];
                    }

                    $this->html_renderer->outputPage(
                        "2b2t Ladder • Search results",
                        $this->html_renderer->renderHeader(),
                        $this->html_renderer->renderWrapper(
                            $this->html_renderer->renderSearch(
                                $this->html_renderer->renderText(
                                    "Search results for '" . $search_term . "'"
                                ),
                                $search_handler
                                    ->doSearch($search_term)
                                    ->renderSearch()
                            )
                        )
                    );

                    return;
                case '/ladder/kills':
                    $this->html_renderer->outputPage(
                        "2b2t Ladder • Most kills",
                        $this->html_renderer->renderHeader(),
                        $this->html_renderer->renderWrapper(
                            $this->html_renderer->renderTag(
                                'br',
                                []
                            ),
                            $this->leaderboard_handler
                                ->loadLeaderboard(LeaderboardHandler::LEADERBOARD_MOST_KILLS)
                                ->renderLeaderboard()
                        )
                    );

                    return;
                case '/ladder/deaths':
                    $this->html_renderer->outputPage(
                        "2b2t Ladder • Most deaths",
                        $this->html_renderer->renderHeader(),
                        $this->html_renderer->renderWrapper(
                            $this->html_renderer->renderTag(
                                'br',
                                []
                            ),
                            $this->leaderboard_handler
                                ->loadLeaderboard(LeaderboardHandler::LEADERBOARD_MOST_DEATHS)
                                ->renderLeaderboard()
                        )
                    );

                    return;
                case '/ladder/joins':
                    $this->html_renderer->outputPage(
                        "2b2t Ladder • Most joins",
                        $this->html_renderer->renderHeader(),
                        $this->html_renderer->renderWrapper(
                            $this->html_renderer->renderTag(
                                'br',
                                []
                            ),
                            $this->leaderboard_handler
                                ->loadLeaderboard(LeaderboardHandler::LEADERBOARD_MOST_JOINS)
                                ->renderLeaderboard()
                        )
                    );

                    return;
                case '/ladder/leaves':
                    $this->html_renderer->outputPage(
                        "2b2t Ladder • Most leaves",
                        $this->html_renderer->renderHeader(),
                        $this->html_renderer->renderWrapper(
                            $this->html_renderer->renderTag(
                                'br',
                                []
                            ),
                            $this->leaderboard_handler
                                ->loadLeaderboard(LeaderboardHandler::LEADERBOARD_MOST_LEAVES)
                                ->renderLeaderboard()
                        )
                    );

                    return;
                default:
                    $this->renderError(404, "Page not found.");
            }
        } catch(Exception $e) {
            if(self::DEBUG) {
                die(nl2br($e));
            }

            $this->renderError(500, "Something went wrong while trying to load this page.");
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
     * @throws Exception
     */
    public function renderError($code, $message) {
        if(!is_int($code) || !is_string($message)) {
            throw new InvalidArgumentException();
        }

        http_response_code($code);

        /*
        $this->html_renderer->renderPage( // Render default page
            $message, // The title of the page
            $this->html_renderer->renderHeader(), // Default header
            $this->html_renderer->renderErrorPage( // Error page
                $this->html_renderer->renderErrorImage($code), // Error page image
                $this->html_renderer->renderErrorMessage($message) // Error page message
            ),
            $this->html_renderer->renderFooter() // Default footer
        );
        */

        die();
    }
}