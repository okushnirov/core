<?php

namespace okushnirov\core\Render\Items\Library;

class OptionsSQL
{
  public static function get(?\SimpleXMLElement $xml):array
  {
    $query = (string)($xml['sql'] ?? '');
    
    if (empty($query)) {
      
      return [];
    }
    
    $code = "\$SQL = \"$query\";";
    $SQL = '';
    
    eval($code);
    
    //trigger_error(__METHOD__."\nPrepare\n$code\nResult\n$SQL");
    
    return (new Options())::fill($xml, $SQL);
  }
}