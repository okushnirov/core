<?php

namespace okushnirov\core\Handlers;

define('DO_LANG_HANDLER', 1);

define('DO_REQUEST_HANDLER', 2);

use core\Handlers\{ErrorPath, ErrorLang};
use okushnirov\core\Library\{Location, Session};

class ErrorRequest
{
  private static int $_http_code;
  
  private static string $_request;
  
  public function __construct(string $request, int $http_code, array $trace = [])
  {
    self::$_request = $request;
    self::$_http_code = empty($http_code) ? 404 : $http_code;
    
    if (!empty($trace)) {
      if (isset($trace['SESSION'])) {
        $trace['SESSION'] = Session::decryptCRC($trace['SESSION']);
      }
      
      trigger_error(__METHOD__."\nHTTP ".self::$_http_code."\n".json_encode($trace,
          JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), E_USER_WARNING);
    }
  }
  
  public static function run($flags = 0):void
  {
    Location::$folder = 'error';
    
    switch (self::$_http_code) {
      # 401 Unauthorized
      # 403 Forbidden
      case 401:
      case 403:
        try {
          (new \core\Root\Folders\Error())::index(self::$_http_code, 0, '', self::$_request);
        } catch (\Exception) {
          header('Location: '.Location::serverName());
        }
        
        exit;
      
      # 200 OK
      # 404 Not Found
      case 200:
      case 404:
        $path = explode('/', trim(mb_strtolower(parse_url(self::$_request, PHP_URL_PATH)), '/'));
        Location::$folder = 1 < count($path) ? implode('/', $path) : $path[0] ?? Location::$folder;
        
        # Language handler
        if ($flags & DO_LANG_HANDLER && !empty($path[0])) {
          try {
            $result = ErrorLang::run($path[0]);
          } catch (\Exception $e) {
            trigger_error($e->getMessage());
            $result = false;
          }
          
          if ($result) {
            array_shift($path);
            
            # Reload page
            if (empty($path)) {
              header('Location: '.Location::getLocation());
              
              exit;
            }
            
            # Cut language from request
            self::$_request = mb_substr(self::$_request, 3);
          }
        }
        
        Location::$folder = 1 < count($path) ? implode('/', $path) : $path[0] ?? Location::$folder;
        
        # Cut folder from request
        self::$_request = Location::serverName().ltrim(self::$_request, '/');
        
        # Path handler (If it needs)
        if ($flags & DO_REQUEST_HANDLER && 'error' !== Location::$folder) {
          try {
            ErrorPath::run(self::$_request);
          } catch (\Exception $e) {
            trigger_error($e->getMessage());
          }
        }
        
        break;
      
      default:
        
        exit(http_response_code(self::$_http_code));
    }
    
    try {
      (new Root())::handler(Location::$folder, self::$_request);
    } catch (\Exception $e) {
      try {
        (new \core\Root\Folders\Error())::index(self::$_http_code, $e->getCode(), $e->getMessage(), self::$_request);
      } catch (\Exception $e) {
        trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
        
        header('Location: '.Location::serverName());
      }
    }
  }
}