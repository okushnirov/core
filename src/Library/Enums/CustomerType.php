<?php

namespace okushnirov\core\Library\Enums;

enum CustomerType
{
  case COMPANY;
  
  case BUSINESSMAN;
  
  case PERSON;
  
  public static function getType(int | string $type):?CustomerType
  {
    
    return match (mb_convert_case(trim((string)$type), MB_CASE_UPPER)) {
      '3', 'ЮЛ', 'СУБЪЕКТ_ЮЛ' => CustomerType::COMPANY,
      '4', 'ФОП', 'СУБЪЕКТ_ФОП' => CustomerType::BUSINESSMAN,
      '5', 'ФЛ', 'СУБЪЕКТ_ФЛ' => CustomerType::PERSON,
      default => null
    };
  }
  
  public static function fromName(string $name):CustomerType
  {
    foreach (self::cases() as $status) {
      if ($name === $status->name) {
        
        return $status;
      }
    }
    
    throw new \ValueError("$name is not a valid backing value for enum ".self::class);
  }
}