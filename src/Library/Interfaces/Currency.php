<?php

namespace okushnirov\core\Library\Interfaces;

interface Currency
{
  public function getLabel(string $lang = 'uk'):string;
  
  public function getLabelShort(string $lang = 'uk'):string;
  
  public function getSymbol():string;
}