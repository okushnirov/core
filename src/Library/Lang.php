<?php

namespace okushnirov\core\Library;

use core\Handlers\LangHandler;
use okushnirov\core\Library\Enums\{CookieType, SessionType};

final class Lang
{
  public static string $lang = '';
  
  public static ?LangHandler $language;
  
  public static function getShort(string $lang = ''):string
  {
    
    return LangHandler::tryFrom($lang ? : self::$lang)
                      ?->short() ?? '';
  }
  
  public static function set(SessionType $session = SessionType::WS, CookieType $cookie = CookieType::Yes):void
  {
    if (!enum_exists(LangHandler::class)) {
      trigger_error(__METHOD__.' No language enum handler found', E_USER_WARNING);
      
      return;
    }
    
    if (isset($_REQUEST['lang']) && is_string($_REQUEST['lang']) && self::existsLang($_REQUEST['lang'])) {
      self::$lang = $_REQUEST['lang'];
    } elseif (CookieType::Yes === $cookie && isset($_COOKIE['lang']) && is_string($_COOKIE['lang'])
      && self::existsLang($_COOKIE['lang'])) {
      self::$lang = $_COOKIE['lang'];
    } elseif (SessionType::NONE !== $session && Session::sessionStart($session)
      && isset($_SESSION['lang'])
      && is_string($_SESSION['lang'])
      && self::existsLang($_SESSION['lang'])) {
      self::$lang = $_SESSION['lang'];
    }
    
    self::$lang = self::$lang ? : (LangHandler::cases()[0]?->value ?? '');
    self::$language = LangHandler::tryFrom(self::$lang);
    
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    
    if (CookieType::Yes === $cookie && ($_COOKIE['lang'] ?? '') !== self::$lang && !headers_sent()) {
      $cookieParams = [
        'expires' => time() + 2592000,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
      ];
      
      if (!session_id()) {
        session_set_cookie_params([
          'path' => $cookieParams['path'],
          'secure' => $cookieParams['secure'],
          'httponly' => $cookieParams['httponly'],
          'samesite' => $cookieParams['samesite'],
        ]);
      }
      
      setcookie('lang', self::$lang, $cookieParams);
      $_COOKIE['lang'] = self::$lang;
    }
    
    if (SessionType::NONE !== $session && Session::sessionStart($session)
      && ($_SESSION['lang'] ?? '') !== self::$lang) {
      $_SESSION['lang'] = self::$lang;
    }
  }
  
  private static function existsLang(string $lang):bool
  {
    
    return !is_null(LangHandler::tryFrom($lang));
  }
}