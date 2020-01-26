<?php

/**
 * Class SkinRenderHandler
 *
 * Handles the rendering and caching of rendered skins.
 */
class SkinRenderHandler
{
    const SKIN_INSERT_QUERY = "INSERT INTO " . DatabaseHandler::RENDEREDSKIN_CACHE_TABLE . " (`skin`, `username`, `cache_time`) VALUES (?, ?, ?)";

    /**
     * @var DatabaseHandler
     */
    private $database;

    /**
     * SkinRenderHandler constructor.
     * @param DatabaseHandler $database
     */
    public function __construct(DatabaseHandler $database) {
        $this->database = $database;
    }

    /**
     * Gets the cached version of the skin if available, else it renders the skin.
     *
     * @param $username
     * @return string Base64-encoded version of the rendered skin.
     * @throws Exception
     */
    public function getSkin($username) {
        if($this->isCached($username)) {
            return $this->getCachedSkin($username);
        }

        if(!$this->clearSkinCache($username)) {
            return false;
        }

        if(!$base64 = $this->renderSkin($username)) {
            return false;
        }

        if(!$this->cacheSkin($username, $base64)) {
            return false;
        }

        return $base64;
    }

    /**
     * Renders the skin and returns the base64 encoded version of it.
     *
     * @param $username
     * @return string Base64-encoded version of the rendered skin.
     */
    public function renderSkin($username) {
        $renderer = new Renderer(
            $username,
            '0',
            '15',
            '10',
            '0',
            '0',
            '0',
            '0',
            'true',
            'false',
            'base64',
            '12',
            'true',
            'true'
        );

        return $renderer->get3DRender();
    }

    /**
     * Returns true if a skin is cached, false when it is not (or on failure).
     *
     * @param $username
     * @return bool
     */
    public function isCached($username) {
        if(!is_string($username)) {
            throw new InvalidArgumentException("Username must be of type string.");
        }

        try {
            $statement = $this->database->getConnection()->prepare("SELECT `cache_time` FROM " . DatabaseHandler::RENDEREDSKIN_CACHE_TABLE . " WHERE `username` = ?");
            $statement->execute([$username]);
        } catch(Exception $e) {
            return false;
        }

        return ($statement->rowCount() > 0 && $statement->fetch()['cache_time'] + CacheHandler::CACHE_INVALIDATION_TIME_LIMIT > time());
    }

    /**
     * Get the base64 cached skin.
     *
     * @param $username
     * @return string Returns the base64 of the cached skin.
     * @throws Exception
     */
    public function getCachedSkin($username) {
        if(!is_string($username)) {
            throw new InvalidArgumentException("Username must be of type string.");
        }

        $statement = $this->database->getConnection()->prepare("SELECT `skin` FROM " . DatabaseHandler::RENDEREDSKIN_CACHE_TABLE . " WHERE `username` = ?");
        $statement->execute([$username]);

        if($statement->rowCount() < 1) {
            throw new LogicException("Tried to get non-existent cached render.");
        }

        return $statement->fetch()['skin'];
    }

    /**
     * Caches a skin. Returns true on success and false on failure.
     *
     * @param $username
     * @param $base64
     * @return bool
     */
    public function cacheSkin($username, $base64) {
        if(!is_string($username)) {
            throw new InvalidArgumentException("Username must be of type string.");
        }

        if(!is_string($base64) || !base64_decode($base64)) {
            throw new InvalidArgumentException("Skin must be of type string (base64).");
        }

        try {
            $statement = $this->database->getConnection()->prepare(self::SKIN_INSERT_QUERY);
            $statement->execute([$base64, $username, time()]);
        } catch(Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Removes the cached result from the Rendered Skin cache if available. Returns true on success and false on failure.
     *
     * @param $username
     * @return bool
     */
    public function clearSkinCache($username) {
        if(!is_string($username)) {
            throw new InvalidArgumentException("Username must be of type string.");
        }

        try {
            $this->database
                ->getConnection()
                ->prepare("DELETE FROM " . DatabaseHandler::RENDEREDSKIN_CACHE_TABLE . " WHERE `username` = ?")
                ->execute([$username]);
        } catch(Exception $e) {
            return false;
        }

        return true;
    }
}