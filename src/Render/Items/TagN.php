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
 * Название html элемента берётся из названия ветки<br>
 * Обработчик в цикле перебирает все свойства и атрибуты xml ветки<br>
 * crypt - true - шифрует значение поля, иначе выводит значение<br>
 * position - after - Значение выводится после html, before - по-умолчанию<br>
 * target - вывод временной метки в формате 'YmdHis', crypt - шифрует метку<br>
 * xpath - путь к XML данным<br>
 * Default value - Значение по-умолчанию [не обязательный],<br>
 * - если начинается с $ - значение переменной php<br>
 * - если заключено в {} - выражение php<br>
 * /attr - Для разноязычных атрибутов<br>
 * /attr/attrName - Название атрибута<br>
 * /attr/attrName/Lang - Текст языковой версии<br>
 * /children - вложенные HTML
 */
class TagN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
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
      $method = self::getMethodData($methodRef, self::getMethodList($objID));
      
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