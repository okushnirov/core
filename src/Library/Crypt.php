<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\{Enums\Decrypt, Enums\Encrypt, Interfaces\CryptType};

final class Crypt
{
  private const MATRIX_DIMENSION = 9;
  
  public static function action(
    string $string, CryptType $variant, string $startText = '', string $endText = ''):string
  {
    if ('' === $string) {
      
      return '';
    }
    
    $return = match ($variant) {
      Encrypt::BASE => self::encryptString($string),
      Decrypt::BASE => self::decryptString($string),
      Encrypt::CHR => self::encryptString($string, true),
      Decrypt::CHR => self::decryptString($string, true),
      Encrypt::INT => self::encryptInteger($string),
      Decrypt::INT => self::decryptInteger($string)
    };
    
    return '' === $return ? '' : Str::wrapText($return, $startText, $endText);
  }
  
  private static function decryptInteger(int | string $string):string
  {
    $base8to10 = base_convert(base_convert(base_convert((string)$string, 8, 10), 8, 10), 8, 10);
    
    return substr($base8to10, 2, -2);
  }
  
  private static function decryptString(string $string, bool $random = false):string
  {
    if (!$random) {
      
      return base64_decode(base64_decode($string));
    }
    
    $padLength = (4 - (strlen($string) % 4)) % 4;
    $base64 = str_pad(strtr($string, '-_', '+/'), strlen($string) + $padLength, '=');
    
    $decoded = base64_decode($base64);
    $decryptedMatrix = self::safeCrypt($decoded, true);
    
    $result = '';
    $length = strlen($decryptedMatrix);
    
    for ($i = 2; $i < $length; $i += 3) {
      $result .= $decryptedMatrix[$i];
    }
    
    return $result;
  }
  
  private static function encryptInteger(string $string):string
  {
    $salted = rand(10, 99).$string.rand(10, 99);
    
    return base_convert(base_convert(base_convert($salted, 10, 8), 10, 8), 10, 8);
  }
  
  private static function encryptString(string $string, bool $random = false):string
  {
    if (!$random) {
      
      return base64_encode(base64_encode($string));
    }
    
    $salted = '';
    $length = strlen($string);
    
    for ($i = 0; $i < $length; $i++) {
      $salted .= rand(10, 99).$string[$i];
    }
    
    $encryptedMatrix = self::safeCrypt($salted.rand(10, 99));
    
    return rtrim(strtr(base64_encode($encryptedMatrix), '+/', '-_'), '=');
  }
  
  private static function findCoordinates(array $matrix, string $symbol):?array
  {
    for ($i = 0; $i < self::MATRIX_DIMENSION; $i++) {
      for ($j = 0; $j < self::MATRIX_DIMENSION; $j++) {
        if ($matrix[$i][$j] === $symbol) {
          return [
            $i,
            $j
          ];
        }
      }
    }
    
    return null;
  }
  
  private static function generateMatrices():array
  {
    $alphabet = array_merge([
      '?',
      '(',
      '@',
      ';',
      '$',
      '#',
      ']',
      '&',
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
    
    $s1 = [];
    $s2 = [];
    $dim = self::MATRIX_DIMENSION;
    $maxIndex = ($dim * $dim) - 1;
    
    for ($i = 0; $i < $dim; $i++) {
      for ($j = 0; $j < $dim; $j++) {
        $currentIndex = ($i * $dim) + $j;
        $s1[$i][$j] = (string)$alphabet[$currentIndex];
        $s2[$i][$j] = str_rot13((string)$alphabet[$maxIndex - $currentIndex]);
      }
    }
    
    return [
      $s1,
      $s2
    ];
  }
  
  private static function safeCrypt(string $string, bool $isDecrypt = false):string
  {
    [
      $s1,
      $s2
    ] = self::generateMatrices();
    
    $stringLength = strlen($string);
    $m = (int)floor($stringLength / 2) * 2;
    $hasOddSymbol = ($m !== $stringLength);
    $oddSymbol = $hasOddSymbol ? $string[$stringLength - 1] : '';
    
    $output = [];
    $oddSymbolCoordinates = [];
    
    if ($hasOddSymbol) {
      $oddSymbolCoordinates = self::findCoordinates($isDecrypt ? $s2 : $s1, $oddSymbol);
    }
    
    for ($ii = 0; $ii < $m; $ii += 2) {
      $symbol1 = $string[$ii];
      $symbol2 = $string[$ii + 1];
      
      $resSymbol1 = $symbol1;
      $resSymbol2 = $symbol2;
      
      $a1 = self::findCoordinates($isDecrypt ? $s2 : $s1, $symbol1);
      $a2 = self::findCoordinates($isDecrypt ? $s1 : $s2, $symbol2);
      
      if ($a1 !== null && $a2 !== null) {
        $resSymbol1 = $isDecrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
        $resSymbol2 = $isDecrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
      }
      
      $output[] = $resSymbol1.$resSymbol2;
    }
    
    if ($hasOddSymbol && $oddSymbolCoordinates !== null) {
      $output[] = $isDecrypt ? $s1[$oddSymbolCoordinates[1]][$oddSymbolCoordinates[0]]
        : $s2[$oddSymbolCoordinates[1]][$oddSymbolCoordinates[0]];
    }
    
    return implode('', $output);
  }
}