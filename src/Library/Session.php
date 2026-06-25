<?php

namespace okushnirov\core\Library;

use okushnirov\core\Handlers\{SessionHandler, SessionHandlerWs};
use okushnirov\core\Library\Enums\{Decrypt, SessionType};

final class Session
{
  public static function decryptCRC(array $session, bool $decryptAll = false):array
  {
    $s = $session;
    
    foreach ($s['CRC'] ?? [] as $key => $value) {
      $s['CRC'][$key] = match ($key) {
        'avatar' => $decryptAll ? $value : '',
        'hash' => $decryptAll ? Crypt::action(Crypt::action($value, Decrypt::CHR), Decrypt::CHR) : '',
        default => Crypt::action($value, Decrypt::CHR)
      };
    }
    
    return $s;
  }
  
  public static function sessionDestroy():void
  {
    if (session_id()) {
      $_SESSION = [];
      
      if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"],
          $params["httponly"]);
      }
      
      session_destroy();
      session_start();
      session_regenerate_id(true);
    }
  }
  
  public static function sessionStart(SessionType $session):bool
  {
    if (SessionType::NONE === $session) {
      
      return false;
    }
    
    if (!session_id()) {
      try {
        if (SessionType::DB === $session) {
          new SessionHandler();
        } elseif (SessionType::WS === $session) {
          new SessionHandlerWs();
        }
        
        if (!@session_start()) {
          throw new \Exception('SessionType::'.$session->name.' connection failure or session storage is unavailable');
        }
      } catch (\Exception $e) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        
        trigger_error(__METHOD__.' '.$e->getMessage()."\n".json_encode($backtrace,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), E_USER_ERROR);
      }
    }
    
    return !empty(session_id());
  }
}