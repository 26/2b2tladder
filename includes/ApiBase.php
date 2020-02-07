<?php

namespace Api;

/**
 * Interface ApiBase
 * @package Api
 */
interface ApiBase
{
    const LIMIT = 500;

    /**
     * Executes the request with the given ApiParams class.
     *
     * @param ApiParams $params
     */
    public static function execute(ApiParams $params);

    /**
     * Returns an array of strings containing a list of allowed parameters.
     *
     * @return array
     */
    public static function getAllowedParams();

    /**
     * Returns the result of the request as an InternalApiResult.
     *
     * @return array
     */
    public static function getResult();

    /**
     * Returns true if and only if authentication is required for this endpoint.
     *
     * @return bool
     */
    public static function needsAuthentication();
}