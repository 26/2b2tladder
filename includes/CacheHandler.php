<?php

class CacheHandler
{
    const HOST = '127.0.0.1';
    const CACHE_INVALIDATION_TIME_LIMIT = 1800; // 0.5 hours

    const USER_CACHE_TABLE = 'user_cache';
    const LASTKILL_CACHE_TABLE = 'lastkill_cache';
    const LASTDEATH_CACHE_TABLE = 'lastdeath_cache';

    const USER_INSERT_QUERY = "INSERT INTO " . self::USER_CACHE_TABLE . " (`id`, `username`, `uuid`, `kills`, `deaths`, `joins`, `leaves`, `admin`, `cache_url`, `cache_endpoint`, `cache_query`, `cache_time`) VALUES (:id, :username, :uuid, :kills, :deaths, :joins, :leaves, :admin, :cache_url, :cache_endpoint, :cache_query, :cache_time)";
    const LASTKILL_INSERT_QUERY = "INSERT INTO " . self::LASTKILL_CACHE_TABLE . " (`id`, `username`, `date`, `time`, `message`, `cache_url`, `cache_endpoint`, `cache_query`, `cache_time`) VALUES (:id, :username, :date, :time, :message, :cache_url, :cache_endpoint, :cache_query, :cache_time)";
    const LASTDEATH_INSERT_QUERY = "INSERT INTO " . self::LASTDEATH_CACHE_TABLE . " (`id`, `username`, `date`, `time`, `message`, `cache_url`, `cache_endpoint`, `cache_query`, `cache_time`) VALUES (:id, :username, :date, :time, :message, :cache_url, :cache_endpoint, :cache_query, :cache_time)";

    protected $database;

    private $database_name = "tbtt";
    private $user = "tbtt";

    /**
     * CacheHandler constructor.
     */
    public function __construct()
    {
        $config = [];
        require('/etc/2b2t/config.php');

        $password = $config['db']['pass'];
        $this->database = new DatabaseHandler($this->database_name, $this->user, $password, CacheHandler::HOST);

        unset($config);
    }

    /**
     * CacheHandler destructor.
     */
    public function __destruct()
    {
        $this->database->closeConnection();
    }

    /**
     * @param ApiQuery $query
     * @return bool
     */
    public function isCached(ApiQuery $query)
    {
        $table = $this->getTableNameFromType($query->getType());

        try {
            $statement = $this->database->getConnection()->prepare("SELECT `time` FROM `$table` WHERE `url` = :url AND `endpoint` = :endpoint AND `query` = :query");
            $statement->execute([
                'url' => $query->getURL(),
                'endpoint' => $query->getEndpoint(),
                'query' => http_build_query($query->getParameters())
            ]);
        } catch(PDOException $exception) {
            return false;
        }

        return ($statement->rowCount() > 0 && $statement->fetch()['time'] + self::CACHE_INVALIDATION_TIME_LIMIT > time());
    }

    /**
     * @param ApiQuery $query
     * @return ApiResult
     * @throws Exception
     */
    public function getCacheResult(ApiQuery $query)
    {
        $type = $query->getType();
        $table = $this->getTableNameFromType($type);

        $statement = $this->database->getConnection()->prepare("SELECT * FROM `$table` WHERE `url` = :url AND `endpoint` = :endpoint AND `query` = :query AND `time` + :cache_time > :current_time");
        $statement->execute([
            'url' => $query->getURL(),
            'endpoint' => $query->getEndpoint(),
            'query' => http_build_query($query->getParameters()),
            'cache_time' => self::CACHE_INVALIDATION_TIME_LIMIT,
            'current_time' => time()
        ]);

        if($statement->rowCount() < 1) {
            throw new LogicException("Tried to get non-existent cache result.");
        }

        $result_object = ApiResult::newFromArray($statement->fetch(), $query);

        if(!$result_object) {
            throw new Exception("Invalid result object.");
        }

        return $result_object;
    }

    /**
     * @param ApiQuery $query
     * @return bool
     */
    public function clearCacheResult(ApiQuery $query)
    {
        $table = $this->getTableNameFromType($query->getType());

        try {
            $statement = $this->database->getConnection()->prepare("DELETE FROM `$table` WHERE `url` = :url AND `endpoint` = :endpoint AND `query` = :query");
            $statement->execute([
                'url' => $query->getURL(),
                'endpoint' => $query->getEndpoint(),
                'query' => http_build_query($query->getParameters())
            ]);
        } catch(PDOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param ApiResult $api_result
     * @return bool
     */
    public function cacheResult(ApiResult $api_result) {
        $result = $api_result->getResult();
        $result_type = $api_result->getQuery()->getType();

        switch($result_type) {
            case 'username':
                $query = self::USER_CACHE_TABLE;
                $parameters = [
                    'id' => $api_result->getResult()->getID(),
                    'username' => $api_result->getResult()->getUsername(),
                    'uuid' => $api_result->getResult()->getUUID(),
                    'kills' => $api_result->getResult()->getKills(),
                    'deaths' => $api_result->getResult()->getDeaths(),
                    'joins' => $api_result->getResult()->getJoins(),
                    'leaves' => $api_result->getResult()->getLeaves(),
                    'admin' => $api_result->getResult()->getAdminStatus(),
                    'cache_url' => $api_result->getQuery()->getURL(),
                    'cache_endpoint' => $api_result->getQuery()->getEndpoint(),
                    'cache_query' => json_encode($api_result->getQuery()->getParameters()),
                    'cache_time' => time()
                ];

                break;
            case 'lastkill':
                $query = self::LASTKILL_CACHE_TABLE;
                $parameters = [
                    'id' => $api_result->getResult()->getID(),
                    'username' => $api_result->getResult()->getUsername(),
                    'date' => $api_result->getResult()->getDate(),
                    'time' => $api_result->getResult()->getTime(),
                    'message' => $api_result->getResult()->getMessage(),
                    'cache_url' => $api_result->getQuery()->getURL(),
                    'cache_endpoint' => $api_result->getQuery()->getEndpoint(),
                    'cache_query' => json_encode($api_result->getQuery()->getParameters()),
                    'cache_time' => time()
                ];

                break;
            case 'lastdeath':
                $query = self::LASTDEATH_INSERT_QUERY;
                $parameters = [
                    'id' => $api_result->getResult()->getID(),
                    'username' => $api_result->getResult()->getUsername(),
                    'date' => $api_result->getResult()->getDate(),
                    'time' => $api_result->getResult()->getTime(),
                    'message' => $api_result->getResult()->getMessage(),
                    'cache_url' => $api_result->getQuery()->getURL(),
                    'cache_endpoint' => $api_result->getQuery()->getEndpoint(),
                    'cache_query' => json_encode($api_result->getQuery()->getParameters()),
                    'cache_time' => time()
                ];

                break;
            default:
                throw new LogicException("Invalid type.");
        }

        try {
            $this->clearCacheResult($api_result->getQuery());

            $statement = $this->database->getConnection()->prepare($query);
            $statement->execute($parameters);
        } catch(PDOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param $type
     * @return string
     */
    private function getTableNameFromType($type) {
        switch($type) {
            case 'username':
                return self::USER_CACHE_TABLE;
            case 'lastkill':
                return self::LASTKILL_CACHE_TABLE;
            case 'lastdeath':
                return self::LASTDEATH_CACHE_TABLE;
            default:
                throw new LogicException("Invalid type.");
        }
    }
}