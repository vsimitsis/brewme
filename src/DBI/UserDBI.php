<?php

namespace BrewMe\DBI;

use BrewMe\AbstractDBI;

class UserDBI extends AbstractDBI {

    public static function findUser(int $userId) {}

    public static function findUserByUsername(string $username) {}

    public static function createUser(array $user) {}

    public static function deleteUser(int $userId) {}

    public static function findUserPreferences(int $userId) {}

    public static function createUserPreferences(array $userPreferences) {}
    
    public static function updateUserPreferences(array $userPreferences) {}

    public static function upsertUserPreferences(array $userPreferences) {}

    public static function deleteUserPreferences(int $userId) {}
   
}