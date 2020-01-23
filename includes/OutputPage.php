<?php

class OutputPage
{
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
            // I should probably make this more user-friendly.
            die("Unable to load page... Please reload.");
        }

        try {
            $this->cache_handler = new CacheHandler();
            $this->io_handler = new IOHandler();
            $this->leaderboard_handler = new LeaderboardHandler($this->cache_handler, $this->io_handler);
        } catch(Exception $e) {
            $this->renderError(500, "Something went wrong while trying to load this page.");
        }
    }

    /**
     * @throws Exception
     */
    public function render() {
        $this->leaderboard_handler->loadLeaderboard(LeaderboardHandler::LEADERBOARD_MOST_KILLS); # Load default leaderboard to get displayed

        $this->html_renderer->renderPage(
            "2b2t Ladder &bull; 2b2t Leaderboard",
            $this->html_renderer->renderHeader(),
            $this->html_renderer->renderHomePage(
                $this->html_renderer->renderHomePageSearch(),
                $this->leaderboard_handler->renderLeaderboard()
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

        $this->html_renderer->renderPage( // Render default page
            $message, // The title of the page
            $this->html_renderer->renderHeader(), // Default header
            $this->html_renderer->renderErrorPage( // Error page
                $this->html_renderer->renderErrorImage($code), // Error page image
                $this->html_renderer->renderErrorMessage($message) // Error page message
            ),
            $this->html_renderer->renderFooter() // Default footer
        );

        die();
    }
}