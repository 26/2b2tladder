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
        } catch(Exception $e) {
            $this->renderError(500, "Something went wrong while trying to load this page.");
        }
    }

    /**
     * @throws Exception
     */
    public function render() {
        try {
            $query = new ApiQuery('https://api.2b2t.dev/', 'stats', ['username' => "Popbob"], 'username');
            $result = $this->cache_handler->doQuery($this->io_handler, $query);
        } catch(Exception $e) {
            $this->renderError(500, "Something went wrong while trying to load your profile.");
        }

        if(!$result) {
            $this->renderError(404, "This user was not found.");
        }

        $this->renderError(500, "Something went wrong while trying to load this page."); die();
        echo $result->getResult()->getID();
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
            $message // The title of the page
        );

        //$this->html_renderer->renderHeader(), // Default header
        //            $this->html_renderer->renderErrorPage( // Error page
        //                $this->html_renderer->renderErrorImage($code), // Error page image
        //                $this->html_renderer->renderErrorMessage($message) // Error page message
        //            ),
        //            $this->html_renderer->renderFooter() // Default footer

        die();
    }
}