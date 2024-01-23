<?php

namespace okushnirov\core\Library;

final class Encoding
{
  public static function decode(mixed $value):string
  {
    
    return is_null($value)
      ? '' : (CODE_PAGE_HTML === CODE_PAGE_DBASE ? $value
        : mb_convert_encoding($value, CODE_PAGE_HTML, CODE_PAGE_DBASE));
  }
  
  public static function encode(mixed $value):string
  {
    
    return is_null($value)
      ? '' : (CODE_PAGE_HTML === CODE_PAGE_DBASE ? $value
        : mb_convert_encoding($value, CODE_PAGE_DBASE, CODE_PAGE_HTML));
  }
}