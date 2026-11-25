<?php

namespace okushnirov\core\Library\Number2Word;

enum Language: string
{
  case UK = 'ua';
  case RU = 'ru';
  
  public static function tryFromOrDefault(?string $value):self
  {
    return self::tryFrom(mb_strtolower((string)$value)) ?? self::UK;
  }
}