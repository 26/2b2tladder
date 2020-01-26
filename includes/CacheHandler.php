<?php

class CacheHandler
{
    const CACHE_INVALIDATION_TIME_LIMIT = 1800; // 0.5 hours

    const USER_INSERT_QUERY = "INSERT INTO `" . DatabaseHandler::USER_CACHE_TABLE . "` (`id`, `username`, `uuid`, `kills`, `deaths`, `joins`, `leaves`, `adminlevel`, `cache_url`, `cache_endpoint`, `cache_query`, `cache_time`) VALUES (:id, :username, :uuid, :kills, :deaths, :joins, :leaves, :admin, :cache_url, :cache_endpoint, :cache_query, :cache_time)";
    const LASTKILL_INSERT_QUERY = "INSERT INTO `" . DatabaseHandler::LASTKILL_CACHE_TABLE . "` (`id`, `username`, `date`, `time`, `message`, `cache_url`, `cache_endpoint`, `cache_query`, `cache_time`) VALUES (:id, :username, :date, :time, :message, :cache_url, :cache_endpoint, :cache_query, :cache_time)";
    const LASTDEATH_INSERT_QUERY = "INSERT INTO `" . DatabaseHandler::LASTDEATH_CACHE_TABLE . "` (`id`, `username`, `date`, `time`, `message`, `cache_url`, `cache_endpoint`, `cache_query`, `cache_time`) VALUES (:id, :username, :date, :time, :message, :cache_url, :cache_endpoint, :cache_query, :cache_time)";
    const USERSONLINE_INSERT_QUERY = "INSERT INTO `" . DatabaseHandler::USERSONLINE_CACHE_TABLE . "` (`max`, `now`, `cache_url`, `cache_endpoint`, `cache_query`, `cache_time`) VALUES (:max, :now, :cache_url, :cache_endpoint, :cache_query, :cache_time)";

    protected $database;

    private $io_handler;

    /**
     * CacheHandler constructor.
     */
    public function __construct()
    {
        $this->database = DatabaseHandler::newFromConfig();
        $this->io_handler = new IOHandler();
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
            $statement = $this->database->getConnection()->prepare("SELECT `cache_time` FROM `$table` WHERE `cache_url` = :url AND `cache_endpoint` = :endpoint AND `cache_query` = :query");
            $statement->execute([
                'url' => $query->getURL(),
                'endpoint' => $query->getEndpoint(),
                'query' => http_build_query($query->getParameters())
            ]);
        } catch(PDOException $exception) {
            return false;
        }

        return ($statement->rowCount() > 0 && $statement->fetch()['cache_time'] + self::CACHE_INVALIDATION_TIME_LIMIT > time());
    }

    public function isCachedFor(ApiQuery $query)
    {
        $table = $this->getTableNameFromType($query->getType());

        try {
            $statement = $this->database->getConnection()->prepare("SELECT `cache_time` FROM `$table` WHERE `cache_url` = :url AND `cache_endpoint` = :endpoint AND `cache_query` = :query");
            $statement->execute([
                'url' => $query->getURL(),
                'endpoint' => $query->getEndpoint(),
                'query' => http_build_query($query->getParameters())
            ]);
        } catch(PDOException $exception) {
            return false;
        }

        if(!$statement->rowCount()) {
            throw new LogicException("Tried to get cached time of nonexistent object.");
        }

        return time() - $statement->fetch()['cache_time'];
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

        $statement = $this->database->getConnection()->prepare("SELECT * FROM `$table` WHERE `cache_url` = :url AND `cache_endpoint` = :endpoint AND `cache_query` = :query AND `cache_time` + :cache_time > :current_time");
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
            $statement = $this->database->getConnection()->prepare("DELETE FROM `$table` WHERE `cache_url` = :url AND `cache_endpoint` = :endpoint AND `cache_query` = :query");
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
        $result_type = $api_result->getQuery()->getType();

        switch($result_type) {
            case 'username':
                $query = self::USER_INSERT_QUERY;
                $parameters = [
                    'id' => $api_result->getResult()->getID(),
                    'username' => $api_result->getResult()->getUsername(),
                    'uuid' => $api_result->getResult()->getUUID(),
                    'kills' => $api_result->getResult()->getKills(),
                    'deaths' => $api_result->getResult()->getDeaths(),
                    'joins' => $api_result->getResult()->getJoins(),
                    'leaves' => $api_result->getResult()->getLeaves(),
                    'admin' => (int)$api_result->getResult()->getAdminStatus(),
                    'cache_url' => $api_result->getQuery()->getURL(),
                    'cache_endpoint' => $api_result->getQuery()->getEndpoint(),
                    'cache_query' => http_build_query($api_result->getQuery()->getParameters()),
                    'cache_time' => time()
                ];

                break;
            case 'lastkill':
                $query = self::LASTKILL_INSERT_QUERY;
                $parameters = [
                    'id' => $api_result->getResult()->getID(),
                    'username' => $api_result->getResult()->getUsername(),
                    'date' => $api_result->getResult()->getDate(),
                    'time' => $api_result->getResult()->getTime(),
                    'message' => $api_result->getResult()->getMessage(),
                    'cache_url' => $api_result->getQuery()->getURL(),
                    'cache_endpoint' => $api_result->getQuery()->getEndpoint(),
                    'cache_query' => http_build_query($api_result->getQuery()->getParameters()),
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
                    'cache_query' => http_build_query($api_result->getQuery()->getParameters()),
                    'cache_time' => time()
                ];

                break;
            case 'usersonline':
                $query = self::USERSONLINE_INSERT_QUERY;
                $parameters = [
                    'max' => $api_result->getResult()->getMax(),
                    'now' => $api_result->getResult()->getNow(),
                    'cache_url' => $api_result->getQuery()->getURL(),
                    'cache_endpoint' => $api_result->getQuery()->getEndpoint(),
                    'cache_query' => http_build_query($api_result->getQuery()->getParameters()),
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
            die($exception);
            return false;
        }

        return true;
    }

    /**
     * Checks if a query is cached and returns the cached result if it is, else it does a new query.
     *
     * @param IOHandler $io_handler
     * @param ApiQuery $query
     * @return ApiResult
     * @throws Exception
     */
    public function doQuery(IOHandler &$io_handler, ApiQuery $query) {
        if($this->isCached($query)) {
            $result = $this->getCacheResult($query);
        } else {
            $result = $io_handler->doQuery($query);

            if($result !== false) {
                if(!$this->cacheResult($result)) {
                    throw new Exception("Unable to cache result.");
                }
            }
        }

        return $result;
    }

    /**
     * Checks if a skin has ever been cached.
     *
     * Skin caching and fetching is very expensive. If the skin has been cached ever, use that. A skin cache is only updated when a profile with an outdated skin gets visited.
     *
     * @param $uuid
     * @return bool
     */
    public function isSkinCached($uuid) {
        if(!is_string($uuid)) {
            throw new InvalidArgumentException("UUID must be of type string.");
        }

        try {
            $statement = $this->database->getConnection()->prepare("SELECT * FROM " . DatabaseHandler::SKIN_CACHE_TABLE . " WHERE `uuid` = ?");
            $statement->execute([$uuid]);
        } catch(Exception $e) {
            return false;
        }

        return ($statement->rowCount() > 0);
    }

    /**
     * Returns how long a skin has been cached for.
     *
     * @param $uuid
     * @return bool|int
     */
    public function skinCachedFor($uuid) {
        if(!is_string($uuid)) {
            throw new InvalidArgumentException("UUID must be of type string.");
        }

        try {
            $statement = $this->database->getConnection()->prepare("SELECT cache_time FROM " . DatabaseHandler::SKIN_CACHE_TABLE . " WHERE `uuid` = ?");
            $statement->execute([$uuid]);
        } catch(Exception $e) {
            return false;
        }

        if($statement->rowCount() === 0) return -1;

        return ($statement->fetch()['cache_time'] - time());
    }

    /**
     * Caches a base64 encoded skin.
     *
     * @param $uuid
     * @param $skin_base64
     * @return bool
     */
    public function cacheSkin($uuid, $skin_base64) {
        if(!is_string($uuid)) {
            throw new InvalidArgumentException("UUID must be of type string.");
        }

        if(!is_string($skin_base64) || !base64_decode($skin_base64)) {
            throw new InvalidArgumentException("Skin must be of type string (base64).");
        }

        try {
            $statement = $this->database->getConnection()->prepare("INSERT INTO " . DatabaseHandler::SKIN_CACHE_TABLE . " (`uuid`, `skin`, `cache_time`) VALUES (?, ?, ?)");
            $statement->execute([$uuid, $skin_base64, time()]);
        } catch(Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns the base64 encoded string of the cached skin.
     *
     * @param $uuid
     * @return bool|mixed
     */
    public function getCachedSkin($uuid) {
        try {
            $statement = $this->database->getConnection()->prepare("SELECT * FROM `" . DatabaseHandler::SKIN_CACHE_TABLE . "` WHERE `uuid` = :uuid");
            $statement->execute([
                'uuid' => $uuid
            ]);

            if($statement->rowCount() < 1) {
                throw new LogicException("Tried to get non-existent cache result.");
            }
        } catch(Exception $e) {
            return false;
        }

        return $statement->fetch();
    }

    /**
     * @param $uuid
     * @return bool
     */
    public function clearCacheSkin($uuid) {
        if(!is_string($uuid)) {
            throw new InvalidArgumentException("UUID must be of type string.");
        }

        try {
            $statement = $this->database->getConnection()->prepare("DELETE FROM " . DatabaseHandler::SKIN_CACHE_TABLE . " WHERE `uuid` = ?");
            $statement->execute([$uuid]);
        } catch(Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $uuid
     * @return bool|mixed
     * @throws Exception
     */
    public function loadSkin($uuid) {
        if(!is_string($uuid)) {
            throw new InvalidArgumentException("UUID must be of type string.");
        }

        $uuid = str_replace("-", "", $uuid);

        if($this->isSkinCached($uuid)) {
            $result = $this->getCachedSkin($uuid);

            if(!$result) return HtmlRenderer::DEFAULT_SKIN_URL;
            return $result['skin'];
        } else {
            $result = $this->io_handler->getSkinAsBase64($uuid);

            if(!$result) {
                return HtmlRenderer::DEFAULT_SKIN_URL;
            }

            $this->clearCacheSkin($uuid);
            $this->cacheSkin($uuid, $result);
            return $result;
        }
    }

    /**
     * @param $type
     * @return string
     */
    private function getTableNameFromType($type) {
        switch($type) {
            case 'username':
                return DatabaseHandler::USER_CACHE_TABLE;
            case 'lastkill':
                return DatabaseHandler::LASTKILL_CACHE_TABLE;
            case 'lastdeath':
                return DatabaseHandler::LASTDEATH_CACHE_TABLE;
            case 'usersonline':
                return DatabaseHandler::USERSONLINE_CACHE_TABLE;
            default:
                throw new LogicException("Invalid type.");
        }
    }
}