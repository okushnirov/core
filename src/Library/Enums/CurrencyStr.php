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
      CurrencyStr::UAH => CurrencyInt::UAH->value,
      CurrencyStr::USD => CurrencyInt::USD->value,
      CurrencyStr::EUR => CurrencyInt::EUR->value
    };
  }
  
  public function getLabel(string $lang):string
  {
    
    return match ($this) {
      CurrencyStr::UAH => 'ru' === $lang ? 'гривна' : 'гривня',
      CurrencyStr::USD => 'ru' === $lang ? 'доллар США' : 'долар США',
      CurrencyStr::EUR => 'ru' === $lang ? 'евро' : 'євро'
    };
  }
  
  public function getLabelShort(string $lang):string
  {
    
    return match ($this) {
      CurrencyStr::UAH => 'грн',
      CurrencyStr::USD => 'дол. США',
      CurrencyStr::EUR => 'ru' === $lang ? 'евро' : 'євро'
    };
  }
  
  public function getSymbol():string
  {
    
    return match ($this) {
      CurrencyStr::UAH => '₴',
      CurrencyStr::USD => '$',
      CurrencyStr::EUR => '€'
    };
  }
}