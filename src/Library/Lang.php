<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{CookieType, SessionType};

final class Lang
{
  public static bool $debug = false;
  
  public static string $lang = '';
  
  public static mixed $settings;
  
  public static function getSettings():void
  {
    self::$settings = File::parse(['/json/language.json']);
  }
  
  public static function set(SessionType $session = SessionType::WS, CookieType $cookie = CookieType::Yes):void
  {
    if (self::$debug) {
      trigger_error(__METHOD__.json_encode([
          'ARGUMENTS' => [
            'session' => $session->name,
            'cookie' => $cookie->name
          ],
          'REQUEST' => $_REQUEST['lang'] ?? '',
          'COOKIE' => $_COOKIE['lang'] ?? '',
          'session_id' => session_id(),
          'SESSION' => $_SESSION['lang'] ?? ''
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    if (empty(self::$settings)) {
      self::getSettings();
    }
    
    if (!isset(self::$settings->language->default)) {
      if (self::$debug) {
        trigger_error(__METHOD__.' no language settings found');
      }
      
      return;
    }
    
    if (isset($_REQUEST['lang']) && self::existsLang($_REQUEST['lang'])) {
      self::$lang = $_REQUEST['lang'];
      
      if (self::$debug) {
        trigger_error(__METHOD__.' Request read ['.self::$lang.']');
      }
      
      goto set;
    }
    
    if (CookieType::Yes === $cookie && self::existsLang($_COOKIE['lang'] ?? '')) {
      self::$lang = $_COOKIE['lang'];
      
      if (self::$debug) {
        trigger_error(__METHOD__.' Cookie read ['.self::$lang.']');
      }
      
      goto set;
    }
    
    if (SessionType::NONE !== $session && Session::sessionStart($session)
      && self::existsLang($_SESSION['lang'] ?? '')) {
      self::$lang = $_SESSION['lang'];
      
      if (self::$debug) {
        trigger_error(__METHOD__.' Session read ['.self::$lang.']');
      }
    }
    
    set:
    
    self::$lang = self::existsLang(self::$lang) ? self::$lang : self::$settings->language->default;
    
    if (CookieType::Yes === $cookie && ($_COOKIE['lang'] ?? '') !== self::$lang) {
      if (!session_id()) {
        session_set_cookie_params([
          'path' => '/',
          'secure' => 80 !== (TEST_SERVER ? TEST_SERVER_PORT : SERVER_PORT),
          'httponly' => true,
          'samesite' => 'Lax'
        ]);
      }
      
      setcookie('lang', self::$lang, [
        'expires' => time() + 2592000,
        'path' => '/',
        'secure' => 80 !== (TEST_SERVER ? TEST_SERVER_PORT : SERVER_PORT),
        'httponly' => true,
        'samesite' => 'Lax',
      ]);
      $_COOKIE['lang'] = self::$lang;
      
      if (self::$debug) {
        trigger_error(__METHOD__." Cookie set [{$_COOKIE['lang']}]");
      }
    }
    
    if (SessionType::NONE !== $session && Session::sessionStart($session)
      && ($_SESSION['lang'] ?? '') !== self::$lang) {
      $_SESSION['lang'] = self::$lang;
      
      if (self::$debug) {
        trigger_error(__METHOD__." Session set [{$_SESSION['lang']}]");
      }
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__.json_encode([
          'Lang::$lang' => self::$lang,
          'Session ID' => session_id(),
          'Session ' => $_SESSION['lang'] ?? '',
          'Cookie' => $_COOKIE['lang'] ?? ''
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
  }
  
  private static function existsLang(?string $lang):bool
  {
    
    return property_exists(self::$settings->language->lang, $lang);
  }
}