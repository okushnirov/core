<?php

namespace okushnirov\core\Render\Items\Options;

use okushnirov\core\Render\Items\Dict\Dict;

class OptionsDict
{
  public static function get(?\SimpleXMLElement $xml, int $objID = 0, \SimpleXMLElement | bool $xmlData = false):array
  {
    $query = Dict::getQuery($xml, $objID, $xmlData);
    
    return '' === $query ? [] : (new Options())::fill($xml, $query);
  }
}