<?php

namespace Api;

final class ApiMain
{
    /**
     * @var \CacheHandler
     */
    private $cache_handler;

    /**
     * @var \DatabaseHandler
     */
    private $database_handler;

    /**
     * @var ApiOutput
     */
    private $api_output;

    /**
     * @var ApiErrorFactory
     */
    private $api_error_factory;

    /**
     * ApiMain constructor.
     */
    public function __construct() {
        $this->api_output = new ApiOutput();
        $this->api_error_factory = new ApiErrorFactory();
        $this->cache_handler = new \CacheHandler();
        $this->database_handler = \DatabaseHandler::newFromConfig();
    }

    /**
     * Execute API request.
     * @throws \Exception
     */
    public function execute() {
        if(ApiOutput::isValidOutputMode($_GET['format'])) {
            $this->api_output->setOutputMode($_GET['format']);
        } else {
            $result = $this->api_error_factory->createInvalidParameterResult("format");
            $this->api_output->outputResult($result);
        }

        if(!isset($_GET['action'])) {
            $result = $this->api_error_factory->createMissingParameterResult("action");
            $this->api_output->outputResult($result);
        }
    }
}