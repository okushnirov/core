<?php

namespace okushnirov\core\Render\Items\Options;

use okushnirov\core\{Library\DbSQLAnywhere, Library\Enums\CookieType, Library\Enums\SQLAnywhere, Library\Lang,
  Library\Str, Render\Render
};

class Options extends Render
{
  private static bool $debug = false;
  
  public static function explode(array $source, string $string, string $separator = ';'):array
  {
    $values = explode($separator, trim($string, $separator));
    $destination = [];
    
    if (self::$debug) {
      trigger_error(__METHOD__." Values:\n".json_encode($values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    
    foreach ($source as $value => $name) {
      $exist = in_array((string)$value, $values, true);
      
      if (self::$debug) {
        trigger_error(__METHOD__." Value[$value] = Name[$name] Isset[$exist]");
      }
      
      if ($exist) {
        $destination[$value] = $name;
        unset($source[$value]);
      }
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__."\nSource:\n".json_encode($source, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ."\nDestination:\n".json_encode($destination, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    
    return [
      'source' => $source,
      'destination' => $destination
    ];
  }
  
  public static function fill(\SimpleXMLElement $xml, string $query):array
  {
    $name = Str::lowerCase((string)($xml['name'] ?? ''));
    $value = Str::lowerCase((string)($xml['value'] ?? ''));
    
    if (empty($name) || empty($value) || empty($query)) {
      
      return [];
    }
    
    $result = DbSQLAnywhere::query($query, SQLAnywhere::FETCH_ALL);
    
    if (empty($result)) {
      
      return [];
    }
    
    if (self::isTrue($xml['lang-postfix'] ?? '')) {
      Lang::set(cookie: CookieType::No);
      $name .= Lang::$settings->language->lang->{Lang::$lang}->short;
    }
    
    foreach ($result as $options) {
      $optValue = (string)($options[$value] ?? '');
      $optName = (string)($options[$name] ?? '');
      
      if (isset($source[$optValue]) || '' === $optValue) {
        
        continue;
      }
      
      $source[$optValue] = $optName;
    }
    
    return $source ?? [];
  }
  
  public static function first(
    \SimpleXMLElement $xml, bool $disabled = false, string $type = 'string', string $default = ''):string
  {
    $html = '';
    
    foreach ($xml as $option) {
      $name = (string)($option->{Lang::$lang} ?? $option ?? '');
      $value = trim($option['value'] ?? '');
      $value = 'NULL' === $value ? '' : ('string' === $type ? $value : (int)$value);
      $attribute = self::getAttribute($option);
      $attribute .= $disabled ? ' style="display:none;"' : '';
      $attribute .= '' !== $default && $value === $default ? ' selected=""' : '';
      
      if (self::$debug) {
        trigger_error(__METHOD__."\noptions value(".gettype($value)
          .")=$value\noptions default($type) $default\ncompare value = default = [".('' !== $default
            && $value === $default)."]");
      }
      
      $html .= "<option $attribute>".(self::isTrue($option['prepare'] ?? '') ? Str::prepare($name) : $name)."</option>";
    }
    
    return $html;
  }
  
  public static function list(
    array  $source, string $type = 'string', int | string $default = '', bool $isPrepare = false, int $order = 0,
    string $filter = ''):string
  {
    if (empty($source)) {
      
      return '';
    }
    
    $html = '';
    
    foreach ($source as $value => $name) {
      $value = 'NULL' === $value ? '' : ('string' === $type ? (string)$value : (int)$value);
      
      if ('' !== $filter && false === mb_strpos($filter, ";$value;")) {
        
        continue;
      }
      
      $selected = '' !== $default && $value === $default ? 'selected=""' : '';
      $attr = $order ? "data-order=\"$order\"" : '';
      $html .= "<option value=\"$value\" $attr $selected>".($isPrepare ? Str::prepare($name) : $name)."</option>";
      
      if (self::$debug) {
        trigger_error(__METHOD__." Value[$value] = Default[$default] Selected[$selected]");
      }
      
      if ($order) {
        $order++;
      }
    }
    
    return $html;
  }
}