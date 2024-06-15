<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Crypt, Library\Enums\Encrypt, Render\Items\Interfaces\HtmlInterface, Render\Render};

class TagN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    $attribute = '';
    $isAfter = 'after' === (string)($xmlItem['position'] ?? '');
    $value = self::getXPathValue($xmlItem, $xmlData);
    
    if (isset($xmlItem['crypt'])) {
      $attribute .= self::cryptValue($xmlItem, $xmlData);
      $value = '';
      
      unset($xmlItem['crypt']);
    }
    
    unset($xmlItem['position'], $xmlItem['xpath']);
    
    if (isset($xmlItem['target'])) {
      $attribute .= ' data-target="'.('crypt' === (string)$xmlItem['target']
          ? Crypt::action((new \DateTime())->format('YmdHis'), Encrypt::CHR) : (new \DateTime())->format('YmdHis')).'"';
      
      unset($xmlItem['target']);
    }
    
    $methodRef = (string)($xmlItem['method'] ?? '');
    unset($xmlItem['method']);
    
    $attribute .= self::getAttribute($xmlItem);
    
    $html = '';
    
    if (isset($xmlItem->children)) {
      foreach ($xmlItem->children->children() as $child) {
        $html .= self::xml2HTML(self::xml2DOM($child), $objID, $xmlData, $variables);
      }
    }
    
    $html = ($isAfter ? $html : $value).self::getValue($xmlItem ?? null).($isAfter ? $value : $html);
    
    if ('' !== $methodRef) {
      $method = self::getMethodData($methodRef, self::getMethodList($objID));
      
      if (1 !== $method->access) {
        
        return '';
      }
      
      $attribute .= $method->attribute;
      
      $namePosition = strtolower($xmlItem['method-name'] ?? '');
      
      if ('' !== $namePosition) {
        unset($xmlItem['method-name']);
        
        $html = 'after' === $namePosition ? $html.$method->name : $method->name.$html;
      }
    }
    
    return "<{$xmlItem->getName()} $attribute>$html</{$xmlItem->getName()}>";
  }
}