<?php

namespace okushnirov\core\Render\Items\Options;

class OptionsCount
{
  public static function get(?\SimpleXMLElement $xml):array
  {
    if (empty($xml)) {
      
      return [];
    }
    
    $start = (int)($xml['start'] ?? 0);
    $end = (int)($xml['end'] ?? 0);
    $step = (int)($xml['step'] ?? 1);
    
    for ($c = $start; $c <= $end; $c = $c + $step) {
      $source[$c] = $c;
    }
    
    return $source ?? [];
  }
}