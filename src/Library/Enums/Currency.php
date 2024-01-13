<?php

namespace okushnirov\core\Library\Enums;

enum Currency: string
{
  case UAH = 'UAH';
  
  case USD = 'USD';
  
  case EUR = 'EUR';
  
  public function getCode():int
  {
    
    return match ($this) {
      Currency::UAH => 980,
      Currency::USD => 840,
      Currency::EUR => 978
    };
  }
}