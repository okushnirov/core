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
      CurrencyInt::UAH => CurrencyStr::UAH->value,
      CurrencyInt::USD => CurrencyStr::USD->value,
      CurrencyInt::EUR => CurrencyStr::EUR->value
    };
  }
  
  public function getLabel(string $lang):string
  {
    
    return match ($this) {
      CurrencyInt::UAH => 'ru' === $lang ? 'гривна' : 'гривня',
      CurrencyInt::USD => 'ru' === $lang ? 'доллар США' : 'долар США',
      CurrencyInt::EUR => 'ru' === $lang ? 'евро' : 'євро'
    };
  }
  
  public function getLabelShort(string $lang):string
  {
    
    return match ($this) {
      CurrencyInt::UAH => 'грн',
      CurrencyInt::USD => 'дол. США',
      CurrencyInt::EUR => 'ru' === $lang ? 'евро' : 'євро'
    };
  }
  
  public function getSymbol():string
  {
    
    return match ($this) {
      CurrencyInt::UAH => '₴',
      CurrencyInt::USD => '$',
      CurrencyInt::EUR => '€'
    };
  }
}