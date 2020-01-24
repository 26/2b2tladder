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
        try {
            $this->html_renderer->outputPage(
                "2b2t Ladder • Leaderboard",
                $this->html_renderer->renderHeader(),
                $this->html_renderer->renderHomePage(
                    $this->html_renderer->renderHomePageSearch(),
                    $this->html_renderer->renderWrapper(
                        $this->leaderboard_handler
                            ->loadLeaderboard(LeaderboardHandler::LEADERBOARD_MOST_LEAVES)
                            ->renderLeaderboard()
                    )
                )
            );
        } catch(Exception $e) {
            if(self::DEBUG) {
                die(nl2br($e));
            }

            $this->renderError(500, "Something went wrong while trying to load this page.");
        }
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