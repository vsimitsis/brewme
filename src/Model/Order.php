<?php

namespace BrewMe\Model;

class Order
{
    public $user_id;
    public $type;
    public $status;
    public $comments;
    public $created_at;
    public $updated_at;

    /**
     * The possible order statuses
     */
    const
        STATUS_PENDING = 1,
        STATUS_DONE = 2,
        STATUS_CANCELLED = 3;

    /**
     * Order constructor.
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