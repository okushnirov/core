<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\SessionType;

final class Location
{
  public static string $folder = '';
  
  public static function errorRedirect(bool $accessDenied = false):void
  {
    if ($accessDenied) {
      header('Location: /error/');
      
      exit;
    }
  }
  
  public static function getLocation(string $endSlash = '/'):string
  {
    
    return '' === self::$folder || '/' === self::$folder ? self::serverName($endSlash)
      : self::serverName().self::$folder.$endSlash;
  }
  
  public static function httpsRedirect(string $location = '/'):void
  {
    $redirect = TEST_SERVER ? TEST_SERVER_REDIRECT : SERVER_REDIRECT;
    
    if (80 === (int)$_SERVER['SERVER_PORT'] && $redirect) {
      header('Location: '.('' === $location || '/' === $location ? self::getLocation() : $location));
      
      exit;
    }
  }
  
  public static function logout(
    string $location, string $query, SessionType $session = SessionType::DB, bool $reloadHome = true):void
  {
    parse_str(mb_strtolower($query), $result);
    
    if ('' === $location || !isset($result['logout']) || SessionType::NONE === $session && !session_id()) {
      
      return;
    }
    
    if (session_id() && SessionType::NONE !== $session) {
      Session::sessionDestroy();
    }
    
    if ($reloadHome) {
      header('Location:'.self::serverName());
    } else {
      header('Location:'.self::serverName(false).parse_url($location, PHP_URL_PATH));
    }
    
    exit;
  }
  
  public static function serverName(string $endSlash = '/'):string
  {
    $port = TEST_SERVER ? TEST_SERVER_PORT : SERVER_PORT;
    
    $name = 'http';
    $name .= 80 !== $port ? 's' : '';
    $name .= '://'.$_SERVER['SERVER_NAME'];
    $name .= 80 === $port || 443 === $port ? '' : ":$port";
    $name .= $endSlash;
    
    return $name;
  }
}