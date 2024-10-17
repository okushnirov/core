<?php

namespace okushnirov\core\Render\Items\Options;

use okushnirov\core\{Library\DbSQLAnywhere, Library\Enums\SQLAnywhere, Library\Lang, Library\Str, Render\Render};

class Options extends Render
{
  private static bool $debug = false;
  
  public static function explode(array $source, string $string, string $separator = ';'):array
  {
    $values = explode($separator, trim($string, $separator));
    $destination = [];
    
    foreach ($values as $value) {
      if (isset($source[$value])) {
        $destination[$value] = $source[$value];
        unset($source[$value]);
      }
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__."\nValues:\n".json_encode($values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ."\nSource:\n".json_encode($source, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\nDestination:\n"
        .json_encode($destination, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
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
      $name .= Lang::getShort(Lang::$lang);
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
    \SimpleXMLElement $xml, bool $disabled = false, string $type = 'string', int | string $default = '',
    string            $fieldName = ''):string
  {
    if ('' === $default && '' !== $fieldName) {
      self::$prevValues[$fieldName] = '' === (self::$prevValues[$fieldName] ?? '') ? ('string' === $type
        ? (string)($xml->option[0]['value'] ?? '') : (int)($xml->option[0]['value'] ?? ''))
        : self::$prevValues[$fieldName];
    }
    
    $html = '';
    
    foreach ($xml as $option) {
      $name = (string)($option->{Lang::$lang} ?? $option ?? '');
      $value = trim($option['value'] ?? '');
      $value = 'NULL' === $value ? '' : ('string' === $type ? $value : (int)$value);
      $attribute = self::getAttribute($option);
      $attribute .= $disabled ? ' style="display:none;"' : '';
      
      if ('' !== $default && $value === $default) {
        $attribute .= ' selected=""';
        
        if ('' !== $fieldName) {
          self::$prevValues[$fieldName] = 'string' === $type ? (string)$value : (int)$value;
        }
      }
      
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
    string $filter = '', string $fieldName = ''):string
  {
    if (empty($source)) {
      
      return '';
    }
    
    if ('' === $default && '' !== $fieldName) {
      self::$prevValues[$fieldName] = '' === (self::$prevValues[$fieldName] ?? '') ? ('string' === $type
        ? (string)key($source) : (int)key($source)) : self::$prevValues[$fieldName];
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
      
      if ('' !== $selected && '' !== $fieldName) {
        self::$prevValues[$fieldName] = $value;
      }
      
      if (self::$debug) {
        trigger_error(__METHOD__." Value[$value] = Default[$default] Selected[$selected]", E_USER_ERROR);
      }
      
      if ($order) {
        $order++;
      }
    }
    
    return $html;
  }
}