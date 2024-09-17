<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\{Enums\Charset, Enums\DateEn, Enums\DateRu, Interfaces\DateFormat};

final class Str
{
  public static function getDate(
    mixed  $dateIn, DateFormat $formatOut = DateRu::ISO, string $alternativeText = '', string $startText = '',
    string $endText = ''):string
  {
    # IN: error date OUT: alternative text
    if ($dateIn == 'NA' || empty($dateIn) || is_bool($dateIn)) {
      
      return $alternativeText;
    }
    
    # IN: YYYY-MM-DD OUT: d.m.Y H:i:s || Y-m-d H:i:s
    if ($formatOut === DateRu::DATETIME || $formatOut === DateEn::DATETIME) {
      $dateOut = strtotime($dateIn);
      
      return empty($dateOut) ? '' : self::wrapText(date($formatOut->value, $dateOut), $startText, $endText);
    }
    
    # IN: YYYY-MM-DD OUT: d.m.Y H:i || Y-m-d H:i
    if ($formatOut === DateRu::TIMESTAMP || $formatOut === DateEn::TIMESTAMP) {
      $dateOut = strtotime($dateIn);
      
      return empty($dateOut) ? '' : self::wrapText(date($formatOut->value, $dateOut), $startText, $endText);
    }
    
    # IN: YYYY-MM-DD OUT: DD.MM.YYYY
    $dateOut = date_create($dateIn);
    
    if ($formatOut === DateRu::ISO) {
      
      return empty($dateOut) ? '' : self::wrapText($dateOut->format($formatOut->value), $startText, $endText);
    }
    
    # IN: DD.MM.YYYY OUT: YYYY-MM-DD
    return empty($dateOut) ? '' : self::wrapText($dateOut->format(DateEn::ISO->value), $startText, $endText);
  }
  
  public static function getNumber(
    mixed  $number, int $decimal = 2, string $emptyText = '', string $decimalSeparator = '.',
    string $thousandSeparator = ' ', string $startText = '', string $endText = ''):bool | string
  {
    
    return empty($number)
      ? $emptyText
      : self::wrapText(mb_eregi_replace('_', $thousandSeparator,
        number_format((float)$number, $decimal, $decimalSeparator, '_')), $startText, $endText);
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
  
  public static function transformAccount(string $account):string
  {
    if (19 !== strlen($account)) {
      
      return $account;
    }
    
    $array = str_split($account);
    
    array_splice($array, 4, 0, ' ');
    array_splice($array, 7, 0, ' ');
    array_splice($array, 11, 0, ' ');
    array_splice($array, 15, 0, ' ');
    array_splice($array, 19, 0, ' ');
    array_splice($array, 23, 0, ' ');
    
    return implode('', $array);
  }
  
  public static function transformIBAN(string $iban):string
  {
    if (29 !== strlen($iban)) {
      
      return $iban;
    }
    
    $chunk = substr($iban, 0, 10);
    $account = substr($iban, 10);
    
    $array = str_split($chunk);
    array_splice($array, 2, 0, ' ');
    array_splice($array, 5, 0, ' ');
    
    return implode('', $array).' '.self::transformAccount($account);
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
  
  private static function wrapText(string $text, string $startText = '', string $endText = ''):string
  {
    
    return '' === $text ? $text : "$startText$text$endText";
  }
}