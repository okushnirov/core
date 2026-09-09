<?php

namespace okushnirov\core\Library\Enums;

enum Auth
{
  case DB;
  case DB_USER;
  case LDAP;
  case LDAP_DB;
  case WS;
  case WS_DATA;
  case MANUAL;
  
  public function isStandalone():bool
  {
    return match ($this) {
      self::WS, self::WS_DATA, self::MANUAL => true,
      default => false,
    };
  }
}