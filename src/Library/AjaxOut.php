<?php

namespace okushnirov\core\Library;

final class AjaxOut extends \stdClass
{
  public string $console = '';
  
  public ?int $errorCode = null;
  public string $errorMessage = '';
  
  public string $html = '';
  
  public bool $reload = false;
  
  public bool $success = false;
}