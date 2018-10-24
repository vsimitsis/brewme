<?php

namespace BrewMe\DBI;

use BrewMe\DBI\AbstractDBI;

class UserDBI extends AbstractDBI {

    public static function findUser(int $userId) {
        return self::_get_by_id(self::TABLE_USERS, $userId);
    }

    public static function findUserByUsername(string $username) {
        return self::_get_by_key(self::TABLE_USERS, 'username', $username);
    }

    public static function createUser(array $user) {
        return self::default_insert(['username'], $user, self::TABLE_USERS);
    }

    public static function deleteUser(int $userId) {}

    public static function findUserPreferences(int $userId) {}

    public static function createUserPreferences(array $userPreferences) {}
    
    public static function updateUserPreferences(array $userPreferences) {}

    public static function upsertUserPreferences(array $userPreferences) {}

    public static function deleteUserPreferences(int $userId) {}
   
}