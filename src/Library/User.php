<?php

namespace okushnirov\core\Library;

final class User extends Person
{
  public static bool $isAdmin = false;
  
  public static bool $isAvatar = false;
  
  public static bool $isDev = false;
  
  public static bool $isLogin = false;
  
  public static Partner $partner;
  
  public static mixed $settings;
  
  public function __construct(mixed $settings = null)
  {
    self::$settings = $settings;
    
    self::$isAdmin = !empty($_SESSION['isAdmin']);
    
    self::$isAvatar = !empty($_SESSION['isAvatar']);
    
    self::$isDev = !empty($_SESSION['isDev']);
    
    self::$isLogin = !empty($_SESSION['isLogin']);
    
    if (!self::$isLogin) {
      
      return;
    }
    
    $session = Session::decryptCRC($_SESSION, true);
    $user = $session['CRC'] ?? [];
    
    if (!isset($user['id'])) {
      
      return;
    }
    
    self::$partner = (new Partner)->setValue($user);
    
    (new Person)::setValue($user);
  }
}