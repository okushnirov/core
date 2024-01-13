<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\CustomerType;

final class Customer
{
  public static int $errorCode = 0;
  
  public static function checkCode(string $code, CustomerType | int | string $type):bool
  {
    # Наявність 6 або більше символів, що повторюються
    preg_match_all('/(.)\1{6,}/', $code, $matches, PREG_SET_ORDER);
    
    if (isset($matches[0])) {
      self::$errorCode = -2;
      
      return false;
    }
    
    $return = match ($type instanceof CustomerType ? $type : CustomerType::getType($type)) {
      CustomerType::COMPANY => preg_match('/^\d{8}$/', $code),
      CustomerType::BUSINESSMAN => Str::isINN($code),
      CustomerType::PERSON => Str::isINN($code)
        # Серія та номер паспорта
        || preg_match('/^[АБВГДЕЖЗИКЛМНОПРСТУФХЧШЮЯ]{2}\d{6}$/u', $code)
        # Номер паспорта у вигляді ID карти
        || preg_match('/^\d{9}$/', $code),
      # Помилка типу клієнта
      default => false
    };
    
    self::$errorCode = $return ? 0 : -1;
    
    return $return;
  }
}