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
     * @var Result
     */
    private $result;

    public function __construct($result, ApiQuery $query)
    {
        $this->result = $result;
        $this->query = $query;
    }

    /**
     * @return Result
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
     * @return bool|Result
     */
    private static function createResultObject(array $result, ApiQuery $query) {
        if($query->getType() === 'username') {
            return ApiResult::createUserFromArray($result);
        }

        if($query->getType() === 'lastdeath') {
            return ApiResult::createLastDeathFromArray($result);
        }

        if($query->getType() === 'lastkill') {
            return ApiResult::createLastKillFromArray($result);
        }

        if($query->getType() === 'usersonline') {
            return ApiResult::createUsersOnlineFromArray($result);
        }

        throw new InvalidArgumentException("Type was not understood.");
    }

    /**
     * @param array $result
     * @return bool|UsersOnlineResult
     */
    private static function createUsersOnlineFromArray(array $result) {
        if(!isset($result['max'])        ||
            !isset($result['now']))  {
            return false;
        }

        if(!is_int($result['max']) ||
           !is_int($result['now'])) {
            return false;
        }

        return new UsersOnlineResult(
            $result['max'],
            $result['now']
        );
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
           !is_string($result['date'])     ||
           !is_string($result['time'])     ||
           !is_string($result['message']))    {
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
           !is_string($result['date'])     ||
           !is_string($result['time'])     ||
           !is_string($result['message']))    {
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
           !isset($result['adminlevel']))    {
            return false;
        }

        if(!is_int($result['id'])           ||
           !is_string($result['username'])  ||
           !is_string($result['uuid'])      ||
           !is_int($result['kills'])        ||
           !is_int($result['deaths'])       ||
           !is_int($result['joins'])        ||
           !is_int($result['leaves'])       ||
           !is_bool((bool)$result['adminlevel']))      {
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
            (bool)$result['adminlevel']
        );
    }
}