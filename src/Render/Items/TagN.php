<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Crypt, Library\Enums\Encrypt, Render\Items\Interfaces\HtmlInterface, Render\Render};

/**
 * Class TagN
 *
 * @sample
 * <tagName render="TagN" attributes="" properties="" crypt="true|false|" position="after|before" target="crypt|"
 *   xpath="">Default value
 *  <uk>Text UA</uk>
 *  <ru>Text RU</ru>
 *  <attr>
 *   <attrName>
 *    <uk>Text UA</uk>
 *    <ru>Text RU</ru>
 *   </attrName>
 *  </attr>
 *  <children></children>
 * </tagName>
 * @discription
 * Название html элемента берётся из названия ветки
 * Обработчик в цикле перебирает все свойства и атрибуты xml ветки
 * /attr - Для разноязычных атрибутов
 * /attr/attrName - Название атрибута
 * /attr/attrName/Lang - Текст языковой версии
 * /children - вложенные HTML
 * @crypt - true - шифрует значение поля, иначе выводит значение
 * @position - after - Значение выводится после html, before - по-умолчанию
 * @target - вывод временной метки в формате 'YmdHis', crypt - шифрует метку
 * @xpath - путь к XML данным
 * Default value - Значение по-умолчанию [не обязательный],
 * - если начинается с $ - значение переменной php
 * - если заключено в {} - выражение php
 */
class TagN extends Render implements HtmlInterface
{
  public static function html(\SimpleXMLElement $xmlItem, $objID = false, $xmlData = false, $variables = false):string
  {
    $attribute = '';
    $isAfter = 'after' === (string)($xmlItem['position'] ?? '');
    $value = isset($xmlItem['position']) && isset($xmlItem['xpath']) ? self::getXPathValue($xmlItem, $xmlData) : '';
    
    # Crypt value
    if (isset($xmlItem['crypt'])) {
      $attribute .= self::cryptValue($xmlItem, $xmlData);
      
      unset($xmlItem['crypt']);
    }
    
    unset($xmlItem['position'], $xmlItem['xpath']);
    
    # Timestamp
    if (isset($xmlItem['target'])) {
      $attribute .= ' data-target="'.('crypt' === (string)$xmlItem['target']
          ? Crypt::action((new \DateTime())->format('YmdHis'), Encrypt::CHR) : (new \DateTime())->format('YmdHis')).'"';
      
      unset($xmlItem['target']);
    }
    
    # Method
    $methodRef = (string)($xmlItem['method'] ?? '');
    unset($xmlItem['method']);
    
    # Attribute
    $attribute .= self::getAttribute($xmlItem);
    
    if ('' !== $methodRef) {
      $method = self::getMethodData($methodRef, self::getMethodList((int)$objID));
      
      if (1 !== $method->access) {
        
        return '';
      }
      
      $attribute .= $method->attribute;
    }
    
    # Html
    $html = '';
    
    # Children html
    if (isset($xmlItem->children)) {
      foreach ($xmlItem->children->children() as $child) {
        $html .= self::xml2HTML(self::xml2DOM($child), $objID, $xmlData, $variables);
      }
    }
    
    $html = ($isAfter ? $html : $value).self::getValue($xmlItem ?? null).($isAfter ? $value : $html);
    
    return "<{$xmlItem->getName()} $attribute>$html</{$xmlItem->getName()}>";
  }
}