<?php

class OutputPage
{
    /**
     * @var CacheHandler
     */
    private $cache_handler;

    /**
     * @var IOHandler|null
     */
    private $io_handler;

    /**
     * OutputPage constructor.
     */
    public function __construct() {
        $this->cache_handler = new CacheHandler();
        $this->io_handler = new IOHandler();
    }

    /**
     * @throws HttpException
     * @throws Exception
     */
    public function render() {
        $query = new ApiQuery('https://api.2b2t.dev/', 'stats', ['username' => "Marijn"]);

        if($this->cache_handler->isCached($query)) {
            $result = $this->cache_handler->getCacheResult($query);
        } else {
            $result = $this->io_handler->doQuery($query);
            $this->cache_handler->cacheResult($result);
        }

        $result = $result->getResult();

        // TODO: Create tables

        echo $result->getID();
    }

    public function renderError() {
        // TODO

        echo "Something went wrong... :(";
    }
}