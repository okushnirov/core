<?php

namespace okushnirov\core\Handlers;

use core\Render\Auth\Authentication;
use okushnirov\core\Library\{Authorization, Enums\Auth};

final class RootLogin
{
  public static function handler(Auth $authType):void
  {
    try {
      if ((new Authorization())->check($authType)) {
        
        return;
      }
    } catch (\Exception $e) {
      if (Root::$debug) {
        trigger_error(__METHOD__." Error [{$e->getCode()}] {$e->getMessage()}");
      }
    }
    
    session_destroy();
    http_response_code(200);
    
    exit((new Authentication())::render());
  }
}