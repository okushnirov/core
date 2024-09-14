<?php

namespace okushnirov\core\Library\Interfaces;

interface Currency
{
  public function getLabel(string $lang):string;
  
  public function getLabelShort(string $lang):string;
  
  public function getSymbol():string;
}