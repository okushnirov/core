<?php

namespace okushnirov\core\Handlers;

use okushnirov\core\Library\{Config, Encoding, Enums\Charset};
use ReturnTypeWillChange;

final class SessionHandler extends \Exception implements \SessionHandlerInterface
{
  private static mixed $connect;
  private static Charset $charsetDB = Charset::WINDOWS1251;
  
  public function __construct(Charset $charsetDBase = Charset::WINDOWS1251)
  {
    parent::__construct();
    
    Config::load(['dbase.php']);
    
    self::$charsetDB = $charsetDBase;
    
    if (empty(Config::get('dbase'))) {
      
      exit;
    }
    
    session_set_save_handler([
      $this,
      'open'
    ], [
      $this,
      'close'
    ], [
      $this,
      'read'
    ], [
      $this,
      'write'
    ], [
      $this,
      'destroy'
    ], [
      $this,
      'gc'
    ]);
  }
  
  private static function _escape(string $s):string
  {
    
    return "'".sasql_real_escape_string(self::$connect, $s)."'";
  }
  
  private static function _query(string $q):mixed
  {
    sasql_real_query(self::$connect, Encoding::encode("SELECT _сессия_$q)", to: self::$charsetDB));
    
    return empty(Encoding::decode(sasql_error(self::$connect), self::$charsetDB))
      ? sasql_fetch_array(sasql_use_result(self::$connect), SASQL_NUM) : false;
  }
  
  public function close():bool
  {
    if (is_resource(self::$connect)) {
      sasql_disconnect(self::$connect);
    }
    
    return !self::$connect = false;
  }
  
  public function destroy(string $id):bool
  {
    
    return (bool)self::_query('удалить('.self::_escape($id));
  }
  
  #[ReturnTypeWillChange]
  public function gc(int $max_lifetime):bool
  {
    
    return (bool)self::_query('уборка('.self::_escape($max_lifetime));
  }
  
  public function open(string $path, string $name):bool
  {
    $dbase = Config::get('dbase');
    $c = &$dbase[$dbase['session'.(TEST_SERVER ? 'Test' : '')]];
    
    self::$connect = sasql_connect("HOST=$c->host;SERVER=$c->server;DBN=$c->base;UID=$c->user;PWD=$c->pass;CharSet=$c->charset;CPOOL=NO;RetryConnTO=5;CON=PHP_Session;ENCRYPTION=simple;");
    
    if (!is_resource(self::$connect)) {
      
      throw new \Exception('No session database connection', 0);
    }
    
    return true;
  }
  
  #[ReturnTypeWillChange]
  public function read(string $id):false | string
  {
    $r = self::_query('читать('.self::_escape($id));
    
    return false === $r ? false : (empty($r) ? '' : Encoding::decode($r[0], self::$charsetDB));
  }
  
  public function write(string $id, string $data):bool
  {
    
    return (bool)self::_query('записать('.self::_escape($id).','.self::_escape($data).','
      .self::_escape($_SERVER['REMOTE_ADDR']));
  }
}