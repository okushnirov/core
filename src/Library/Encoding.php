<?php

namespace okushnirov\core\Library;

final class Encoding
{
  public static function decode(mixed $value):string
  {
    if (self::isWrongValue($value)) {
      
      return '';
    }
    
    $stringValue = (string)$value;
    $from = self::getDBaseEncoding();
    $to = self::getHtmlEncoding();
    
    return '' === $stringValue || $from === $to ? $stringValue : mb_convert_encoding($stringValue, $to, $from);
  }
  
  public static function encode(mixed $value):string
  {
    if (self::isWrongValue($value)) {
      
      return '';
    }
    
    $stringValue = (string)$value;
    $from = self::getHtmlEncoding();
    $to = self::getDBaseEncoding();
    
    return '' === $stringValue || $from === $to ? $stringValue : mb_convert_encoding($stringValue, $to, $from);
  }
  
  private static function getDBaseEncoding():string
  {
    
    return defined('CODE_PAGE_DBASE') ? CODE_PAGE_DBASE : 'WINDOWS-1251';
  }
  
  private static function getHtmlEncoding():string
  {
    
    return defined('CODE_PAGE_HTML') ? CODE_PAGE_HTML : 'UTF-8';
  }
  
  private static function isWrongValue(mixed $value):bool
  {
    
    return is_null($value) || is_array($value) || (is_object($value) && !method_exists($value, '__toString'));
  }
}