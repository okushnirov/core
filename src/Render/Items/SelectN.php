<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\{Items\Interfaces\HtmlInterface, Items\Library\Options, Items\Library\OptionsCount,
  Items\Library\OptionsDict, Items\Library\OptionsSQL, Render
};

/**
 * Class SelectN
 *
 * @sample
 * <select render="SelectN" attributes="" properties="">
 *  <attr>
 *   <attrName>
 *    <uk>Text UK</uk>
 *    <ru>Text RU</ru>
 *   </attrName>
 *  </attr>
 *  <option class="" value="">Text</option>
 *  <source dest="count|dict|sql|none" xpath="путь к значению" type="string|int"/>Default value</source>
 *  1) <count start="start value" end="end value" step="step value">Default value</count>
 *  2) <dict id="2" order="2" parent="1" parentName="Вид" value="ID" name="Название_" lang-postfix="true"
 *   prepare="">Default value</dict>
 *  3) <sql sql="" value="ID" name="Название_" lang-postfix="true" prepare="">Default value</sql>
 *  4) Элементы списка определяются из данных /option
 * </select>
 * @discription
 * Обработчик в цикле перебирает все свойства и атрибуты xml ветки
 * /attr - Для разноязычных атрибутов
 * /attr/attrName - Название атрибута
 * /attr/attrName/Lang - Текст языковой версии
 * /option - Первая опция (1-n при dest = none)
 * class - Класс опции
 * value - Значение опции, если NULL, то пустое значение опции
 * Text - Текстовое значение опции
 * /source - Источник
 * dest - источник данных (счётчик, справочник или запрос)
 * xpath - путь к значению в XMLData
 * f-xpath - путь к структуре поля в XMLData
 * type - тип значения строка (string) или число для точного выбора опции selected
 * Default value - Значение по-умолчанию [не обязательный],
 * - если начинается с $ - значение переменной php
 * - если заключено в {} - выражение php
 * 1) Источник "Счётчик"
 * start - начальное значение
 * end - конечное значение
 * step - шаг итерации
 * 2) Источник "Справочник"
 * id - номер справочника
 * order - порядок сортировки 0 - по-умолчанию, 1 - по ID, 2 - сортировка в поле "Порядок"
 * parent - номер справочника родителя
 * parentName - название поля родителя
 * name - поле названия опции
 * lang-postfix - добавить к названию поля язык
 * prepare - экранирование спецсимволов названия опции
 * 3) Источник "Запрос"
 * sql - запрос
 * name - поле названия опции
 * lang-postfix - добавить к названию поля язык
 * prepare - экранирование спецсимволов названия опции
 */
class SelectN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    # Class
    $class = (string)($xmlItem['class'] ?? '');
    
    # Disabled
    $disabled = self::isTrue($xmlItem['disabled'] ?? (is_object($variables)
      ? ($variables->readonly ?? '') : (is_array($variables) ? ($variables['readonly'] ?? '')
        : '')));
    
    # Attribute
    if ($disabled) {
      $class .= ' no-update';
      $attribute = 'disabled="" tabindex="-1"';
    } else {
      unset($xmlItem['class'], $xmlItem['disabled']);
      
      $attribute = self::getAttribute($xmlItem);
      $attribute = isset($xmlItem->source['xpath']) || isset($xmlItem->source['f-xpath'])
        ? self::getXPathAttribute($xmlItem->source, $xmlData, $attribute) : $attribute;
    }
    
    $attribute = trim('class="'.trim($class).'" '.$attribute);
    
    # Settings
    $type = (string)($xmlItem->source['type'] ?? 'string');
    
    # Value
    $value = isset($xmlItem->source) && isset($xmlItem->source['xpath']) ? self::getXPathValue($xmlItem->source,
      $xmlData) : '';
    $value = '' === $value ? self::getValue($xmlItem->source ?? null) : $value;
    $value = 'string' === $type || '' === $value ? $value : (int)$value;
    
    # Filter
    $filter = trim($xmlItem->filter ?? '');
    
    # Source
    $source = [];
    $isPrepare = false;
    
    switch ($xmlItem->source['dest'] ?? '') {
      case 'count':
        $source = OptionsCount::get($xmlItem->count ?? null);
        
        break;
      
      case 'dict':
        $source = OptionsDict::get($xmlItem->dict ?? null, $objID);
        $isPrepare = static::isTrue($xmlItem->dict['prepare'] ?? '');
        
        break;
      
      case 'sql':
        $source = OptionsSQL::get($xmlItem->sql ?? null);
        $isPrepare = static::isTrue($xmlItem->sql['prepare'] ?? '');
    }
    
    # Option
    $option = isset($xmlItem->option) ? Options::first($xmlItem->option, false, $type, $value) : '';
    
    return "<select $attribute>$option".Options::list($source, $type, $value, $isPrepare, 0, $filter)."</select>";
  }
}