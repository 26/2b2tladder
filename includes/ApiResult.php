<?php

/**
 * Class ApiResult
 */
class ApiResult
{
    /**
     * @var ApiQuery
     */
    private $query;

    /**
     * @var UserResult|LastKillResult|LastDeathResult
     */
    private $result;

    public function __construct($result, ApiQuery $query)
    {
        $this->result = $result;
        $this->query = $query;
    }

    /**
     * @return LastDeathResult|LastKillResult|UserResult
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * Returns the ApiQuery used to create this object.
     *
     * @return ApiQuery
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @param array $result
     * @param ApiQuery $query
     * @return ApiResult
     * @throws Exception
     */
    public static function newFromArray(array $result, ApiQuery $query) {
        if(!$result) {
            throw new InvalidArgumentException("Result must be populated.");
        }

        $result_object = ApiResult::createResultObject($result, $query);

        if(!$result_object) {
            throw new Exception("Invalid results.");
        }

        return new ApiResult($result_object, $query);
    }

    /**
     * @param array $result
     * @param ApiQuery $query
     * @return bool|LastDeathResult|LastKillResult|UserResult
     */
    private static function createResultObject(array $result, ApiQuery $query) {
        if(isset($query->getParameters()['username'])) {
            return ApiResult::createUserFromArray($result);
        }

        if(isset($query->getParameters()['lastdeath'])) {
            return ApiResult::createLastDeathFromArray($result);
        }

        if(isset($query->getParameters()['lastkill'])) {
            return ApiResult::createLastKillFromArray($result);
        }

        throw new LogicException("Invalid parameter supplied.");
    }

    /**
     * @param array $result
     * @return bool|LastDeathResult
     */
    private static function createLastDeathFromArray(array $result) {
        if(!isset($result['id'])        ||
           !isset($result['username'])  ||
           !isset($result['date'])      ||
           !isset($result['time'])      ||
           !isset($result['message']))  {
           return false;
        }

        if(!is_int($result['id'])          ||
           !is_string($result['username']) ||
           !is_int($result['date'])        ||
           !is_int($result['time'])        ||
           !is_int($result['message']))    {
           return false;
        }

        return new LastDeathResult(
            $result['id'],
            $result['username'],
            $result['date'],
            $result['time'],
            $result['message']
        );
    }

    /**
     * @param array $result
     * @return bool|LastKillResult
     */
    private static function createLastKillFromArray(array $result) {
        if(!isset($result['id'])        ||
           !isset($result['username'])  ||
           !isset($result['date'])      ||
           !isset($result['time'])      ||
           !isset($result['message']))  {
           return false;
        }

        if(!is_int($result['id'])          ||
           !is_string($result['username']) ||
           !is_int($result['date'])        ||
           !is_int($result['time'])        ||
           !is_int($result['message']))    {
           return false;
        }

        return new LastKillResult(
            $result['id'],
            $result['username'],
            $result['date'],
            $result['time'],
            $result['message']
        );
    }

    /**
     * @param $result
     * @return bool|UserResult
     */
    public static function createUserFromArray(array $result) {
        if(!isset($result['id'])        ||
           !isset($result['username'])  ||
           !isset($result['uuid'])      ||
           !isset($result['kills'])     ||
           !isset($result['deaths'])    ||
           !isset($result['joins'])     ||
           !isset($result['leaves'])    ||
           !isset($result['admin']))    {
            return false;
        }

        if(!is_int($result['id'])           ||
           !is_string($result['username'])  ||
           !is_string($result['uuid'])      ||
           !is_int($result['kills'])        ||
           !is_int($result['deaths'])       ||
           !is_int($result['joins'])        ||
           !is_int($result['leaves'])       ||
           !is_bool($result['admin']))      {
            return false;
        }

        return new UserResult(
            $result['id'],
            $result['username'],
            $result['uuid'],
            $result['kills'],
            $result['deaths'],
            $result['joins'],
            $result['leaves'],
            $result['admin']
        );
    }
}