<?php

class ApiQuery
{
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
     */
    public function __construct($url, $endpoint, array $parameters) {
        if(!is_string($url)) {
            throw new InvalidArgumentException("URL must be of type string, " . gettype($endpoint) . " provided.");
        }

        if(!is_string($endpoint)) {
            throw new InvalidArgumentException("Endpoint must be of type string, " . gettype($endpoint) . " provided.");
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

        $this->url = $url;
        $this->endpoint = $endpoint;
        $this->parameters = $parameters;

        $this->type = array_keys($parameters)[0];
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
}