<?php

namespace BrewMe\Model;

class User
{
    public $user_id;
    public $username;
    public $created_at;
    public $updated_at;

    /**
     * User constructor.
     *
     * @param array $attrs
     */
    public function __construct($attrs = [])
    {
        foreach ($attrs as $key => $v) {
            $this->$key = $v;
        }
    }
}