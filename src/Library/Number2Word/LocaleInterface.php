<?php

namespace okushnirov\core\Library\Number2Word;

interface LocaleInterface
{
  public function getCentsDeclensions(string $currency):array;
  
  public function getCurrencyDeclensions(string $currency):array;
  
  public function getFeminineUnits():array;
  
  public function getHundreds():array;
  
  public function getTeens():array;
  
  public function getTens():array;
  
  public function getTriads():array;
  
  public function getUnits():array;
  
  public function getZero():string;
  
  public function isFeminine(int $triadIndex, string $currency, bool $isCents = false):bool;
}