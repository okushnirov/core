<?php

namespace okushnirov\core\Library;

/**
 * Не змінювати кодування ключів (назв стовпців)
 */
define('SQL_KEY_ORIGIN', 1);

/**
 * Не змінювати регістр ключів (назв стовпців)
 */
define('SQL_KEY_CASE_ORIGIN', 2);

/**
 * Не змінювати кодування значення
 */
define('SQL_VALUE_ORIGIN', 4);

use okushnirov\core\Library\{Enums\SQLAnywhere, Interfaces\DbSQL};

final class DbSQLAnywhere implements DbSQL
{
  /**
   * @var resource|bool
   */
  public static $connect = false;
  
  public static bool $debug = false;
  
  public static string $queryError = '';
  
  public static int $queryErrorCode = 0;
  
  public static string $queryErrorMessage = '';
  
  public static string $queryErrorState = '';
  
  public static int $queryNumFields = 0;
  
  public static int $queryNumRows = 0;
  
  private static mixed $_settings;
  
  public static function connect(
    bool | int $connection = false, bool | string $user = false, bool | string $pass = false):bool
  {
    self::$_settings = self::getSettings();
    
    $c = new \stdClass();
    $c->connection = false === $connection ? (int)self::$_settings->dbase->{'dbase'.(TEST_SERVER ? 'Test' : '')}
      : (int)$connection;
    $s = &self::$_settings->dbase->{$c->connection};
    
    $c->user = false === $user ? $s->user : $user;
    $c->pass = false === $pass ? $s->pass : $pass;
    
    # Connect
    self::$connect = sasql_connect("HOST=$s->host;SERVER=$s->server;DBN=$s->base;UID=$c->user;PWD=$c->pass;CharSet=$s->charset;CPOOL=NO;RetryConnTO=5;CON=PHP;");
    
    if (self::$debug) {
      trigger_error(__METHOD__." for[$connection][$user:$pass]".(self::$connect ? ' ok ['.self::$connect.']'
          : ' failed')."\n".json_encode(debug_backtrace()[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ."\nsettings[$c->connection][$c->user:$c->pass]");
    }
    
    unset($c, $s);
    
    return (bool)self::$connect;
  }
  
  public static function disconnect():void
  {
    self::clearProperties();
    
    if (!self::$connect) {
      if (self::$debug) {
        trigger_error(__METHOD__." connect not exist\n".json_encode(debug_backtrace()[0],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
      }
      
      return;
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__.' for['.self::$connect."] disconnect\n".json_encode(debug_backtrace()[0],
          JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    
    self::$connect = self::$connect && !sasql_disconnect(self::$connect);
  }
  
  public static function escape(
    mixed         $queryString, bool $wrapQuotes = false, bool | int $connection = false, bool | string $user = false,
    bool | string $pass = false):bool | string
  {
    if (!self::$connect && !self::connect($connection, $user, $pass)) {
      if (self::$debug) {
        trigger_error(__METHOD__." connect [$connection][$user:$pass] not exist\n".json_encode(debug_backtrace()[0],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
      }
      
      return false;
    }
    
    $queryStringEscaped = sasql_real_escape_string(self::$connect, (string)$queryString);
    
    if (self::$debug) {
      trigger_error(__METHOD__." for[".self::$connect."] ".(false === $queryStringEscaped ? 'error' : 'ok')."\n"
        .json_encode(debug_backtrace()[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ."\n[$queryString]->[$queryStringEscaped]");
    }
    
    return false === $queryStringEscaped ? '' : ($wrapQuotes ? "'$queryStringEscaped'" : $queryStringEscaped);
  }
  
  public static function getSettings():mixed
  {
    
    return File::parse(['/json/dbase.json']);
  }
  
  public static function query(
    string        $queryString, SQLAnywhere $type, bool | int $connection = false, bool | string $user = false,
    bool | string $pass = false, string $keyString = '', int $flags = 0):mixed
  {
    self::clearProperties();
    
    $trace = json_encode(debug_backtrace()[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    if (empty($queryString)) {
      if (self::$debug) {
        trigger_error(__METHOD__."\n$trace SQL is empty");
      }
      
      return false;
    }
    
    if (!self::$connect && !self::connect($connection, $user, $pass)) {
      if (self::$debug) {
        trigger_error(__METHOD__." \n$trace error connection for \n".$queryString);
      }
      
      return false;
    }
    
    $SQL = Encoding::encode($queryString);
    $keyString = $flags & SQL_KEY_CASE_ORIGIN ? $keyString : mb_convert_case($keyString, MB_CASE_LOWER);
    
    if (self::$debug) {
      trigger_error(__METHOD__."\n$trace\nconnect[".self::$connect."][$user:$pass] SQL:\n".Encoding::decode($SQL));
    }
    
    $realResult = sasql_real_query(self::$connect, $SQL);
    
    self::$queryErrorState = trim(sasql_sqlstate(self::$connect) ?? '');
    self::$queryErrorCode = (int)(sasql_errorcode(self::$connect) ?? 0);
    self::$queryError = Encoding::decode(trim(sasql_error(self::$connect) ?? ''));
    self::$queryErrorMessage = '00000' === self::$queryErrorState && !self::$queryErrorCode
    && empty(self::$queryError) ? '' : '['.self::$queryErrorState.']['.self::$queryErrorCode.'] '.self::$queryError;
    
    if (SQLAnywhere::NO_RESULT == $type) {
      if (self::$debug) {
        trigger_error(__METHOD__." result queryErrorState[".self::$queryErrorState.'] queryErrorCode['
          .self::$queryErrorCode.'] queryError['.self::$queryError.'] SQL_NONE');
      }
      
      return true;
    }
    
    if (!$realResult) {
      trigger_error(__METHOD__.' error['.self::$queryErrorMessage."] connect[$connection][$user:$pass]\n"
        .json_encode(debug_backtrace(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
      
      return false;
    }
    
    if (SQLAnywhere::CALL == $type) {
      if (self::$debug) {
        trigger_error(__METHOD__." result queryErrorState[".self::$queryErrorState.'] queryErrorCode['
          .self::$queryErrorCode.'] queryError['.self::$queryError.'] SQL_СALL');
      }
      
      return true;
    }
    
    $queryResult = sasql_use_result(self::$connect);
    
    self::$queryNumRows = (int)(sasql_num_rows($queryResult) ?? 0);
    self::$queryNumFields = (int)(sasql_num_fields($queryResult) ?? 0);
    
    if (self::$debug) {
      trigger_error(__METHOD__." result queryErrorState[".self::$queryErrorState.'] queryErrorCode['
        .self::$queryErrorCode.'] queryError['.self::$queryError.'] numRows['.self::$queryNumRows.'] numFields['
        .self::$queryNumFields.']');
    }
    
    $result = [];
    
    switch ($type) {
      case SQLAnywhere::COLUMN :
        $array = sasql_fetch_array($queryResult, SASQL_NUM);
        $result = empty($array) ? false : ($flags & SQL_VALUE_ORIGIN ? $array[0] : Encoding::decode($array[0]));
        
        break;
      
      case SQLAnywhere::FETCH:
      case SQLAnywhere::OBJECT:
        $array = sasql_fetch_array($queryResult, SASQL_ASSOC);
        $array = self::row((array)$array, $type, $flags);
        
        if ('' !== $keyString && isset($array[$keyString])) {
          $result[$array[$keyString]] = $array;
        } else {
          $result = $array;
        }
        
        break;
      
      case SQLAnywhere::FETCH_ALL:
      case SQLAnywhere::OBJECT_ALL:
        while ($array = sasql_fetch_array($queryResult, SASQL_ASSOC)) {
          $array = self::row($array, $type, $flags);
          
          if ('' !== $keyString && isset($array[$keyString])) {
            $result[$array[$keyString]] = $array;
          } else {
            $result[] = $array;
          }
        }
        
        break;
      
      default:
    }
    
    $result = SQLAnywhere::OBJECT == $type || SQLAnywhere::OBJECT_ALL == $type ? (object)$result : $result;
    
    sasql_free_result($queryResult);
    
    unset($array, $realResult);
    
    return $result;
  }
  
  private static function row(array $array, SQLAnywhere $type, int $flags):object | array
  {
    $row = [];
    
    foreach ($array as $key => $value) {
      $key = $flags & SQL_KEY_ORIGIN ? $key : Encoding::decode((string)$key);
      $key = $flags & SQL_KEY_CASE_ORIGIN ? $key : mb_convert_case($key, MB_CASE_LOWER);
      $value = $flags & SQL_VALUE_ORIGIN ? $value : Encoding::decode((string)$value);
      
      $row[$key] = $value;
    }
    
    return SQLAnywhere::OBJECT == $type || SQLAnywhere::OBJECT_ALL == $type ? (object)$row : $row;
  }
  
  private static function clearProperties():void
  {
    self::$queryErrorMessage = '';
    self::$queryErrorState = '';
    self::$queryErrorCode = 0;
    self::$queryError = '';
    self::$queryNumRows = 0;
    self::$queryNumFields = 0;
  }
}