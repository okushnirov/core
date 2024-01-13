<?php

namespace okushnirov\core\Library;

use okushnirov\core\{Handlers\SessionHandler, Library\Enums\Decrypt, Library\Enums\SessionType};

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
      if (SessionType::DB === $session) {
        try {
          new SessionHandler();
        } catch (\Exception $e) {
          trigger_error(__METHOD__.' '.$e->getMessage());
        }
      }
      
      session_start();
    }
    
    return !empty(session_id());
  }
}