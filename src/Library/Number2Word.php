<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\Charset;
use okushnirov\core\Library\Number2Word\{Language, LocaleRussian, LocaleUkrainian, Service, TextCase};

class Number2Word
{
  private array $request;
  
  public function __construct(array $request = [])
  {
    $this->request = $request;
  }
  
  public function run():string
  {
    $charsetRaw = $this->request['cp'] ?? Charset::UTF8->value;
    $charset = Charset::tryFrom(mb_strtoupper((string)$charsetRaw)) ?? Charset::UTF8;
    
    header("Content-Type: text/plain; charset=".$charset->value);
    http_response_code(200);
    
    $number = $this->request['num'] ?? null;
    
    if (is_null($number) || !is_numeric(str_replace(',', '.', (string)$number))) {
      return '';
    }
    
    $lang = Language::tryFromOrDefault($this->request['lang'] ?? null);
    
    $locale = match ($lang) {
      Language::RU => new LocaleRussian(),
      Language::UK => new LocaleUkrainian()
    };
    
    $service = new Service($locale);
    
    return $service->convert(number: $number, currency: mb_strtoupper((string)($this->request['cur'] ?? 'UAH')),
      includeDecimals: (bool)($this->request['dec'] ?? true), decimalsAsWords: (bool)($this->request['dec2word']
        ?? false), textCase: TextCase::tryFrom((int)($this->request['reg'] ?? 0)) ?? TextCase::ORIGINAL);
  }
}