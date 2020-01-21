<?php

/**
 * Class LastKilLResult
 */
class LastKilLResult
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $date;

    /**
     * @var string
     */
    private $time;

    /**
     * @var string
     */
    private $message;

    /**
     * LastKilLResult constructor.
     *
     * @param int $id
     * @param string $username
     * @param string $date
     * @param string $time
     * @param string $message
     */
    public function __construct($id, $username, $date, $time, $message)
    {
        $this->id = $id;
        $this->username = $username;
        $this->date = $date;
        $this->time = $time;
        $this->message = $message;
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
    public function getDate() {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getTime() {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }
}