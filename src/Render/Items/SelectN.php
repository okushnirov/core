<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\{Items\Interfaces\HtmlInterface, Items\Options\Options, Items\Options\OptionsCount,
  Items\Options\OptionsDict, Items\Options\OptionsSQL, Render
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
 *  <option class="" value="">Option text</option>
 *  <source dest="count|dict|sql|none" xpath="путь к значению" type="string|int"/>Default value</source>
 *  1) <count start="start value" end="end value" step="step value">Default value</count>
 *  2) <dict id="2" order="2" parent="1" parentName="Вид" value="ID" name="Название_" lang-postfix="true"
 *   prepare="">Default value</dict>
 *  3) <sql sql="" value="ID" name="Название_" lang-postfix="true" prepare="">Default value</sql>
 *  4) Элементы списка определяются из данных /option
 * </select>
 * @discription
 * Обработчик в цикле перебирает все свойства и атрибуты xml ветки select<br>
 * /attr - Контейнер для атрибутов<br>
 * /attr/attrName - Название атрибута<br>
 * /attr/attrName/Lang - Текст языковой версии<br>
 * /option - Первая опция (1-n при dest = none)<br>
 * class - Класс опции<br>
 * value - Значение опции, если NULL, то пустое значение опции<br>
 * Option text - Текстовое значение опции<br>
 * /source - Источник<br>
 * dest - источник данных (счётчик, справочник или запрос)<br>
 * xpath - путь к значению в XMLData<br>
 * f-xpath - путь к структуре поля в XMLData<br>
 * type - тип значения строка (string) или число для точного выбора опции selected<br>
 * Default value - Значение по-умолчанию [не обязательный],<br>
 * - если начинается с $ - значение переменной php<br>
 * - если заключено в {} - выражение php<br>
 * 1) Источник "Счётчик"<br>
 * start - начальное значение<br>
 * end - конечное значение<br>
 * step - шаг итерации<br>
 * 2) Источник "Справочник"<br>
 * id - номер справочника<br>
 * order - порядок сортировки 0 - по-умолчанию, 1 - по ID, 2 - сортировка в поле "Порядок"<br>
 * parent - номер справочника родителя<br>
 * parentName - название поля родителя<br>
 * name - поле названия опции<br>
 * lang-postfix - добавить к названию поля язык<br>
 * prepare - экранирование спецсимволов названия опции<br>
 * 3) Источник "Запрос"<br>
 * sql - запрос<br>
 * name - поле названия опции<br>
 * lang-postfix - добавить к названию поля язык<br>
 * prepare - экранирование спецсимволов названия опции<br>
 */
class SelectN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    $class = (string)($xmlItem['class'] ?? '');
    
    $disabled = self::isTrue($xmlItem['disabled'] ?? (is_object($variables)
      ? ($variables->readonly ?? '') : (is_array($variables) ? ($variables['readonly'] ?? '')
        : '')));
    
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
    
    $type = (string)($xmlItem->source['type'] ?? 'string');
    
    $value = isset($xmlItem->source) && isset($xmlItem->source['xpath']) ? self::getXPathValue($xmlItem->source,
      $xmlData) : '';
    $value = '' === $value ? self::getValue($xmlItem->source ?? null) : $value;
    $value = 'string' === $type || '' === $value ? $value : (int)$value;
    
    $filter = trim($xmlItem->filter ?? '');
    
    $source = [];
    $isPrepare = false;
    
    switch ($xmlItem->source['dest'] ?? '') {
      case 'count':
        $source = OptionsCount::get($xmlItem->count ?? null);
        
        break;
      
      case 'dict':
        $source = OptionsDict::get($xmlItem->dict ?? null, $objID, $xmlData);
        $isPrepare = static::isTrue($xmlItem->dict['prepare'] ?? '');
        
        break;
      
      case 'sql':
        $source = OptionsSQL::get($xmlItem->sql ?? null);
        $isPrepare = static::isTrue($xmlItem->sql['prepare'] ?? '');
    }
    
    $option = isset($xmlItem->option) ? Options::first($xmlItem->option, false, $type, $value) : '';
    
    return "<select $attribute>$option".Options::list($source, $type, $value, $isPrepare, 0, $filter)."</select>";
  }
}