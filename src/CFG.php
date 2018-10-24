<?php

namespace BrewMe;

use Dotenv\Dotenv;

class CFG {

    protected static $dotenv;

    /**
     * Set the env file
     *
     * @param Dotenv $dotenv
     * @return bool
     */
    public static function setDotenv(Dotenv $dotenv)
    {
        self::$dotenv = $dotenv;
        return true;
    }

    /**
     * Load the env file
     *
     * @return bool
     */
    public static function load()
    {
        if (self::$dotenv === null) {
            self::$dotenv = new Dotenv(__DIR__ . "/../");
            self::$dotenv->load();
        }
        return true;
    }

    /**
     * Load the env variables and get the key
     *
     * @param $key
     * @return array|false|string
     */
    public static function get($key)
    {
        self::load();
        return getenv($key);
    }
}