<?php

namespace okushnirov\core\Render;

use okushnirov\core\Library\{Crypt, DbSQLAnywhere, Enums\Encrypt, Enums\HeaderXML, Enums\SQLAnywhere, File, Lang, Str,
  User
};

class Render
{
  private static string $_root = __NAMESPACE__.'\\Items\\';
  
  public static function getMethodData(
    string $methodName, \SimpleXMLElement | bool | array $methodData = false):\stdClass
  {
    $data = new \stdClass();
    $methods = is_array($methodData) ? $methodData : (is_bool($methodData) ? self::getMethodList() : []);
    
    if (is_object($methodData)) {
      foreach ($methodData->method as $method) {
        $ref = trim($method['Идентификатор'] ?? '');
        
        if ('' === $ref) {
          
          continue;
        }
        
        foreach ($method->attributes() as $key => $value) {
          $methods[$ref][Str::lowerCase($key)] = (string)$value;
        }
      }
    }
    
    $data->access = empty($methodName) || empty($methods)
      ? -1 : (array_key_exists($methodName, $methods)
        ? (int)$methods[$methodName]['доступен'] : -2);
    
    if (1 === $data->access) {
      $data->name = Str::prepare($methods[$methodName]['название'] ??
        $methods[$methodName]['название_'.Lang::$lang] ?? '');
      
      $data->attribute = ' data-method-ref="'.$methodName.'"';
      $data->attribute .= ' data-method-state="'.(int)(1 === $data->access).'"';
      
      $data->request = !empty($methods[$methodName]['подтверждение']);
      $data->reset = !empty($methods[$methodName]['перезагрузка']);
    }
    
    return $data;
  }
  
  public static function getMethodList(int $objID = 0):array
  {
    $SQL = "CALL \"dbo\".\"_метод_список\"(".(0 < $objID ? $objID : 'null').')';
    $methods = DbSQLAnywhere::query($SQL, SQLAnywhere::FETCH_ALL, false, User::$login, User::$pass, 'идентификатор');
    
    return empty($methods) ? [] : $methods;
  }
  
  public static function getXMLData(int $objType, int $objID, string $table):\SimpleXMLElement
  {
    DbSQLAnywhere::connect(false, User::$login, User::$pass);
    $tableEscape = DbSQLAnywhere::escape($table, true);
    
    $SQL = "SELECT \"dbo\".\"_объект_структура_".(0 < $objID ? "значения_xml\"($tableEscape,'ID=$objID')"
        : "xml\"($objType,$tableEscape)");
    $result = DbSQLAnywhere::query($SQL, SQLAnywhere::COLUMN);
    
    return new \SimpleXMLElement($result);
  }
  
  public static function getXMLObject(int $objID, int $withChildren = 0):\SimpleXMLElement | bool
  {
    if (0 >= $objID) {
      
      return false;
    }
    
    $SQL = "SELECT \"dbo\".\"_объект_показать_xml\"($objID,1,$withChildren)";
    $result = DbSQLAnywhere::query($SQL, SQLAnywhere::COLUMN);
    
    try {
      $xml = new \SimpleXMLElement($result);
    } catch (\Exception) {
      $xml = false;
    }
    
    return $xml;
  }
  
  public static function xml2DOM(\SimpleXMLElement $xml):\DOMDocument
  {
    $dom = new \DOMDocument();
    
    try {
      $dom->loadXML($xml->saveXML());
    } catch (\Exception $e) {
      trigger_error($e->getMessage());
    }
    
    return $dom;
  }
  
  public static function xml2HTML(
    \DOMDocument $dom, int $objID = 0, \SimpleXMLElement | bool $xmlData = false, mixed $variables = false):string
  {
    $xpath = new \DOMXPath($dom);
    
    foreach ($xpath->query('//comment()') as $comment) {
      $comment->parentNode->removeChild($comment);
    }
    
    foreach ($xpath->query('//*[@render]') as $node) {
      if ($node->nodeType !== XML_ELEMENT_NODE) {
        
        continue;
      }
      
      $render = (string)($node->getAttribute('render') ?? '');
      
      if (!$render) {
        
        continue;
      }
      
      $classname = static::$_root.$render;
      $html = '';
      
      try {
        if (!class_exists($classname) || !method_exists($classname, 'html')) {
          throw new \Exception("Class $classname or public static method 'html' not found");
        }
        
        $xmlItem = simplexml_import_dom($node);
        unset($xmlItem['render']);
        
        $html = (new $classname())::html($xmlItem, $objID, $xmlData, $variables);
      } catch (\Exception $e) {
        trigger_error($e->getMessage());
      }
      
      $fragment = $dom->createDocumentFragment();
      $fragment->appendXML($html);
      $node->parentNode->replaceChild($fragment, $node);
    }
    
    return preg_replace('/>\s+</', '><', str_ireplace([
      '<?xml version="1.0"?>',
      HeaderXML::UTF->value,
      '<items>',
      '</items>',
      "\n"
    ], '', $dom->saveXML(null, LIBXML_NOEMPTYTAG)));
  }
  
  public static function xmlFile2HTML(
    string $file, int $objID = 0, \SimpleXMLElement | bool $xmlData = false, mixed $variables = false):string
  {
    if (!File::isFile($file)) {
      
      return '';
    }
    
    $dom = new \DOMDocument();
    
    try {
      $dom->load($_SERVER['DOCUMENT_ROOT'].$file);
    } catch (\Exception $e) {
      trigger_error($e->getMessage());
    }
    
    return empty($dom) ? '' : static::xml2HTML($dom, $objID, $xmlData, $variables);
  }
  
  protected static function cryptValue(\SimpleXMLElement $xml, \SimpleXMLElement | bool $xmlData = false):string
  {
    if (empty($xmlData)) {
      
      return '';
    }
    
    $isCrypt = self::isTrue($xml['crypt']);
    
    $value = self::getXPathValue($xml, $xmlData);
    
    return 'data-crypt-id="'.($isCrypt ? Crypt::action($value, Encrypt::CHR) : $value).'"';
  }
  
  protected static function getAttribute(?\SimpleXMLElement $xml):string
  {
    if (empty($xml)) {
      
      return '';
    }
    
    $html = '';
    
    foreach ($xml->attributes() as $key => $value) {
      $html .= " $key=\"$value\"";
    }
    
    if (isset($xml->attr)) {
      foreach ($xml->attr->children() as $key => $value) {
        $isPrepare = static::isTrue($value['prepare'] ?? '');
        $attrValue = (string)($value->{Lang::$lang} ?? $value ?? '');
        
        $html .= " $key=\"".($isPrepare ? Str::prepare($attrValue) : $attrValue)."\"";
      }
    }
    
    return $html;
  }
  
  protected static function getValue(?\SimpleXMLElement $xml):string
  {
    if (empty($xml)) {
      
      return '';
    }
    
    $isPrepare = static::isTrue($xml['prepare'] ?? '');
    $value = trim($xml->{Lang::$lang} ?? $xml);
    
    if (str_starts_with($value, '$') || str_starts_with($value, '{') && str_ends_with($value, '}')) {
      eval("\$value = ".trim($value, '{}').";");
    }
    
    return $isPrepare ? Str::prepare($value) : $value;
  }
  
  protected static function getXPathAttribute(
    ?\SimpleXMLElement $xml, \SimpleXMLElement | bool $xmlData = false, string $attribute = ''):string
  {
    if (!isset($xml['f-xpath']) && !$xmlData) {
      
      return '';
    }
    
    $f = [];
    
    if (isset($xml['f-xpath'])) {
      $f = $xmlData->xpath($xml['f-xpath']) ?? $f;
      
      if (!empty($f)) {
        
        goto skip;
      }
    }
    
    if (isset($xml['xpath'])) {
      $f = $xmlData->xpath($xml['xpath']) ?? $f;
    }
    
    skip:
    
    if (false === mb_stripos($attribute, 'name="')) {
      $fieldID = (int)($f[0]['id'] ?? 0);
      $attribute .= 0 < $fieldID ? ' name="'.Crypt::action($fieldID, Encrypt::CHR).'"' : '';
    }
    
    $fieldRequired = 'y' === strtolower($f[0]['required'] ?? '');
    
    $fieldType = trim($f[0]['type'] ?? '');
    $fieldWidth = (int)($f[0]['width'] ?? 0);
    
    switch ($fieldType) {
      # DOUBLE, NUMERIC
      case mb_stristr($fieldType, 'numeric'):
      case 'double' :
        $attribute .= false === mb_stripos($attribute, 'data-numeric="') ? " data-numeric=\"$fieldWidth\"" : '';
        $attribute .= false === mb_stripos($attribute, 'data-decimal="') ? ' data-decimal="'.trim($f[0]['scale'] ?? 0)
          .'"' : '';
        
        break;
      
      # DATE
      case 'date':
        $attribute .= false === mb_stripos($attribute, 'maxlength="') ? ' maxlength="10"' : '';
        $attribute .= $fieldRequired && false === mb_stripos($attribute, 'minlength="') ? ' minlength="10"' : '';
        
        break;
      
      # ...CHAR
      case (mb_stristr($fieldType, 'char') ? $fieldType : ''):
        $attribute .= false === mb_stripos($attribute, 'maxlength="') && !empty($fieldWidth)
          ? " maxlength=\"$fieldWidth\"" : '';
    }
    
    return $attribute;
  }
  
  protected static function getXPathValue(?\SimpleXMLElement $xml, \SimpleXMLElement | bool $xmlData = false):string
  {
    if (!isset($xml['xpath']) || !$xmlData) {
      
      return '';
    }
    
    $isPrepare = self::isTrue($xml['prepare'] ?? '');
    $isLang = self::isTrue($xml['lang'] ?? '');
    $array = $xmlData->xpath($isLang ? mb_ereg_replace('\$lang', Lang::$lang, $xml['xpath']) : $xml['xpath']) ?? [];
    $value = (string)($array[0] ?? '');
    
    # Fix numeric leading zero
    $value = false === mb_stripos($array[0]['type'] ?? '', 'numeric') || '' === $value ? $value : (float)$value;
    
    return $isPrepare ? Str::prepare($value) : $value;
  }
  
  protected static function isTrue(?string $prepare = ''):bool
  {
    
    return filter_var($prepare, FILTER_VALIDATE_BOOLEAN);
  }
}