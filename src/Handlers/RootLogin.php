<?php

namespace okushnirov\core\Handlers;

use core\Handlers\LoginHandler;
use okushnirov\core\Auth\Authorization;
use okushnirov\core\Library\{Enums\Auth, Location};

final class RootLogin
{
  public static function handler(
    Authorization $auth, Auth $authType, int $doLoginHandler = 0, bool $isDebug = false):void
  {
    try {
      if ($auth->check($authType)) {
        
        return;
      }
    } catch (\Throwable $e) {
      if ($isDebug) {
        trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
      }
    }
    
    if ($doLoginHandler) {
      LoginHandler::run();
    } else {
      if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
      }
    }
    
    Location::authRedirect(true);
  }
}