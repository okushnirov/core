<?php

namespace okushnirov\core\Handlers;

define('DO_LOGIN_HANDLER', 1);

define('DO_RELOAD_HOME', 2);

use core\Root\Folders\Error;
use okushnirov\core\Library\{Enums\Auth, Enums\CookieType, Enums\SessionType, Lang, Location, Session};

final class Root extends \Exception
{
  const ROOT_PATH = 'core\Root\\';
  
  const ROOT_FOLDERS = 'Folders\\';
  
  public static bool $debug = false;
  
  public static ?array $path;
  
  public static ?string $query;
  
  public static function handler(
    string     $folder, string $request = '/', ?Auth $authType = null, SessionType $session = SessionType::WS,
    CookieType $cookie = CookieType::No, bool $redirect = true, int $flag = 0):void
  {
    self::$path = explode('/', mb_strtolower(trim((string)parse_url($request, PHP_URL_PATH), '/')));
    self::$query = trim((string)parse_url($request, PHP_URL_QUERY));
    
    Location::$folder = $folder;
    
    if (self::$debug) {
      trigger_error(__METHOD__." Start\n".json_encode([
          "Auth " => $authType->name ?? '',
          "COOKIE type [$cookie->name]" => $_COOKIE ?? [],
          "Folder" => $folder,
          'Path' => self::$path,
          'Query' => $request,
          'Redirect' => $redirect,
          "SESSION type [$session->name]" => Session::decryptCRC($_SESSION ?? [])
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    if (SessionType::NONE !== $session && Session::sessionStart($session)) {
      $_SESSION['folder'] = $folder;
      
      if (self::$debug) {
        trigger_error(__METHOD__."\nSession id = ".session_id()."\nSession folder = {$_SESSION['folder']}",
          E_USER_ERROR);
      }
    }
    
    if (self::$query) {
      Location::logout($request, self::$query, $session, $flag & DO_RELOAD_HOME);
    }
    
    if ($redirect) {
      Location::httpsRedirect($request);
    }
    
    Lang::set($session, $cookie);
    
    if (!is_null($authType)) {
      RootLogin::handler($authType, $flag & DO_LOGIN_HANDLER);
    }
    
    if ('' === (self::$path[0] ?? '')) {
      $className = self::ROOT_PATH.('/' === $folder ? 'Index' : self::ROOT_FOLDERS.ucfirst($folder));
    } else {
      array_walk(self::$path, function(&$value) {
        $value = mb_convert_case($value, MB_CASE_TITLE);
      });
      
      $className = '';
      $classRoot = self::ROOT_PATH.self::ROOT_FOLDERS;
      $fullPath = self::$path;
      $cnt = count(self::$path);
      
      for ($i = 0; $i < $cnt; $i++) {
        $classPath = implode('\\', $fullPath);
        $className = $classRoot.$classPath;
        
        if (self::$debug) {
          trigger_error(__METHOD__." Find className: $className...", E_USER_ERROR);
        }
        
        if (class_exists($className) && method_exists($className, 'index')) {
          
          break;
        }
        
        array_pop($fullPath);
        $className = '';
      }
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__." Set className: $className", E_USER_ERROR);
    }
    
    if ($className) {
      try {
        (new $className)::index();
        
        exit;
      } catch (\Exception $e) {
        trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
      }
    }
    
    try {
      (new Error())::index(404, 0, '', $request);
    } catch (\Exception $e) {
      trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
      
      throw new \Exception($e->getMessage(), $e->getCode());
    }
  }
}