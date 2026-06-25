<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Auth\Interfaces\AuthStrategy;

class AuthStrategyDbUser implements AuthStrategy
{
  
  public function authenticate():bool
  {
    
    return true;
  }
}