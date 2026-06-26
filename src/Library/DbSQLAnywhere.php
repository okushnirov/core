<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\SQLAnywhere;
use okushnirov\core\Library\Interfaces\DbSQL;

/**
 * Не змінювати регістр ключів (назв стовпців)
 */
define('SQL_KEY_CASE_ORIGIN', 2);

/**
 * Не змінювати кодування ключів (назв стовпців)
 */
define('SQL_KEY_ORIGIN', 1);

/**
 * Не змінювати кодування значення
 */
define('SQL_VALUE_ORIGIN', 4);

final class DbSQLAnywhere implements DbSQL
{
  /**
   * @var resource|bool
   */
  public static $connect = false;
  
  public static bool $isDebug = false;
  
  public static string $queryError = '';
  
  public static int $queryErrorCode = 0;
  
  public static string $queryErrorMessage = '';
  
  public static string $queryErrorState = '';
  
  public static int $queryNumFields = 0;
  
  public static int $queryNumRows = 0;
  
  public static function connect(
    bool | int $connection = false, bool | string $user = false, bool | string $pass = false):bool
  {
    Config::load(['dbase.php']);
    
    if (empty(Config::get('dbase'))) {
      
      return false;
    }
    
    $c = new \stdClass();
    $c->connection = false === $connection ? (int)Config::get('dbase')['dbase'.(defined('TEST_SERVER')
    && TEST_SERVER ? 'Test' : '')] : (int)$connection;
    $s = &Config::get('dbase')[$c->connection];
    
    $c->user = false === $user ? $s['user'] : $user;
    $c->pass = false === $pass ? $s['pass'] : $pass;
    
    # Connect
    $connectString = "HOST={$s['host']};SERVER={$s['server']};DBN={$s['base']};UID=$c->user;PWD=$c->pass;CharSet={$s['charset']};CPOOL=NO;RetryConnTO=5;CON=PHP;";
    self::$connect = sasql_connect($connectString);
    
    if (self::$isDebug) {
      trigger_error(__METHOD__." for[$connection][$user:***] -> $connectString -> ".(self::$connect ? 'ok ['
          .self::$connect.']' : 'failed')."\n".json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0],
          JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\nsettings[$c->connection][$c->user:$c->pass]");
    }
    
    unset($c, $s);
    
    return (bool)self::$connect;
  }
  
  public static function disconnect():void
  {
    self::clearProperties();
    
    if (!self::$connect) {
      if (self::$isDebug) {
        trigger_error(__METHOD__." connect not exist\n".json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
      }
      
      return;
    }
    
    if (self::$isDebug) {
      trigger_error(__METHOD__.' for['.self::$connect."] disconnect\n"
        .json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    
    self::$connect = self::$connect && !sasql_disconnect(self::$connect);
  }
  
  public static function escape(
    mixed         $queryString, bool $wrapQuotes = false, bool | int $connection = false, bool | string $user = false,
    bool | string $pass = false):bool | string
  {
    if (!self::$connect && !self::connect($connection, $user, $pass)) {
      if (self::$isDebug) {
        trigger_error(__METHOD__." connect [$connection][$user:***] not exist\n"
          .json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
      }
      
      return false;
    }
    
    $queryStringEscaped = sasql_real_escape_string(self::$connect, (string)$queryString);
    
    if (self::$isDebug) {
      trigger_error(__METHOD__." for[".self::$connect."] ".(false === $queryStringEscaped ? 'error' : 'ok')."\n"
        .json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ."\n[$queryString]->[$queryStringEscaped]");
    }
    
    return false === $queryStringEscaped ? '' : ($wrapQuotes ? "'$queryStringEscaped'" : $queryStringEscaped);
  }
  
  public static function query(
    string        $queryString, SQLAnywhere $type, bool | int $connection = false, bool | string $user = false,
    bool | string $pass = false, string $keyString = '', int $flags = 0):mixed
  {
    self::clearProperties();
    
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
    $trace = json_encode($backtrace, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    if (empty($queryString)) {
      if (self::$isDebug) {
        trigger_error(__METHOD__."\n$trace SQL is empty");
      }
      
      return false;
    }
    
    if (!self::$connect && !self::connect($connection, $user, $pass)) {
      if (self::$isDebug) {
        trigger_error(__METHOD__." \n$trace error connection for \n".$queryString);
      }
      
      return false;
    }
    
    if (self::$isDebug) {
      trigger_error(__METHOD__."\n$trace\nconnect[".self::$connect."][$user:***] SQL:\n$queryString");
    }
    
    $realResult = sasql_real_query(self::$connect, Encoding::encode($queryString));
    
    self::$queryErrorState = trim(sasql_sqlstate(self::$connect) ?? '');
    self::$queryErrorCode = (int)(sasql_errorcode(self::$connect) ?? 0);
    self::$queryError = Encoding::decode(trim(sasql_error(self::$connect) ?? ''));
    self::$queryErrorMessage = '00000' === self::$queryErrorState && !self::$queryErrorCode
    && empty(self::$queryError) ? '' : '['.self::$queryErrorState.']['.self::$queryErrorCode.'] '.self::$queryError;
    
    if (SQLAnywhere::NO_RESULT == $type) {
      if (self::$isDebug) {
        trigger_error(__METHOD__." result queryErrorState[".self::$queryErrorState.'] queryErrorCode['
          .self::$queryErrorCode.'] queryError['.self::$queryError.'] SQL_NONE');
      }
      
      return true;
    }
    
    if (!$realResult) {
      trigger_error(__METHOD__.' error['.self::$queryErrorMessage."] connect[$connection][$user:***]\n"
        .json_encode(debug_backtrace(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
      
      return false;
    }
    
    if (SQLAnywhere::CALL == $type) {
      if (self::$isDebug) {
        trigger_error(__METHOD__." result queryErrorState[".self::$queryErrorState.'] queryErrorCode['
          .self::$queryErrorCode.'] queryError['.self::$queryError.'] SQL_CALL');
      }
      
      return true;
    }
    
    $queryResult = sasql_use_result(self::$connect);
    
    if (false === $queryResult) {
      trigger_error(__METHOD__." Query [$queryString] return empty result");
      
      return SQLAnywhere::OBJECT == $type || SQLAnywhere::OBJECT_ALL == $type ? (object)[] : [];
    }
    
    self::$queryNumRows = (int)(sasql_num_rows($queryResult) ?? 0);
    self::$queryNumFields = (int)(sasql_num_fields($queryResult) ?? 0);
    
    if (self::$isDebug) {
      trigger_error(__METHOD__." result queryErrorState[".self::$queryErrorState.'] queryErrorCode['
        .self::$queryErrorCode.'] queryError['.self::$queryError.'] numRows['.self::$queryNumRows.'] numFields['
        .self::$queryNumFields.']');
    }
    
    $keyString = $flags & SQL_KEY_CASE_ORIGIN ? $keyString : mb_convert_case($keyString, MB_CASE_LOWER);
    $result = [];
    
    switch ($type) {
      case SQLAnywhere::COLUMN :
        $array = sasql_fetch_array($queryResult, SASQL_NUM);
        $result = empty($array) ? false : ($flags & SQL_VALUE_ORIGIN ? $array[0] : Encoding::decode($array[0]));
        
        break;
      
      case SQLAnywhere::FETCH:
        $array = sasql_fetch_array($queryResult, SASQL_ASSOC);
        
        if (false === $array || null === $array) {
          $result = false;
          
          break;
        }
        
        $array = self::row($array, $type, $flags);
        
        if ('' !== $keyString && isset($array[$keyString])) {
          $result[$array[$keyString]] = $array;
        } else {
          $result = $array;
        }
        
        break;
      
      case SQLAnywhere::OBJECT:
        $array = sasql_fetch_array($queryResult, SASQL_ASSOC);
        
        if (false === $array || null === $array) {
          $result = false;
          
          break;
        }
        
        $array = self::row($array, $type, $flags);
        
        if ('' !== $keyString && isset($array->{$keyString})) {
          $result[$array->{$keyString}] = $array;
        } else {
          $result = $array;
        }
        
        break;
      
      case SQLAnywhere::FETCH_ALL:
        while ($array = sasql_fetch_array($queryResult, SASQL_ASSOC)) {
          $array = self::row($array, $type, $flags);
          
          if ('' !== $keyString && isset($array[$keyString])) {
            $result[$array[$keyString]] = $array;
          } else {
            $result[] = $array;
          }
        }
        
        break;
      
      case SQLAnywhere::OBJECT_ALL:
        while ($array = sasql_fetch_array($queryResult, SASQL_ASSOC)) {
          $array = self::row($array, $type, $flags);
          
          if ('' !== $keyString && isset($array->{$keyString})) {
            $result[$array->{$keyString}] = $array;
          } else {
            $result[] = $array;
          }
        }
        
        break;
      
      default:
    }
    
    if (is_array($result) && ($type === SQLAnywhere::OBJECT || $type === SQLAnywhere::OBJECT_ALL)) {
      if ($type === SQLAnywhere::OBJECT_ALL || '' !== $keyString) {
        $result = (object)$result;
      }
    }
    
    sasql_free_result($queryResult);
    
    unset($array, $backtrace, $realResult, $trace);
    
    return $result;
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
}