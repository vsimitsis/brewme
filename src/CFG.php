<?php

namespace BrewMe;

use Dotenv\Dotenv;

class CFG {
    
    protected static $dotenv;

    public static function setDotenv(Dotenv $dotenv)
    {
        self::$dotenv = $dotenv;
        return self:
    }


    public function load()
    {
        if (self::$dotenv === null) {
            self::$dotenv = new Dotenv(__DIR__ . "/../");
            self::$dotenv->load();
        }
        return true;
    }
}