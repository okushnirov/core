<?php

namespace okushnirov\core\Library\Number2Word;

class LocaleUkrainian implements LocaleInterface
{
  
  public function getCentsDeclensions(string $currency):array
  {
    return match ($currency) {
      'UAH' => [
        'копійок',
        'копійка',
        'копійки'
      ],
      'USD', 'EUR' => [
        'центів',
        'цент',
        'центи'
      ],
      default => [
        '',
        '',
        ''
      ],
    };
  }
  
  public function getCurrencyDeclensions(string $currency):array
  {
    return match ($currency) {
      'UAH' => [
        'гривень',
        'гривня',
        'гривні'
      ],
      'USD' => [
        'доларів США',
        'долар США',
        'долари США'
      ],
      'EUR' => [
        'євро',
        'євро',
        'євро'
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
      'дві'
    ];
  }
  
  public function getTeens():array
  {
    return [
      'десять',
      'одинадцять',
      'дванадцять',
      'тринадцять',
      'чотирнадцять',
      "п'ятнадцять",
      'шістнадцять',
      'сімнадцять',
      'вісімнадцять',
      "дев'ятнадцять"
    ];
  }
  
  public function getHundreds():array
  {
    return [
      '',
      'сто',
      'двісті',
      'триста',
      'чотириста',
      "п'ятсот",
      'шістсот',
      'сімсот',
      'вісімсот',
      "дев'ятсот"
    ];
  }
  
  public function getTens():array
  {
    return [
      '',
      '',
      'двадцять',
      'тридцять',
      'сорок',
      "п'ятдесят",
      'шістдесят',
      'сімдесят',
      'вісімдесят',
      "дев'яносто"
    ];
  }
  
  public function getTriads():array
  {
    return [
      [
        '...льйон',
        '',
        'а',
        'ів'
      ],
      [
        'тисяч',
        'а',
        'і',
        ''
      ],
      [
        'мільйон',
        '',
        'и',
        'ів'
      ],
      [
        'мільярд',
        '',
        'и',
        'ів'
      ],
      [
        'трильйон',
        '',
        'и',
        'ів'
      ],
      [
        'квадрильйон',
        '',
        'и',
        'ів'
      ],
      [
        'квинтильйон',
        '',
        'и',
        'ів'
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
      'чотири',
      "п'ять",
      'шість',
      'сім',
      'вісім',
      "дев'ять"
    ];
  }
  
  public function getZero():string
  {
    return 'нуль';
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