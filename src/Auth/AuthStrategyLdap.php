<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Auth\Interfaces\AuthStrategy;
use okushnirov\core\Library\Config;

final class AuthStrategyLdap implements AuthStrategy
{
  private bool $isDebug;
  private UserSession $session;
  
  public function __construct(UserSession $userSession, bool $isDebug)
  {
    $this->isDebug = $isDebug;
    $this->session = $userSession;
  }
  
  public function authenticate():bool
  {
    Config::load(['login.php']);
    
    $settings = Config::getAsObject();
    
    if (empty($settings->login) || !isset($settings->login->ldap)) {
      
      throw new \Exception('Empty ldap auth settings', -200);
    }
    
    $ldap = $settings->login->ldap->{0};
    
    if ($this->isDebug) {
      trigger_error(__METHOD__." Settings\n".json_encode($ldap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    if (empty($ldap->domain)
      || empty($ldap->host)
      || empty($ldap->port)
      || empty($ldap->member)
      || empty($ldap->base)) {
      
      throw new \Exception('Wrong LDAP parameters', -205);
    }
    
    $cleanLogin = str_ireplace($ldap->domain, '', $this->session->getUserLogin());
    $loginWithDomain = $cleanLogin.$ldap->domain;
    
    ldap_set_option(null, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option(null, LDAP_OPT_NETWORK_TIMEOUT, 3);
    ldap_set_option(null, LDAP_OPT_TIMELIMIT, 3);
    
    $connect = ldap_connect($ldap->host, $ldap->port);
    
    if (!$connect) {
      if ($this->isDebug) {
        trigger_error(__METHOD__." Connect to $ldap->host:$ldap->port is failed");
      }
      
      return false;
    }
    
    $bind = ldap_bind($connect, $loginWithDomain, $this->session->getUserPassword());
    
    if (!$bind) {
      if ($this->isDebug) {
        trigger_error(__METHOD__." Bind [$loginWithDomain:***] is failed");
      }
      
      return false;
    }
    
    $hasValidGroup = false;
    $isAdmin = false;
    
    foreach ($ldap->member as $key => $value) {
      $filter = "(&(memberOf=$value)(sAMAccountName=".ldap_escape($cleanLogin, null, LDAP_ESCAPE_FILTER)."))";
      $resultSearchLDAP = ldap_search($connect, $ldap->base, $filter);
      
      if ($this->isDebug) {
        trigger_error(__METHOD__." Base $ldap->base\n Filter $filter");
      }
      
      if (false === $resultSearchLDAP) {
        
        continue;
      }
      
      $resultEnter = ldap_get_entries($connect, $resultSearchLDAP);
      $cnt = (int)($resultEnter['count'] ?? 0);
      
      if (1 === $cnt) {
        $hasValidGroup = true;
        
        if ('admin' === $key) {
          $isAdmin = true;
        }
      }
    }
    
    $this->session->setAdminStatus($isAdmin);
    $this->session->setLoginStatus($hasValidGroup);
    
    if ($this->isDebug) {
      trigger_error(__METHOD__.' Result ['.$this->session->getUserLogin().'] isAdmin ['.$this->session->isAdmin().']');
    }
    
    return $this->session->isLogin();
  }
}