<?php

namespace okushnirov\core\Library;

use okushnirov\core\{Handlers\SessionHandler, Handlers\SessionHandlerWs, Library\Enums\Decrypt,
  Library\Enums\SessionType
};

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
      session_destroy();
      session_start();
      session_regenerate_id();
    }
  }
  
  public static function sessionStart(SessionType $session):bool
  {
    if (!session_id()) {
      try {
        if (SessionType::DB === $session) {
          new SessionHandler();
        } elseif (SessionType::WS === $session) {
          new SessionHandlerWs();
        }
        
        session_start();
      } catch (\Exception $e) {
        trigger_error(__METHOD__.' '.$e->getMessage());
      }
    }
    
    return !empty(session_id());
  }
}