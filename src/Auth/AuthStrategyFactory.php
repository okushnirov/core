<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Auth\Interfaces\AuthStrategy;
use okushnirov\core\Library\Enums\Auth;

final class AuthStrategyFactory
{
  public static function create(
    Auth $type, UserSession $session, bool | int | null $connection = false, bool $isDebug = false):AuthStrategy
  {
    if (TEST_SERVER && (Auth::LDAP === $type || Auth::LDAP_DB === $type)) {
      $type = Auth::DB;
    }
    
    return match ($type) {
      Auth::DB => new AuthStrategyDb($connection, $session, $isDebug),
      Auth::DB_USER => new AuthStrategyDbUser(),
      Auth::LDAP => new AuthStrategyLdap($session, $isDebug),
      Auth::LDAP_DB => new AuthStrategyLdapDb(new AuthStrategyLdap($session, $isDebug),
        new AuthStrategyDb($connection, $session, $isDebug)),
      Auth::WS, Auth::WS_DATA => new AuthStrategyWs($session, Auth::WS_DATA === $type, $isDebug)
    };
  }
}