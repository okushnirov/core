<?php

namespace okushnirov\core\Handlers;

define('DO_REQUEST_HANDLER', 1);

use core\Handlers\ErrorRequestHandler;
use core\Root\Folders\Error;
use okushnirov\core\Library\Enums\{CookieType, SessionType};
use okushnirov\core\Library\Location;

final class ErrorRequest
{
  private int $http_code;
  
  private string $request;
  
  public function __construct(string $request, int $http_code, array $trace = [])
  {
    $this->request = $request;
    $this->http_code = $http_code ? : 404;
    
    if (!empty($trace)) {
      (new TraceHandler($this->http_code, $trace))->log();
    }
    
    Location::$folder = 'error';
  }
  
  public function run(
    int $flags = 0, SessionType $session = SessionType::NONE, CookieType $cookie = CookieType::No):void
  {
    switch ($this->http_code) {
      case 200:
      case 404:
        
        break;
      
      case 401:
      case 403:
        try {
          (new Error([], $session, $cookie))::index($this->http_code, 0, '', $this->request);
        } catch (\Exception) {
          header('Location: '.Location::serverName());
        }
        
        exit;
      
      default:
        
        exit(http_response_code($this->http_code));
    }
    
    $path = explode('/', trim(mb_strtolower(parse_url($this->request, PHP_URL_PATH)), '/'));
    Location::$folder = 1 < count($path) ? implode('/', $path) : ($path[0] ?? Location::$folder);
    $absoluteRequest = Location::serverName().ltrim($this->request, '/');
    
    if ($flags & DO_REQUEST_HANDLER && 'error' !== Location::$folder) {
      try {
        ErrorRequestHandler::run($absoluteRequest);
      } catch (\Exception $e) {
        trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
      }
    }
    
    try {
      Root::handler(Location::$folder, $absoluteRequest, session: $session, cookie: $cookie);
    } catch (\Exception $e) {
      try {
        Error::index($this->http_code, $e->getCode(), $e->getMessage(), $absoluteRequest);
      } catch (\Exception $e) {
        trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
        
        header('Location: '.Location::serverName());
      }
    }
  }
}