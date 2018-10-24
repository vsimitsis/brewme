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

    public static function upsertUserPreferences(array $userPreferences) {
        $q = "INSERT INTO " . self::TABLE_USER_PREFERENCES . " (user_id, type, comments)
              VALUES (:user_id, :type, :comments)
              ON DUPLICATE KEY UPDATE
              comments     = :comments";
        return self::query($q, $userPreferences);
    }

    public static function getUserPreferencesByUserIdAndType(int $userId, string $type) {
        $q = "SELECT * FROM " . self::TABLE_USER_PREFERENCES . " WHERE user_id = ? AND type = ?";
        $result = self::query_and_fetch($q, [$userId, $type]);
        if ($result) {
            return array_shift($result);
        } else {
            return null;
        }
    }
   
}