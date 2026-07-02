<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\Charset;

final class Encoding
{
  public static function decode(mixed $value, Charset $from = Charset::WINDOWS1251, Charset $to = Charset::UTF8):string
  {
    if (self::isWrongValue($value)) {
      
      return '';
    }
    
    $fromValue = $from->value;
    $toValue = $to->value;
    $stringValue = (string)$value;
    
    return '' === $stringValue || $fromValue === $toValue
      ? $stringValue
      : mb_convert_encoding($stringValue, $toValue, $fromValue);
  }
  
  public static function encode(mixed $value, Charset $from = Charset::UTF8, Charset $to = Charset::WINDOWS1251):string
  {
    
    return self::decode($value, $from, $to);
  }
  
  private static function isWrongValue(mixed $value):bool
  {
    
    return is_null($value) || is_array($value) || (is_object($value) && !method_exists($value, '__toString'));
  }
}