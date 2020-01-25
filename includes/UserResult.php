<?php

/**
 * Class UserResult
 */
class UserResult implements Result
{
    /**
     * @var bool
     */
    private $admin;

    /**
     * @var int
     */
    private $leaves;

    /**
     * @var int
     */
    private $joins;

    /**
     * @var int
     */
    private $deaths;

    /**
     * @var int
     */
    private $kills;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $username;

    /**
     * @var int
     */
    private $id;

    /**
     * UserResult constructor.
     *
     * @param int $id
     * @param string $username
     * @param string $uuid
     * @param int $kills
     * @param int $deaths
     * @param int $joins
     * @param int $leaves
     * @param bool $admin
     */
    public function __construct($id, $username, $uuid, $kills, $deaths, $joins, $leaves, $admin)
    {
        $this->id = $id;
        $this->username = $username;
        $this->uuid = $uuid;
        $this->kills = $kills;
        $this->deaths = $deaths;
        $this->joins = $joins;
        $this->leaves = $leaves;
        $this->admin = $admin;
    }

    /**
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getUUID() {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function getKills() {
        return $this->kills;
    }

    /**
     * @return int
     */
    public function getDeaths() {
        return $this->deaths;
    }

    /**
     * @return int
     */
    public function getJoins() {
        return $this->joins;
    }

    /**
     * @return int
     */
    public function getLeaves() {
        return $this->leaves;
    }

    /**
     * @return bool
     */
    public function getAdminStatus() {
        return $this->admin;
    }
}