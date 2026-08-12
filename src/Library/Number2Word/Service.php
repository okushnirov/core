<?php

namespace okushnirov\core\Library\Number2Word;

readonly class Service
{
  public function __construct(private LocaleInterface $locale = new LocaleUkrainian())
  {
  }
  
  public function convert(
    float | int | string $number, string $currency = 'UAH', bool $includeDecimals = true, bool $decimalsAsWords = false,
    TextCase             $textCase = TextCase::ORIGINAL):string
  {
    $cleanNumber = $this->parseNumber($number);
    $formatted = sprintf($includeDecimals ? '%.2f' : '%.0f', $cleanNumber);
    
    $parts = explode('.', $formatted);
    $amount = (int)$parts[0];
    $cents = isset($parts[1]) ? (int)$parts[1] : 0;
    
    # Основне число
    $result = 0 === $amount ? $this->locale->getZero() : $this->numberToWords((string)$amount, $currency);
    
    # Назва валюти
    if ('-1' !== $currency && !empty($result)) {
      $currencyWord = $this->declineWord($amount, $this->locale->getCurrencyDeclensions($currency));
      
      if ('' !== $currencyWord) {
        $result .= ' '.$currencyWord;
      }
    }
    
    # Копійки / центи
    if ($includeDecimals) {
      $centsWords = $decimalsAsWords ? (0 === $cents
        ? $this->locale->getZero()
        : $this->numberToWords(sprintf('%02d', $cents), $currency, true)) : sprintf('%02d', $cents);
      
      $centsDeclension = $this->declineWord($cents, $this->locale->getCentsDeclensions($currency));
      $result .= ' '.$centsWords.($centsDeclension !== '' ? ' '.$centsDeclension : '');
    }
    
    return $this->applyCaseTransform(trim($result), $textCase);
  }
  
  private function parseNumber(float | int | string $number):float
  {
    if (is_string($number)) {
      $number = str_replace([
        ' ',
        ','
      ], [
        '',
        '.'
      ], $number);
    }
    
    return abs((float)$number);
  }
  
  private function numberToWords(string $number, string $currency, bool $isCents = false):string
  {
    $padded = str_pad($number, (int)(ceil(strlen($number) / 3) * 3), '0', STR_PAD_LEFT);
    $triads = array_reverse(str_split($padded, 3));
    $result = [];
    
    foreach ($triads as $triadIndex => $triadValue) {
      $value = (int)$triadValue;
      
      if (0 === $value && 0 < $triadIndex) {
        continue;
      }
      
      $digits = array_map('intval', str_split($triadValue));
      $triadWords = [];
      
      # Сотні
      if (0 < $digits[0]) {
        $triadWords[] = $this->locale->getHundreds()[$digits[0]];
      }
      
      # Десятки та одиниці
      if (1 === $digits[1]) {
        $triadWords[] = $this->locale->getTeens()[$digits[2]];
      } else {
        if (1 < $digits[1]) {
          $triadWords[] = $this->locale->getTens()[$digits[1]];
        }
        
        if (0 < $digits[2]) {
          $useFeminine = $this->locale->isFeminine($triadIndex, $currency, $isCents);
          
          if ($useFeminine && 2 >= $digits[2]) {
            $triadWords[] = $this->locale->getFeminineUnits()[$digits[2]];
          } else {
            $triadWords[] = $this->locale->getUnits()[$digits[2]];
          }
        }
      }
      
      # Назва розряду (тисячі, мільйони тощо)
      $triadDefs = $this->locale->getTriads();
      $currentTriadDef = $triadDefs[$triadIndex] ?? $triadDefs[0];
      
      if (0 < $value && 0 < $triadIndex) {
        $suffixIndex = match (true) {
          1 === preg_match('/^1$|^\d*[0,2-9]1$/', (string)$value) => 1,
          1 === preg_match('/^[2-4]$|\d*[0,2-9][2-4]$/', (string)$value) => 2,
          default => 3
        };
        
        $triadWords[] = $currentTriadDef[0].$currentTriadDef[$suffixIndex];
      }
      
      if (!empty($triadWords)) {
        $result[$triadIndex] = implode(' ', $triadWords);
      }
    }
    
    return implode(' ', array_reverse($result));
  }
  
  private function declineWord(int $number, array $words):string
  {
    if (empty($words)) {
      return '';
    }
    
    $mod100 = $number % 100;
    $mod10 = 19 < $mod100 ? $mod100 % 10 : $mod100;
    
    return match ($mod10) {
      1 => $words[1] ?? '',
      2, 3, 4 => $words[2] ?? '',
      default => $words[0] ?? '',
    };
  }
  
  private function applyCaseTransform(string $text, TextCase $case):string
  {
    return match ($case) {
      TextCase::UPPERCASE => mb_strtoupper($text, 'UTF-8'),
      TextCase::CAPITALIZE => mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($text, 1, null,
          'UTF-8'),
      TextCase::ORIGINAL => $text,
    };
  }
}