<?php

namespace okushnirov\core\Handlers;

use core\Handlers\LoginHandler;
use okushnirov\core\Library\{Authorization, Enums\Auth, Location};

final class RootLogin
{
  public static function handler(Auth $authType, int $flag = 0):void
  {
    try {
      if ((new Authorization())->check($authType)) {
        
        return;
      }
    } catch (\Exception $e) {
      if (Root::$debug) {
        trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
      }
    }
    
    if ($flag) {
      LoginHandler::run();
    } else {
      session_destroy();
      http_response_code(200);
    }
    
    Location::authRedirect(true);
  }
}