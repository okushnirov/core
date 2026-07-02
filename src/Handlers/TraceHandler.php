<?php

namespace okushnirov\core\Handlers;

use okushnirov\core\Library\Session;

final class TraceHandler
{
  private int $http_code;
  private array $trace;
  
  public function __construct(int $http_code, array $trace)
  {
    $this->http_code = $http_code;
    $this->trace = $trace;
  }
  
  public function log():void
  {
    if (isset($this->trace['SESSION'])) {
      $this->trace['SESSION'] = Session::decryptCRC($this->trace['SESSION']);
    }
    
    trigger_error("HTTP ".$this->http_code."\n".json_encode($this->trace,
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), E_USER_ERROR);
  }
}