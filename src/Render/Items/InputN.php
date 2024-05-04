<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\{Items\Interfaces\HtmlInterface, Render};

/**
 * Class InputN
 *
 * @sample
 * <tagName render="InputN" attributes="" properties="">
 *  <value xpath="путь к значению" f-xpath="путь к структуре поля" lang="true"/>Default value</value>
 *  <attr>
 *   <attrName>
 *    <uk>Text UA</uk>
 *    <ru>Text RU</ru>
 *   </attrName>
 *  </attr>
 * </tagName>
 * @discription
 * Если элемент только для чтения, то будет div, иначе название html
 * элемента берётся из названия ветки (input/textarea)
 * Обработчик в цикле перебирает все свойства и атрибуты xml ветки
 * /value - Значения
 * xpath - путь к значению в XMLData
 * f-xpath - путь к структуре поля в XMLData
 * lang - языковая локализация поля (если true - замена $lang на язык)
 * Default value - Значение по-умолчанию [не обязательный],
 * - если начинается с $ - значение переменной php
 * - если заключено в {} - выражение php
 * /attr - Для разноязычных атрибутов
 * /attr/attrName - Название атрибута
 * /attr/attrName/Lang - Текст языковой версии
 */
class InputN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    # Class
    $class = (string)($xmlItem['class'] ?? '');
    
    # Readonly
    $readonly = self::isTrue($xmlItem['readonly'] ?? (is_object($variables)
      ? ($variables->readonly ?? '') : (is_array($variables) ? ($variables['readonly'] ?? '')
        : '')));
    
    # Settings
    if ($readonly) {
      $tagName = 'div';
      $class .= ' no-update';
      $attribute = 'readonly="" tabindex="-1"';
    } else {
      unset($xmlItem['class'], $xmlItem['readonly']);
      
      $tagName = $xmlItem->getName();
      
      $attribute = self::getAttribute($xmlItem);
      $attribute = isset($xmlItem->value['xpath']) || isset($xmlItem->value['f-xpath'])
        ? self::getXPathAttribute($xmlItem->value, $xmlData, $attribute) : $attribute;
    }
    
    # Attribute
    $attribute = trim('class="'.trim($class).'" '.$attribute);
    
    # Value
    $value = isset($xmlItem->value) && isset($xmlItem->value['xpath']) ? self::getXPathValue($xmlItem->value, $xmlData)
      : '';
    $value = '' === $value ? self::getValue($xmlItem->value ?? null) : $value;
    
    return 'input' === $tagName ? "<input $attribute value=\"$value\"/>" : "<$tagName $attribute>$value</$tagName>";
  }
}