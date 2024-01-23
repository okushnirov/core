<?php

namespace okushnirov\core\Handlers;

use okushnirov\core\Library\{Encoding, File};
use ReturnTypeWillChange;

final class SessionHandler extends \Exception implements \SessionHandlerInterface
{
  private static mixed $_con;
  
  private static mixed $_set;
  
  public function __construct()
  {
    parent::__construct();
    
    self::$_set = File::parse(['/json/dbase.json']);
    
    if (empty(self::$_set->dbase)) {
      
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
    
    return "'".sasql_real_escape_string(self::$_con, $s)."'";
  }
  
  private static function _query(string $q):mixed
  {
    sasql_real_query(self::$_con, Encoding::encode("SELECT _сессия_$q)"));
    
    return empty(Encoding::decode(sasql_error(self::$_con))) ? sasql_fetch_array(sasql_use_result(self::$_con),
      SASQL_NUM) : false;
  }
  
  public function close():bool
  {
    if (is_resource(self::$_con)) {
      sasql_disconnect(self::$_con);
    }
    
    return !self::$_con = false;
  }
  
  public function destroy(string $id):bool
  {
    
    return (bool)self::_query('удалить('.self::_escape($id));
  }
  
  #[ReturnTypeWillChange] public function gc(int $max_lifetime):bool
  {
    
    return (bool)self::_query('уборка('.self::_escape($max_lifetime));
  }
  
  public function open(string $path, string $name):bool
  {
    $c = &self::$_set->dbase->{self::$_set->dbase->{'session'.(TEST_SERVER ? 'Test' : '')}};
    
    self::$_con = sasql_connect("HOST=$c->host;SERVER=$c->server;DBN=$c->base;UID=$c->user;PWD=$c->pass;CharSet=$c->charset;CPOOL=NO;RetryConnTO=5;CON=PHP_Session;ENCRYPTION=simple;");
    
    if (!is_resource(self::$_con)) {
      
      throw new \Exception('No session database connection', 0);
    }
    
    return true;
  }
  
  #[ReturnTypeWillChange] public function read(string $id):false | string
  {
    $r = self::_query('читать('.self::_escape($id));
    
    return false === $r ? false : (empty($r) ? '' : Encoding::decode($r[0]));
  }
  
  public function write(string $id, string $data):bool
  {
    
    return (bool)self::_query('записать('.self::_escape($id).','.self::_escape($data).','
      .self::_escape($_SERVER['REMOTE_ADDR']));
  }
}