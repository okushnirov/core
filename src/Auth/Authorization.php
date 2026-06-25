<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Library\Enums\{Auth, SessionType};

final class Authorization
{
  private bool $isDebug;
  private UserSession $session;
  
  public function __construct(
    UserSession $session, ?string $userLogin = null, string $userPassword = '', bool $isDebug = false)
  {
    $this->session = $session;
    $this->isDebug = $isDebug;
    
    if ($this->session->isLogin()) {
      $decryptedLogin = $session->getSessionLogin();
      $decryptedPassword = $session->getSessionPassword();
      
      $this->session->initCredentials($userLogin ?? $decryptedLogin,
        '' === $userPassword ? $decryptedPassword : $userPassword);
    } else {
      $this->session->initCredentials($userLogin, $userPassword);
    }
    
    if ($this->isDebug) {
      trigger_error(__METHOD__.' user['.$this->session->getUserLogin().'] isLogin['.$this->session->isLogin().'] call['
        .(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? 'Unknown file').']');
    }
  }
  
  public function check(
    Auth $type = Auth::DB_USER, bool | int | null $connection = false, SessionType $sessionType = SessionType::WS):bool
  {
    if ($this->isDebug) {
      trigger_error(__METHOD__." authType[$type->name] connection[$connection] session[$sessionType->name]");
    }
    
    if (is_null($this->session->getUserLogin())) {
      $this->logout();
      
      return false;
    }
    
    $strategy = AuthStrategyFactory::create($type, $this->session, $connection, $this->isDebug);
    $isLoginSuccess = $strategy->authenticate();
    
    if (!$isLoginSuccess) {
      $this->logout();
      
      return false;
    }
    
    if (Auth::WS === $type || Auth::WS_DATA === $type) {
      
      return SessionType::NONE === $sessionType || $this->session->saveUserSession();
    }
    
    try {
      $xmlUserData = (new UserAuthWS($this->session->getUserLogin(), null, $sessionType))->get();
    } catch (\Exception $e) {
      $xmlUserData = null;
      
      trigger_error(__METHOD__.' UserAuthWS get error code ['.$e->getCode().'] message ['.$e->getMessage().']');
    }
    
    if (!$xmlUserData) {
      $this->logout();
      
      return false;
    }
    
    $this->session->authorizeUser($xmlUserData);
    
    return SessionType::NONE === $sessionType || $this->session->saveUserSession();
  }
  
  public function isActiveUserSession():bool
  {
    
    return $this->session->saveUserSession();
  }
  
  public function logout():void
  {
    $this->session->clear();
  }
}