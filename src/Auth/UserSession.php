<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Library\{Crypt, Enums\Decrypt, Enums\Encrypt};

final class UserSession
{
  private array $crcData = [];
  private bool $isAdmin = false;
  private bool $isAvatar = false;
  private bool $isDev = false;
  private bool $isLogin;
  private ?int $userID = null;
  private ?string $userLogin = null;
  private string $userPassword = '';
  
  public function __construct()
  {
    $this->isLogin = !empty($_SESSION['isLogin']);
    
    if ($this->isLogin) {
      $this->isAdmin = !empty($_SESSION['isAdmin']);
      $this->isAvatar = !empty($_SESSION['isAvatar']);
      $this->isDev = !empty($_SESSION['isDev']);
      $this->crcData = $_SESSION['CRC'] ?? [];
    }
  }
  
  public function authorizeUser(array | object $userData):void
  {
    $this->isLogin = true;
    $this->isAvatar = false;
    $this->isDev = false;
    $this->crcData = [];
    
    foreach ($userData as $key => $value) {
      $value = (string)$value;
      
      if ('role' === $key) {
        $this->isAdmin = $this->isAdmin || 'рлАдминистратор' === $value;
        $this->isDev = $this->isDev || 'рлРазработчик' === $value;
      }
      
      $this->setCRC($key, 'avatar' === $key ? $value : Crypt::action($value, Encrypt::CHR));
    }
    
    $this->setCRC('hash', Crypt::action(Crypt::action($this->getUserPassword(), Encrypt::CHR), Encrypt::CHR));
  }
  
  public function clear():void
  {
    $this->isLogin = false;
    $this->isAdmin = false;
    $this->isAvatar = false;
    $this->isDev = false;
    $this->userID = null;
    $this->userLogin = null;
    $this->userPassword = '';
    $this->crcData = [];
    
    if (session_id()) {
      unset($_SESSION['CRC'], $_SESSION['isLogin'], $_SESSION['isAdmin'], $_SESSION['isDev'], $_SESSION['csrf_token']);
    }
  }
  
  public function getSessionLogin():string
  {
    
    return Crypt::action($this->crcData['login'] ?? '', Decrypt::CHR);
  }
  
  public function getSessionPassword():string
  {
    
    return Crypt::action(Crypt::action($this->crcData['hash'] ?? '', Decrypt::CHR), Decrypt::CHR);
  }
  
  public function getUserID():?int
  {
    
    return $this->userID;
  }
  
  public function getUserLogin():?string
  {
    
    return $this->userLogin;
  }
  
  public function getUserPassword():string
  {
    
    return $this->userPassword;
  }
  
  public function initCredentials(?string $userLogin, ?int $userID, string $userPassword):void
  {
    $this->userID = is_null($userID) || 0 >= trim($userID) ? null : $userID;
    $this->userLogin = is_null($userLogin) || '' === trim($userLogin) ? null : $userLogin;
    $this->userPassword = $userPassword;
  }
  
  public function isAdmin():bool
  {
    
    return $this->isAdmin;
  }
  
  public function isLogin():bool
  {
    
    return $this->isLogin;
  }
  
  public function saveUserSession():bool
  {
    if ($this->isLogin()) {
      $_SESSION['isLogin'] = $this->isLogin;
      $_SESSION['isAdmin'] = $this->isAdmin;
      $_SESSION['isAvatar'] = $this->isAvatar;
      $_SESSION['isDev'] = $this->isDev;
      $_SESSION['CRC'] = $this->crcData;
      
      if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
      
      return true;
    }
    
    $this->clear();
    
    return false;
  }
  
  public function setCRC(string $key, string $value):void
  {
    $this->crcData[$key] = $value;
  }
  
  public function setAdminStatus(bool $status):void
  {
    $this->isAdmin = $status;
  }
  
  public function setLoginStatus(bool $status):void
  {
    $this->isLogin = $status;
  }
}