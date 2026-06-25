<?php

namespace okushnirov\core\Library;

use core\Render\Auth\Authentication;
use okushnirov\core\Library\Enums\SessionType;

final class Location
{
  public static string $folder = '';
  
  public static function authRedirect(bool $isAuth = false):void
  {
    if ($isAuth) {
      $auth = new Authentication();
      
      exit($auth::render());
    }
  }
  
  public static function errorRedirect(bool $isError = false):void
  {
    if ($isError) {
      header('Location: /error/');
      
      exit;
    }
  }
  
  public static function getLocation(string $endSlash = '/'):string
  {
    
    return '' === self::$folder || '/' === self::$folder
      ? self::serverName($endSlash)
      : self::serverName('').'/'.trim(self::$folder, '/').$endSlash;
  }
  
  public static function httpsRedirect(string $request = ''):void
  {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    
    if (!$isSecure) {
      $request = trim($request, '/');
      
      if ('' === $request || self::$folder === $request) {
        $targetUrl = self::getLocation();
      } else {
        $targetUrl = str_starts_with($request, 'http://') || str_starts_with($request, 'https://')
          ? preg_replace('/^http:/i', 'https:', $request) : self::getLocation('').'/'.$request;
      }
      
      header('Location: '.$targetUrl);
      
      exit;
    }
  }
  
  public static function logout(
    string $location, string $query, SessionType $session = SessionType::WS, int $flag = 0):void
  {
    parse_str(mb_strtolower($query), $result);
    
    if (!isset($result['logout'])) {
      
      return;
    }
    
    if (SessionType::NONE !== $session && (session_id() || Session::sessionStart($session))) {
      Session::sessionDestroy();
    }
    
    if ($flag || '' === $location) {
      header('Location:'.self::serverName());
    } else {
      header('Location:'.self::serverName(false).parse_url($location, PHP_URL_PATH));
    }
    
    exit;
  }
  
  public static function serverName(bool | string $endSlash = '/'):string
  {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    
    $scheme = $isSecure ? 'https' : 'http';
    $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $port = (int)($_SERVER['SERVER_PORT'] ?? 80);
    
    $name = $scheme.'://'.$host;
    
    if (($isSecure && $port !== 443) || (!$isSecure && $port !== 80)) {
      if (!str_contains($host, ':')) {
        $name .= ':'.$port;
      }
    }
    
    if (is_bool($endSlash)) {
      $name .= $endSlash ? '/' : '';
    } else {
      $name .= $endSlash;
    }
    
    return $name;
  }
}