<?php

namespace BrewMe\DBI;

use PDO;
use BrewMe\CFG;

abstract class AbstractDBI {
   
  const 
    TABLE_USERS = 'users',
    TABLE_USER_PREFERENCES = 'user_preferences',
    TABLE_ORDERS = 'orders';

  /** @var PDO object containing a connection do the db */
  private static
    $dbh,
    $last_inserted_id,
    $affected_rows;

  public static function connect_to_db() {
    self::set_dbh(
        new PDO(
        "mysql:host=".CFG::get('MYSQL_HOST')
        .";dbname="
        .CFG::get('MYSQL_DB_NAME'),
        CFG::get('MYSQL_USER'),
        CFG::get('MYSQL_PASS'),
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_PERSISTENT => false,
          PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8MB4'"
        ]
      )
    );
    return true;
  
  }

  private static function check_connection() {
    if(!self::$dbh) {
      self::connect_to_db();
    }
  }

  /**
   * Sets an instance of the PDO to the dbh
   *
   * @param PDO $dbh an instance of the PDO object
   * @return boolean true once $dbh is set
   */
  public function set_dbh(PDO $dbh) {
    self::$dbh = $dbh;
    return true;
  }

  public static function get_dbh() {
    return self::$dbh;
  }

  public static function query($sql, array $vars = null) {
    self::check_connection();
    if($sql) {
      $sth = self::$dbh->prepare($sql);
      // Check if any variables have been passed
      if($vars) {
        $sth->execute($vars);
      } else {
        $sth->execute();
      }
      self::$last_inserted_id = self::$dbh->lastInsertId();
      self::$affected_rows = $sth->rowCount();
      return $sth;
    } else {
      throw new \Exception("SQL is not provided");
    }
  }

  public function query_and_fetch($sql, array $vars = null) {
    if($sql) {
      $sth = self::query($sql, $vars);
      return $sth->fetchAll(PDO::FETCH_ASSOC);
    } else {
      throw new \Exception("SQL is not provided");
    }
  }

  public function disconnect_from_db() {
    self::$dbh = false;
    return true;
  }

  public function get_last_inserted_id() {
    return self::$last_inserted_id;
  }

  public function get_affected_rows() {
    return self::$affected_rows;
  }

  // DEFAULT ACTIONS AND HELPERS
  protected static function create_values_placeholders_from_arrays(array $arr_data) {
    $arr_str_parts = [];
    foreach ($arr_data as $arr_record) {
      $str_values = "(";
      $arr_values = [];
      foreach($arr_record as $key => $value) {
        $arr_values[] = "?";
      }
      $str_values .= join(', ', $arr_values);
      $str_values .= ")";

      $arr_str_parts[] = $str_values;
    }

    return join(', ', $arr_str_parts);
  }

  protected static function get_values_from_arrays(array $arr_data) {
    $arr_values = [];
    foreach($arr_data as $arr_record) {
      foreach($arr_record as $key => $value) {
        $arr_values[] = $value;
      }
    }
    return $arr_values;
  }

  protected static function array_to_query_args(array $arr_data) {
    $arr_query_args = [];
    foreach($arr_data as $str_field => $value) {
      $arr_query_args[$str_field] = $value;
    }
    return $arr_query_args;
  }

  protected static function array_to_query_update_sets(array $arr_field_names, array $arr_exclude_keys) {
    $arr_pairs = [];
    foreach ($arr_field_names as $str_field_name) {
      if (!array_key_exists($str_field_name, array_flip($arr_exclude_keys))) {
        $arr_pairs[] = "$str_field_name = :$str_field_name";
      }
    }
    return join(', ', $arr_pairs);
  }

  protected static function array_to_insert_args(array $arr_field_names) {
    return join(', ', $arr_field_names);
  }

  protected static function array_to_insert_placeholders(array $arr_field_names) {
    return ":" . join(', :', $arr_field_names);
  }

  protected static function _get_by_id($table, $id, $multiple = false) {
    return self::_get_by_key($table, 'id', $id, $multiple);
  }

  protected static function _get_by_key($table, $key, $value, $multiple = false) {
    $q = "SELECT * FROM {$table} WHERE {$key} = ?";
    $arr_data = self::query_and_fetch($q, [$value]);
    if ($arr_data) {
      if ($multiple) {
        return $arr_data;
      } else {
        return array_shift($arr_data);
      }
    } else {
      return null;
    }
  }

  protected static function _get_all($table) {
    $arr_data = self::query_and_fetch("SELECT * FROM {$table}");
    return !empty($arr_data) ? $arr_data : null;
  }

  protected static function default_insert(array $arr_required_fields, array $arr_data, $str_table_name) {
    $q = "INSERT INTO $str_table_name (".self::array_to_insert_args($arr_required_fields).") VALUES (".self::array_to_insert_placeholders($arr_required_fields).")";
    self::query($q, self::array_to_query_args($arr_data));
    return self::get_last_inserted_id();
  }
  
}