<?php

namespace okushnirov\core\Handlers;

define('DO_REQUEST_HANDLER', 1);

use core\{Handlers\ErrorPath, Root\Folders\Error};
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
          JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), E_USER_ERROR);
    }
    
    Location::$folder = 'error';
  }
  
  public static function run(
    $flags = 0, SessionType $session = SessionType::NONE, CookieType $cookie = CookieType::No):void
  {
    switch (self::$http_code) {
      case 200:
      case 404:
        
        break;
      
      case 401:
      case 403:
        try {
          (new Error())::index(self::$http_code, 0, '', self::$request);
        } catch (\Exception) {
          header('Location: '.Location::serverName());
        }
        
        exit;
      
      default:
        
        exit(http_response_code(self::$http_code));
    }
    
    $path = explode('/', trim(mb_strtolower(parse_url(self::$request, PHP_URL_PATH)), '/'));
    Location::$folder = 1 < count($path) ? implode('/', $path) : ($path[0] ?? Location::$folder);
    self::$request = Location::serverName().ltrim(self::$request, '/');
    
    if ($flags & DO_REQUEST_HANDLER && 'error' !== Location::$folder) {
      try {
        ErrorPath::run(self::$request);
      } catch (\Exception $e) {
        trigger_error($e->getMessage());
      }
    }
    
    try {
      Root::handler(Location::$folder, self::$request, session: $session, cookie: $cookie);
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