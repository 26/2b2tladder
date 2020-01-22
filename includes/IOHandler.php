<?php

class IOHandler
{
    const ACCEPTED_URLS = [
        "https://api.2b2t.dev/"
    ];

    const ACCEPTED_ENDPOINTS = [
        "stats", # Gets the stats of one or all 2b2t users
        "prioq" # Currently unused
    ];

    const ACCEPTED_PARAMETERS = [
        "username",
        "lastkill",
        "lastdeath"
    ];

    /**
     * Executes the API request encoded in an ApiQuery object.
     *
     * @param ApiQuery $query
     * @return ApiResult
     * @throws HttpException
     * @throws Exception
     */
    public function doQuery(ApiQuery $query) {
        $url = $query->getURL();
        $endpoint = $query->getEndpoint();
        $parameters = $query->getParameters();

        $request = $url . $endpoint . '?' . http_build_query($parameters);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_URL, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        if(!$result) {
            // TODO

            throw new HttpException("Unable to connect to external host.");
        }

        switch($endpoint) {
            case 'stats':
            default:
                $result_array = json_decode($result, true)[0];
                return ApiResult::newFromArray($result_array, $query);
        }
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
}