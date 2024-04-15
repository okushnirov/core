<?php

namespace okushnirov\core\Handlers;

use okushnirov\core\Library\{Enums\Auth, Enums\CookieType, Enums\SessionType, Lang, Location, Session};

final class Root extends \Exception
{
  const ROOT_PATH = 'core\Root\\';
  
  const ROOT_FOLDERS = 'Folders\\';
  
  public static bool $debug = false;
  
  public static ?array $path;
  
  public static ?string $query;
  
  public static function handler(
    string     $folder, string $request = '/', ?Auth $loginType = null, SessionType $session = SessionType::WS,
    CookieType $cookie = CookieType::Yes, bool $redirect = true):void
  {
    self::$path = explode('/', mb_strtolower(trim((string)parse_url($request, PHP_URL_PATH), '/')));
    self::$query = trim((string)parse_url($request, PHP_URL_QUERY));
    
    Location::$folder = $folder;
    
    if (self::$debug) {
      trigger_error(__METHOD__." Start\n".json_encode([
          "Auth " => $loginType->name ?? '',
          "COOKIE type [$cookie->name]" => $_COOKIE ?? [],
          "Folder" => $folder,
          'Path' => self::$path,
          'Query' => $request,
          'Redirect' => $redirect,
          'REQUEST' => $_REQUEST ?? [],
          "SESSION type [$session->name]" => Session::decryptCRC($_SESSION ?? [])
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    if (SessionType::NONE !== $session && Session::sessionStart($session)) {
      $_SESSION['folder'] = $folder;
      
      if (self::$debug) {
        trigger_error(__METHOD__."\nSession id = ".session_id()."\nSession folder = {$_SESSION['folder']}");
      }
    }
    
    Location::logout($request, self::$query, $session, false);
    
    if ($redirect) {
      Location::httpsRedirect($request);
    }
    
    Lang::set($session, $cookie);
    
    if (!is_null($loginType)) {
      RootLogin::handler($loginType);
    }
    
    $className = '';
    
    # Index page
    if ('' === (self::$path[0] ?? '')) {
      $className = self::ROOT_PATH.('/' === $folder ? 'Index' : self::ROOT_FOLDERS.ucfirst($folder));
      if (self::$debug) {
        trigger_error(__METHOD__." Stop. Find Index page. Skip search className from path");
      }
    } # Find root page
    else {
      array_walk(self::$path, function(&$value) {
        $value = mb_convert_case($value, MB_CASE_TITLE);
      });
      
      $classRoot = self::ROOT_PATH.self::ROOT_FOLDERS;
      $fullPath = self::$path;
      
      if (self::$debug) {
        trigger_error(__METHOD__." ClassRoot: $classRoot ClassName: $className");
      }
      
      # Find exists class from path
      for ($i = 0; $i < count(self::$path); $i++) {
        $classPath = implode('\\', $fullPath);
        
        if (self::$debug) {
          trigger_error(__METHOD__." Find className: $classRoot$classPath...");
        }
        
        $className = class_exists($classRoot.$classPath) && method_exists($classRoot.$classPath, 'index') ? $classRoot
          .$classPath : '';
        
        if ($className) {
          if (self::$debug) {
            trigger_error(__METHOD__." Found className: $className");
          }
          
          break;
        }
        
        array_pop($fullPath);
      }
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__." Set className: $className");
    }
    
    if ('' === $className) {
      try {
        (new \core\Root\Folders\Error())::index(404, 0, '', $request);
      } catch (\Exception $e) {
        
        throw new \Exception($e->getMessage(), $e->getCode());
      }
    } else {
      try {
        (new $className)::index();
      } catch (\Exception $e) {
        trigger_error($e->getMessage());
        
        (new ErrorRequest('/error/', 404, [
          'REQUEST' => $_REQUEST ?? []
        ]))::run(session: $session, cookie: $cookie);
      }
    }
  }
}