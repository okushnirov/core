<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Auth\Interfaces\AuthStrategy;
use okushnirov\core\Library\{Config, DbSQLAnywhere};

final class AuthStrategyDb implements AuthStrategy
{
  private int | bool $connection;
  private bool $isDebug;
  private UserSession $session;
  
  public function __construct(int | bool $connection, UserSession $userSession, bool $isDebug)
  {
    $this->connection = $connection;
    $this->isDebug = $isDebug;
    $this->session = $userSession;
  }
  
  public function authenticate():bool
  {
    Config::load(['dbase.php']);
    
    $settings = Config::getAsObject();
    
    if (empty($settings) || !isset($settings->dbase)) {
      
      throw new \Exception('Empty database auth settings', -100);
    }
    
    $connectNumber = false === $this->connection ? (int)($settings->dbase->{'login'.(TEST_SERVER ? 'Test' : '')} ?? 0)
      : $this->connection;
    
    if ($this->isDebug) {
      trigger_error(__METHOD__." connection\ncall: $this->connection\nstart: $connectNumber");
    }
    
    if (false !== $connectNumber && !isset($settings->dbase->{"$connectNumber"})) {
      
      throw new \Exception('Wrong DB connection settings', -105);
    }
    
    DbSQLAnywhere::disconnect();
    
    $login = $this->session->getUserLogin();
    $password = $this->session->getUserPassword();
    
    if (!DbSQLAnywhere::connect($connectNumber, $login, $password)) {
      
      throw new \Exception("Wrong DB connect", -115);
    }
    
    DbSQLAnywhere::disconnect();
    
    return true;
  }
}