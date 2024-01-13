<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\{Enums\Decrypt, Enums\Encrypt, Interfaces\CryptType};

final class Crypt
{
  public static function action(string $string, CryptType $variant):bool | string
  {
    
    return '' === $string
      ? ''
      : match ($variant) {
        Encrypt::BASE => self::__encryptString($string),
        Decrypt::BASE => self::__decryptString($string),
        Encrypt::CHR => self::__encryptString($string, true),
        Decrypt::CHR => self::__decryptString($string, true),
        Encrypt::INT => self::__encryptInteger($string),
        Decrypt::INT => self::__decryptInteger($string),
        default => false
      };
  }
  
  protected static function __decryptInteger(int | string $string):string
  {
    
    return substr(base_convert(base_convert(base_convert((int)$string, 8, 10), 8, 10), 8, 10), 2, -2);
  }
  
  protected static function __decryptString(string $string, bool $random = false):string
  {
    if (!$random) {
      
      return base64_decode(base64_decode($string));
    }
    
    $stringChange = self::__safeEncrypt(base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=')),
      1);
    
    $stringString = '';
    
    for ($i = 2; $i < strlen($stringChange); $i += 3) {
      $stringString .= $stringChange[$i];
    }
    
    return $stringString;
  }
  
  protected static function __encryptInteger(string $string):string
  {
    
    return base_convert(base_convert(base_convert((int)(rand(10, 99).$string.rand(10, 99)), 10, 8), 10, 8), 10, 8);
  }
  
  protected static function __encryptString(string $string, bool $random = false):string
  {
    if (!$random) {
      
      return base64_encode(base64_encode($string));
    }
    
    $stringChange = '';
    
    for ($i = 0; $i < strlen($string); $i++) {
      $stringChange .= rand(10, 99).$string[$i];
    }
    
    return rtrim(strtr(base64_encode(self::__safeEncrypt($stringChange.rand(10, 99))), '+/', '-_'), '=');
  }
  
  protected static function __safeEncrypt(string $string, bool $decrypt = false):string
  {
    $o = $s1 = $s2 = [];
    $based = array_merge([
      '?',
      '(',
      '@',
      ';',
      '$',
      '#',
      "]",
      "&",
      '*'
    ], range('a', 'z'), range('A', 'Z'), range(0, 9), [
      '!',
      ')',
      '_',
      '+',
      '|',
      '%',
      '/',
      '[',
      '.',
      ' '
    ]);
    $dimension = 9;
    
    for ($i = 0; $i < $dimension; $i++) {
      for ($j = 0; $j < $dimension; $j++) {
        $s1[$i][$j] = $based[$i * $dimension + $j];
        $s2[$i][$j] = str_rot13($based[($dimension * $dimension - 1) - ($i * $dimension + $j)]);
      }
    }
    
    unset($based);
    
    $m = floor(strlen($string) / 2) * 2;
    $symbol = $m == strlen($string) ? '' : $string[strlen($string) - 1];
    $al = [];
    
    for ($ii = 0; $ii < $m; $ii += 2) {
      $symbol_1 = $symbol_11 = $string[$ii];
      $symbol_2 = $symbol_22 = $string[$ii + 1];
      $a1 = $a2 = [];
      
      for ($i = 0; $i < $dimension; $i++) {
        for ($j = 0; $j < $dimension; $j++) {
          if ($symbol_1 === strval($decrypt ? $s2[$i][$j] : $s1[$i][$j])) {
            $a1 = [
              $i,
              $j
            ];
          }
          
          if ($symbol_2 === strval($decrypt ? $s1[$i][$j] : $s2[$i][$j])) {
            $a2 = [
              $i,
              $j
            ];
          }
          
          if (!empty($symbol) && $symbol === strval($decrypt ? $s2[$i][$j] : $s1[$i][$j])) {
            $al = [
              $i,
              $j
            ];
          }
        }
      }
      
      if (sizeof($a1) && sizeof($a2)) {
        $symbol_11 = $decrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
        $symbol_22 = $decrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
      }
      
      $o[] = $symbol_11.$symbol_22;
    }
    
    if (!empty($symbol) && sizeof($al)) {
      $o[] = $decrypt ? $s1[$al[1]][$al[0]] : $s2[$al[1]][$al[0]];
    }
    
    return implode('', $o);
  }
}