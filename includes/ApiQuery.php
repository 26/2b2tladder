<?php

class ApiQuery
{
    const ACCEPTED_QUERY_TYPES = [
        "username",
        "lastdeath",
        "lastkill"
    ];

    private $endpoint = '';
    private $parameters = [];
    private $url;
    private $type;

    /**
     * ApiQuery constructor.
     *
     * @param $url
     * @param $endpoint
     * @param array $parameters
     * @param $type
     */
    public function __construct($url, $endpoint, array $parameters, $type) {
        if(!is_string($url)) {
            throw new InvalidArgumentException("URL must be of type string, " . gettype($url) . " provided.");
        }

        if(!is_string($endpoint)) {
            throw new InvalidArgumentException("Endpoint must be of type string, " . gettype($endpoint) . " provided.");
        }

        if(!is_string($type)) {
            throw new InvalidArgumentException("Type must be of type string, " . gettype($type) . " provided.");
        }

        if(!IOHandler::isAcceptedURL($url)) {
            throw new LogicException("Unaccepted URL (" . htmlspecialchars($url) . ").");
        }

        if(!IOHandler::isAcceptedEndpoint($endpoint)) {
            throw new LogicException("Unaccepted endpoint (" . htmlspecialchars($endpoint) . ").");
        }

        if(!IOHandler::isAcceptedParameterArray($parameters)) {
            throw new LogicException("Unaccepted parameters (" . implode(", ", $parameters) . ").");
        }

        if(!ApiQuery::isAcceptedQueryType($type)) {
            throw new LogicException("Unaccepted query type (" . htmlspecialchars($type) . ").");
        }

        $this->url = $url;
        $this->endpoint = $endpoint;
        $this->parameters = $parameters;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getURL() {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getEndpoint() {
        return $this->endpoint;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    private static function isAcceptedQueryType($type) {
        if(!is_string($type)) {
            return false;
        }

        if(!in_array($type, self::ACCEPTED_QUERY_TYPES)) {
            return false;
        }

        return true;
    }
}