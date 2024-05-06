<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\{Enums\Charset, Enums\DateEn, Enums\DateRu, Interfaces\DateFormat};

final class Str
{
  public static function getDate(
    mixed $dateIn, DateFormat $formatOut = DateRu::ISO, string $alternativeText = ''):string
  {
    # IN: error date OUT: alternative text
    if ($dateIn == 'NA' || empty($dateIn) || is_bool($dateIn)) {
      
      return $alternativeText;
    }
    
    # IN: YYYY-MM-DD OUT: d.m.Y H:i:s || Y-m-d H:i:s
    if ($formatOut === DateRu::DATETIME || $formatOut === DateEn::DATETIME) {
      $dateOut = strtotime($dateIn);
      
      return empty($dateOut) ? '' : date($formatOut->value, $dateOut);
    }
    
    # IN: YYYY-MM-DD OUT: d.m.Y H:i || Y-m-d H:i
    if ($formatOut === DateRu::TIMESTAMP || $formatOut === DateEn::TIMESTAMP) {
      $dateOut = strtotime($dateIn);
      
      return empty($dateOut) ? '' : date($formatOut->value, $dateOut);
    }
    
    # IN: YYYY-MM-DD OUT: DD.MM.YYYY
    $dateOut = date_create($dateIn);
    
    if ($formatOut === DateRu::ISO) {
      
      return empty($dateOut) ? '' : $dateOut->format($formatOut->value);
    }
    
    # IN: DD.MM.YYYY OUT: YYYY-MM-DD
    return empty($dateOut) ? '' : $dateOut->format(DateEn::ISO->value);
  }
  
  public static function getNumber(
    mixed $number, int $decimal = 2, string $emptyText = '', string $decimalSeparator = '.',
    string $thousandSeparator = ''):bool | string
  {
    
    return empty($number)
      ? $emptyText : mb_eregi_replace('_', $thousandSeparator,
        number_format((float)$number, $decimal, $decimalSeparator, '_'));
  }
  
  public static function isINN(string $inn):bool
  {
    if (!preg_match('/\d{10}/i', $inn)) {
      
      return false;
    }
    
    $check = (-1 * $inn[0] + 5 * $inn[1] + 7 * $inn[2] + 9 * $inn[3] + 4 * $inn[4] + 6 * $inn[5] + 10 * $inn[6] + 5
        * $inn[7] + 7 * $inn[8]) % 11;
    
    return (10 == $check ? 0 : $check) == $inn[9] * 1;
  }
  
  public static function lowerCase(string $value):string
  {
    
    return mb_convert_case($value, MB_CASE_LOWER, Charset::UTF8->value);
  }
  
  public static function prepare(string $string, int $flags = ENT_QUOTES):string
  {
    
    return '' === $string ? '' : htmlspecialchars($string, $flags, Charset::UTF8->value);
  }
  
  public static function removeSpecChar(string $string):array | string
  {
    
    return '' === $string
      ? $string
      : str_replace([
        "\r\n",
        "\r",
        "\n",
        "\t",
        "№",
        '‎',
        ''
      ], '', $string);
  }
  
  public static function replaceHeader(
    string $string = '', Charset $needle = Charset::UTF8, $removeSpecChar = false):string
  {
    $string = Charset::UTF8 === $needle ? (mb_check_encoding($string, Charset::UTF8->value) ? $string
      : Encoding::decode($string)) : $string;
    $string = $string && $removeSpecChar ? self::removeSpecChar($string) : $string;
    
    return mb_eregi_replace((Charset::UTF8 === $needle ? Charset::WINDOWS1251 : Charset::UTF8)->value, $needle->value,
      $string);
  }
  
  public static function separateEntity(string $string, string $tagName):string
  {
    
    return '' === $string
      ? ''
      : ('' === $tagName
        ? $string
        : implode('', array_map(function($value) use ($tagName) {
          
          return "<$tagName>$value</$tagName>";
        }, str_split($string))));
  }
  
  public static function upperCase(string $value):string
  {
    
    return mb_convert_case($value, MB_CASE_UPPER, Charset::UTF8->value);
  }
  
  public static function wordDeclension(int $num, array $word):string
  {
    $cases = [
      2,
      0,
      1,
      1,
      1,
      2
    ];
    
    return $word[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
  }
}