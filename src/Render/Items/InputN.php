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
 * Если элемент только для чтения, то будет div, иначе название html элемента берётся из названия ветки (input/textarea)<br>
 * Обработчик в цикле перебирает все свойства и атрибуты xml ветки<br>
 * /value - Значения<br>
 * xpath - путь к значению в XMLData<br>
 * f-xpath - путь к структуре поля в XMLData<br>
 * lang - языковая локализация поля (если true - замена $lang на язык)<br>
 * Default value - Значение по-умолчанию [не обязательный],<br>
 * - если начинается с $ - значение переменной php<br>
 * - если заключено в {} - выражение php<br>
 * /attr - Контейнер для атрибутов<br>
 * /attr/attrName - Название атрибута<br>
 * /attr/attrName/Lang - Текст языковой версии<br>
 */
class InputN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    $class = (string)($xmlItem['class'] ?? '');
    
    $readonly = self::isTrue($xmlItem['readonly'] ?? (is_object($variables)
      ? ($variables->readonly ?? '') : (is_array($variables) ? ($variables['readonly'] ?? '')
        : '')));
    
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
    
    $attribute = trim('class="'.trim($class).'" '.$attribute);
    
    $value = isset($xmlItem->value) && isset($xmlItem->value['xpath']) ? self::getXPathValue($xmlItem->value, $xmlData)
      : '';
    $value = '' === $value ? self::getValue($xmlItem->value ?? null) : $value;
    
    return 'input' === $tagName ? "<input $attribute value=\"$value\"/>" : "<$tagName $attribute>$value</$tagName>";
  }
}