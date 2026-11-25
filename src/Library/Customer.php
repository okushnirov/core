<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\CustomerType;

final class Customer
{
  public static int $errorCode = 0;
  
  public static function checkCode(string $code, CustomerType | int | string $type):bool
  {
    $code = trim($code);
    
    if (preg_match('/(.)\1{6,}/u', $code)) {
      self::$errorCode = -2;
      
      return false;
    }
    
    $customerType = $type instanceof CustomerType ? $type : CustomerType::getType($type);
    
    $return = match ($customerType) {
      CustomerType::COMPANY => (bool)preg_match('/^\d{8}$/', $code),
      CustomerType::BUSINESSMAN => Str::isINN($code),
      CustomerType::PERSON => Str::isINN($code)
        || preg_match('/^[АБВГДЕЖЗИКЛМНОПРСТУФХЧШЮЯ]{2}\d{6}$/u', $code)
        || preg_match('/^\d{9}$/', $code),
      default => false
    };
    
    self::$errorCode = $return ? 0 : -1;
    
    return $return;
  }
}