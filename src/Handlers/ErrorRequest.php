<?php

namespace okushnirov\core\Handlers;

define('DO_LANG_HANDLER', 1);

define('DO_REQUEST_HANDLER', 2);

use core\{Handlers\ErrorLang, Handlers\ErrorPath, Root\Folders\Error};
use okushnirov\core\Library\{Enums\CookieType, Enums\SessionType, Location, Session};

final class ErrorRequest
{
  private static int $http_code;
  
  private static string $request;
  
  public function __construct(string $request, int $http_code, array $trace = [])
  {
    self::$request = $request;
    self::$http_code = empty($http_code) ? 404 : $http_code;
    
    if (!empty($trace)) {
      if (isset($trace['SESSION'])) {
        $trace['SESSION'] = Session::decryptCRC($trace['SESSION']);
      }
      
      trigger_error(__METHOD__."\nHTTP ".self::$http_code."\n".json_encode($trace,
          JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), E_USER_WARNING);
    }
  }
  
  public static function run(
    $flags = 0, SessionType $session = SessionType::NONE, CookieType $cookie = CookieType::No):void
  {
    Location::$folder = 'error';
    
    switch (self::$http_code) {
      # 401 Unauthorized
      # 403 Forbidden
      case 401:
      case 403:
        try {
          (new Error())::index(self::$http_code, 0, '', self::$request);
        } catch (\Exception) {
          header('Location: '.Location::serverName());
        }
        
        exit;
      
      # 200 OK
      # 404 Not Found
      case 200:
      case 404:
        $path = explode('/', trim(mb_strtolower(parse_url(self::$request, PHP_URL_PATH)), '/'));
        Location::$folder = 1 < count($path) ? implode('/', $path) : ($path[0] ?? Location::$folder);
        
        if ($flags & DO_LANG_HANDLER && !empty($path[0])) {
          try {
            $result = ErrorLang::run($path[0], $session, $cookie);
          } catch (\Exception $e) {
            trigger_error($e->getMessage());
            $result = false;
          }
          
          if ($result) {
            array_shift($path);
            
            if (empty($path)) {
              header('Location: '.Location::getLocation());
              
              exit;
            }
            
            # Cut language from request
            self::$request = mb_substr(self::$request, 3);
          }
        }
        
        Location::$folder = 1 < count($path) ? implode('/', $path) : ($path[0] ?? Location::$folder);
        
        # Cut separator from request
        self::$request = Location::serverName().ltrim(self::$request, '/');
        
        # Path handler (If it needs)
        if ($flags & DO_REQUEST_HANDLER && 'error' !== Location::$folder) {
          try {
            ErrorPath::run(self::$request);
          } catch (\Exception $e) {
            trigger_error($e->getMessage());
          }
        }
        
        break;
      
      default:
        
        exit(http_response_code(self::$http_code));
    }
    
    try {
      (new Root())::handler(Location::$folder, self::$request, session: $session, cookie: $cookie);
    } catch (\Exception $e) {
      try {
        (new Error())::index(self::$http_code, $e->getCode(), $e->getMessage(), self::$request);
      } catch (\Exception $e) {
        trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
        
        header('Location: '.Location::serverName());
      }
    }
  }
}