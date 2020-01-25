<?php

class IOHandler
{
    const ACCEPTED_URLS = [
        "https://api.2b2t.dev/",
        "https://mcapi.us/"
    ];

    const ACCEPTED_ENDPOINTS = [
        "stats", # Gets the stats of one or all 2b2t users
        "prioq", # Currently unused,
        "server/status" # Fetches current server status
    ];

    const ACCEPTED_PARAMETERS = [
        "username",
        "lastkill",
        "lastdeath",
        "ip",
        "port"
    ];

    CONST SKIN_API_ENDPOINT = "https://crafatar.com/avatars/";

    /**
     * Executes the API request encoded in an ApiQuery object.
     *
     * @param ApiQuery $query
     * @return ApiResult|bool
     * @throws Exception
     */
    public function doQuery(ApiQuery $query) {
        $url = $query->getURL();
        $endpoint = $query->getEndpoint();
        $parameters = $query->getParameters();

        $request = $url . $endpoint . '?' . http_build_query($parameters);

        $result = $this->doExternalAPIRequest($request);

        if($result === "[]") {
            return false;
        }

        switch($endpoint) {
            case 'stats':
                $result_array = json_decode($result, true)[0];
                return ApiResult::newFromArray($result_array, $query);
            case 'server/status':
                $result_array = json_decode($result, true)['players'];
                return ApiResult::newFromArray($result_array, $query);
            default:
                return false;
        }
    }

    /**
     * Sends a request to our skins server and returns the result.
     *
     * @param $uuid
     * @return bool|string
     * @throws Exception
     */
    public function getSkinAsBase64($uuid) {
        if(!is_string($uuid)) {
            throw new InvalidArgumentException("UUID should be of type string.");
        }

        return base64_encode($this->doExternalAPIRequest(self::SKIN_API_ENDPOINT . $uuid));
    }

    /**
     * Checks if a given set of parameters are accepted.
     *
     * @param array $parameters
     * @return bool
     */
    public static function isAcceptedParameterArray(array $parameters) {
        foreach($parameters as $key => $parameter) {
            if(!is_string($key)) {
                return false;
            }

            if(!in_array($key, self::ACCEPTED_PARAMETERS)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a given URL is accepted.
     *
     * @param $url
     * @return bool
     */
    public static function isAcceptedURL($url) {
        if(!is_string($url)) {
            return false;
        }

        return in_array($url, self::ACCEPTED_URLS);
    }

    /**
     * Checks if a given endpoint is accepted.
     *
     * @param $endpoint
     * @return bool
     */
    public static function isAcceptedEndpoint($endpoint) {
        if(!is_string($endpoint)) {
            return false;
        }

        return in_array($endpoint, self::ACCEPTED_ENDPOINTS);
    }

    /**
     * @param string $request
     * @return bool|string
     * @throws Exception
     */
    private function doExternalAPIRequest($request) {
        if(!is_string($request)) {
            throw new InvalidArgumentException("Request should be of type string.");
        }

        if(!filter_var($request, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Request should be a valid URL.");
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_URL, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        if(!$result) {
            throw new Exception("Unable to connect to external host.");
        }

        return $result;
    }
}