<?php


class UsersOnlineResult implements Result
{
    /**
     * @var int
     */
    private $max;

    /**
     * @var int
     */
    private $now;

    /**
     * UsersOnlineResult constructor.
     *
     * @param int $max
     * @param int $now
     */
    public function __construct($max, $now)
    {
        if(!is_int($max) || !is_int($now)) {
            throw new InvalidArgumentException("Max and Now must be of type int.");
        }

        $this->max = $max;
        $this->now = $now;
    }

    /**
     * @return int
     */
    public function getMax() {
        return $this->max;
    }

    /**
     * @return int
     */
    public function getNow() {
        return $this->now;
    }
}