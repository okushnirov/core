<?php

namespace okushnirov\core\Library\Number2Word;

class LocaleRussian implements LocaleInterface
{
  public function getCurrencyDeclensions(string $currency):array
  {
    return match ($currency) {
      'UAH' => [
        'гривен',
        'гривна',
        'гривны'
      ],
      'USD' => [
        'долларов США',
        'доллар США',
        'доллара США'
      ],
      'EUR' => [
        'евро',
        'евро',
        'евро'
      ],
      default => [
        '',
        '',
        ''
      ],
    };
  }
  
  public function getCentsDeclensions(string $currency):array
  {
    return match ($currency) {
      'UAH' => [
        'копеек',
        'копейка',
        'копейки'
      ],
      'USD', 'EUR' => [
        'центов',
        'цент',
        'цента'
      ],
      default => [
        '',
        '',
        ''
      ],
    };
  }
  
  public function getFeminineUnits():array
  {
    return [
      '',
      'одна',
      'две'
    ];
  }
  
  public function getHundreds():array
  {
    return [
      '',
      'сто',
      'двести',
      'триста',
      'четыреста',
      'пятьсот',
      'шестьсот',
      'семьсот',
      'восемьсот',
      'девятьсот'
    ];
  }
  
  public function getTeens():array
  {
    return [
      'десять',
      'одиннадцать',
      'двенадцать',
      'тринадцать',
      'четырнадцать',
      'пятнадцать',
      'шестнадцать',
      'семнадцать',
      'восемнадцать',
      'девятнадцать'
    ];
  }
  
  public function getTens():array
  {
    return [
      '',
      '',
      'двадцать',
      'тридцать',
      'сорок',
      'пятьдесят',
      'шестьдесят',
      'семьдесят',
      'восемьдесят',
      'девяносто'
    ];
  }
  
  public function getTriads():array
  {
    return [
      [
        '...ллион',
        '',
        'а',
        'ов'
      ],
      [
        'тысяч',
        'а',
        'и',
        ''
      ],
      [
        'миллион',
        '',
        'а',
        'ов'
      ],
      [
        'миллиард',
        '',
        'а',
        'ов'
      ],
      [
        'триллион',
        '',
        'а',
        'ов'
      ],
      [
        'квадриллион',
        '',
        'а',
        'ов'
      ],
      [
        'квинтиллион',
        '',
        'а',
        'ов'
      ],
    ];
  }
  
  public function getUnits():array
  {
    return [
      '',
      'один',
      'два',
      'три',
      'четыре',
      'пять',
      'шесть',
      'семь',
      'восемь',
      'девять'
    ];
  }
  
  public function getZero():string
  {
    return 'ноль';
  }
  
  public function isFeminine(int $triadIndex, string $currency, bool $isCents = false):bool
  {
    # 1. Дробова частина (копійки / центи)
    if ($isCents) {
      # Копійки — жіночий рід (одна копійка), центи — чоловічий (один цент)
      return match ($currency) {
        'UAH' => true,
        default => false
      };
    }
    
    # 2. Тріада тисяч (1,000) — завжди жіночий рід ("тисяча" - ж.р.)
    if ($triadIndex === 1) {
      return true;
    }
    
    # 3. Основна одиниця (гривні, долари...) при $triadIndex === 0
    if ($triadIndex === 0) {
      return match ($currency) {
        'UAH' => true, # Гривня — ж.р.
        default => false # Долар, Євро — ч.р. / ср.р.
      };
    }
    
    return false;
  }
}