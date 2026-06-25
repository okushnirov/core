<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Auth\Interfaces\AuthStrategy;

final class AuthStrategyLdapDb implements AuthStrategy
{
  private AuthStrategyLdap $ldap;
  private AuthStrategyDb $db;
  
  public function __construct(AuthStrategyLdap $ldap, AuthStrategyDb $db)
  {
    $this->ldap = $ldap;
    $this->db = $db;
  }
  
  public function authenticate():bool
  {
    
    return $this->ldap->authenticate() || $this->db->authenticate();
  }
}