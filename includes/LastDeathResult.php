<?php

class LastDeathResult implements Result
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
     * LastDeathResult constructor.
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

    public function getID() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getDate() {
        return $this->date;
    }

    public function getTime() {
        return $this->time;
    }

    public function getMessage() {
        return $this->message;
    }
}