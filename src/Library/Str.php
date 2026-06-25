<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{Charset, DateEn, DateRu};
use okushnirov\core\Library\Interfaces\DateFormat;

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
    # IN: YYYY-MM-DD OUT: d.m.Y H:i || Y-m-d H:i
    if ($formatOut === DateRu::DATETIME
      || $formatOut === DateEn::DATETIME
      || $formatOut === DateRu::TIMESTAMP
      || $formatOut === DateEn::TIMESTAMP) {
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
    if (empty($number)) {
      
      return $emptyText;
    }
    
    $formatted = number_format((float)$number, $decimal, $decimalSeparator, '_');
    
    return self::wrapText(str_replace('_', $thousandSeparator, $formatted), $startText, $endText);
  }
  
  public static function isINN(string $inn):bool
  {
    if (!preg_match('/^\d{10}$/', $inn)) {
      
      return false;
    }
    
    $control = (int)$inn[9];
    $check = (1 * $inn[0] + 2 * $inn[1] + 3 * $inn[2] + 4 * $inn[3] + 5 * $inn[4] + 6 * $inn[5] + 7 * $inn[6] + 8
        * $inn[7] + 9 * $inn[8]) % 11;
    
    if ((10 > $check ? $check : 0) === $control) {
      
      return true;
    }
    
    $check = (-1 * $inn[0] + 5 * $inn[1] + 7 * $inn[2] + 9 * $inn[3] + 4 * $inn[4] + 6 * $inn[5] + 10 * $inn[6] + 5
        * $inn[7] + 7 * $inn[8]) % 11;
    
    return (10 > $check ? $check : 0) === $control;
  }
  
  public static function lowerCase(string $value):string
  {
    
    return mb_convert_case($value, MB_CASE_LOWER, Charset::UTF8->value);
  }
  
  public static function prepare(
    string $string, int $flags = ENT_QUOTES, string $startText = '', string $endText = '',
    string $emptyText = ''):string
  {
    
    return '' === $string
      ? $emptyText
      : self::wrapText(htmlspecialchars($string, $flags, Charset::UTF8->value), $startText, $endText);
  }
  
  public static function removeSpecChar(string $string, array $search = []):array | string
  {
    
    return '' === $string
      ? $string
      : str_replace($search
        ? : [
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
    
    $pattern = (Charset::UTF8 === $needle ? Charset::WINDOWS1251 : Charset::UTF8)->value;
    
    return preg_replace('/'.preg_quote($pattern, '/').'/ui', $needle->value, $string);
  }
  
  public static function separateEntity(string $string, string $tagName):string
  {
    if ('' === $string) {
      
      return '';
    }
    
    if ('' === $tagName) {
      
      return $string;
    }
    
    $chars = mb_str_split($string, 1, Charset::UTF8->value);
    
    return implode('', array_map(fn($value) => "<$tagName>$value</$tagName>", $chars));
  }
  
  public static function transformAccount(string $account, string $startText = '', string $endText = ''):string
  {
    if (19 !== mb_strlen($account)) {
      
      return $account;
    }
    
    $formatted = preg_replace('/(\d{4})(\d{2})(\d{3})(\d{3})(\d{3})(\d{3})(\d)/', '$1 $2 $3 $4 $5 $6 $7', $account);
    
    return self::wrapText($formatted, $startText, $endText);
  }
  
  public static function transformIBAN(string $iban, string $startText = '', string $endText = ''):string
  {
    if (29 !== mb_strlen($iban)) {
      
      return $iban;
    }
    
    $formatted = preg_replace('/([A-Z]{2})(\d{2})(\d{6})(\d{4})(\d{2})(\d{3})(\d{3})(\d{3})(\d{3})(\d)/',
      '$1 $2 $3 $4 $5 $6 $7 $8 $9 $10', $iban);
    
    return self::wrapText($formatted, $startText, $endText);
  }
  
  public static function transliterateUk2En(string $text):string
  {
    if ('' === $text) {
      
      return '';
    }
    
    $t = [
      'А' => 'A',
      'а' => 'a',
      'Б' => 'B',
      'б' => 'b',
      'В' => 'V',
      'в' => 'v',
      'Г' => 'H',
      'г' => 'h',
      'Ґ' => 'G',
      'ґ' => 'g',
      'Д' => 'D',
      'д' => 'd',
      'Е' => 'E',
      'е' => 'e',
      'Є' => 'Ye',
      # на початку слова — Ye, інакше — ie
      'є' => 'ie',
      'Ж' => 'Zh',
      'ж' => 'zh',
      'З' => 'Z',
      'з' => 'z',
      'И' => 'Y',
      'и' => 'y',
      'І' => 'I',
      'і' => 'i',
      'Ї' => 'Yi',
      # на початку слова — Yi, інакше — i
      'ї' => 'i',
      'Й' => 'Y',
      # на початку слова — Y, інакше — i
      'й' => 'i',
      'К' => 'K',
      'к' => 'k',
      'Л' => 'L',
      'л' => 'l',
      'М' => 'M',
      'м' => 'm',
      'Н' => 'N',
      'н' => 'n',
      'О' => 'O',
      'о' => 'o',
      'П' => 'P',
      'п' => 'p',
      'Р' => 'R',
      'р' => 'r',
      'С' => 'S',
      'с' => 's',
      'Т' => 'T',
      'т' => 't',
      'У' => 'U',
      'у' => 'u',
      'Ф' => 'F',
      'ф' => 'f',
      'Х' => 'Kh',
      'х' => 'kh',
      'Ц' => 'Ts',
      'ц' => 'ts',
      'Ч' => 'Ch',
      'ч' => 'ch',
      'Ш' => 'Sh',
      'ш' => 'sh',
      'Щ' => 'Shch',
      'щ' => 'shch',
      'Ю' => 'Yu',
      # на початку слова — Yu, інакше — iu
      'ю' => 'iu',
      'Я' => 'Ya',
      # на початку слова — Ya, інакше — ia
      'я' => 'ia',
      'Ь' => '',
      'ь' => '',
      # апострофи
      '’' => '',
      '\'' => ''
    ];
    
    # Особлива логіка для "Є, Ї, Й, Ю, Я" на початку слова
    $text = preg_replace_callback('/\b[ЄЇЙЮЯєїйюя]/u', function($match) {
      $char = $match[0];
      
      return match ($char) {
        'Є' => 'Ye',
        'є' => 'ye',
        'Ї' => 'Yi',
        'ї' => 'yi',
        'Й' => 'Y',
        'й' => 'y',
        'Ю' => 'Yu',
        'ю' => 'yu',
        'Я' => 'Ya',
        'я' => 'ya',
        default => $char,
      };
    }, $text);
    
    return strtr($text, $t);
  }
  
  public static function upperCase(string $value):string
  {
    
    return mb_convert_case($value, MB_CASE_UPPER, Charset::UTF8->value);
  }
  
  public static function wordDeclension(int $num, array $word):string
  {
    $num = abs($num);
    
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
  
  public static function wrapText(string $text, string $startText = '', string $endText = ''):string
  {
    
    return '' === $text ? $text : "$startText$text$endText";
  }
}