<?php

class UserPage
{
    public $uuid = '';

    /**
     * UserPage constructor.
     *
     * @param $uuid
     */
    public function __construct($uuid) {
        if(!is_string($uuid)) {
            throw new InvalidArgumentException("UUID must be of type string.");
        }

        $this->uuid = $uuid;
    }
}