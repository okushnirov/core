<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\Currency;

enum CurrencyStr: string implements Currency
{
  case UAH = 'UAH';
  case USD = 'USD';
  case EUR = 'EUR';
  
  public function getCode():int
  {
    
    return match ($this) {
      CurrencyStr::USD => CurrencyInt::USD->value,
      CurrencyStr::EUR => CurrencyInt::EUR->value,
      default => CurrencyInt::UAH->value
    };
  }
  
  public function getLabel(string $lang = 'uk'):string
  {
    
    return match ($this) {
      CurrencyStr::USD => 'ru' === $lang ? 'доллар США' : 'долар США',
      CurrencyStr::EUR => 'ru' === $lang ? 'евро' : 'євро',
      default => 'ru' === $lang ? 'гривна' : 'гривня'
    };
  }
  
  public function getLabelShort(string $lang = 'uk'):string
  {
    
    return match ($this) {
      CurrencyStr::USD => 'дол. США',
      CurrencyStr::EUR => 'ru' === $lang ? 'евро' : 'євро',
      CurrencyStr::UAH => 'грн'
    };
  }
  
  public function getSymbol():string
  {
    
    return match ($this) {
      CurrencyStr::USD => '$',
      CurrencyStr::EUR => '€',
      default => '₴'
    };
  }
}