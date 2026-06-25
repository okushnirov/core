<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\Currency;

enum CurrencyInt: int implements Currency
{
  case UAH = 980;
  case USD = 840;
  case EUR = 978;
  
  public function getName():string
  {
    
    return match ($this) {
      CurrencyInt::USD => CurrencyStr::USD->value,
      CurrencyInt::EUR => CurrencyStr::EUR->value,
      default => CurrencyStr::UAH->value
    };
  }
  
  public function getLabel(string $lang = 'uk'):string
  {
    
    return match ($this) {
      CurrencyInt::USD => 'ru' === $lang ? 'доллар США' : 'долар США',
      CurrencyInt::EUR => 'ru' === $lang ? 'евро' : 'євро',
      default => 'ru' === $lang ? 'гривна' : 'гривня'
    };
  }
  
  public function getLabelShort(string $lang = 'uk'):string
  {
    
    return match ($this) {
      CurrencyInt::USD => 'дол. США',
      CurrencyInt::EUR => 'ru' === $lang ? 'евро' : 'євро',
      default => 'грн'
    };
  }
  
  public function getSymbol():string
  {
    
    return match ($this) {
      CurrencyInt::USD => '$',
      CurrencyInt::EUR => '€',
      default => '₴'
    };
  }
}