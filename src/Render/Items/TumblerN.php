<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\Items\Interfaces\HtmlInterface;
use okushnirov\core\Render\Render;

/**
 * Class TumblerN
 *
 * @sample
 * <label render="TumblerN" class="tumbler left">
 *   <input class="input" type="checkbox" data-on-type="integer" data-on="1" data-off-type="integer" data-off="0"/>
 *   <value xpath="" f-xpath="">Default</value>
 *   <slider class="slider round red"/>
 * </label>
 * @discription
 * Обработчик в цикле перебирает все свойства и атрибуты xml веток label, input, slider<br>
 * label - Внешняя обвёртка overlay<br>
 * /input - Значения<br>
 * /value<br>
 * /value[xpath] - Путь к значению в XMLData<br>
 * /value[f-xpath] - Путь к структуре поля в XMLData<br>
 * /slider - Внутренний слайдер<br>
 */
class TumblerN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    $class = (string)($xmlItem->input['class'] ?? '');
    
    $disabled = self::isTrue($xmlItem->input['disabled'] ?? $variables['disabled'] ?? $variables['readonly'] ?? '');
    unset($xmlItem->input['class'], $xmlItem->input['disabled'], $xmlItem->input['checked']);
    
    $attribute = self::getAttribute($xmlItem->input);
    
    if ($disabled) {
      $class .= ' no-update';
      $attribute .= ' disabled=""';
    } else {
      $attribute .= isset($xmlItem->value['xpath']) || isset($xmlItem->value['f-xpath'])
        ? self::getXPathAttribute($xmlItem->value, $xmlData) : '';
    }
    
    $value = isset($xmlItem->value) && isset($xmlItem->value['xpath']) ? self::getXPathValue($xmlItem->value, $xmlData)
      : '';
    $value = '' === $value ? self::getValue($xmlItem->value ?? null) : $value;
    
    $valueOnType = strtolower($xmlItem->input['data-on-type'] ?? 'integer');
    $valueOn = (string)($xmlItem->input['data-on'] ?? '1');
    $valueOn = 'int' === $valueOnType ? (int)$valueOn : $valueOn;
    
    $attribute = trim('class="'.trim($class).'"'.($valueOn === $value ? ' checked=""' : '').$attribute
      ." value=\"$value\"");
    
    return "<label ".self::getAttribute($xmlItem).">
  <input $attribute />
  <span ".self::getAttribute($xmlItem->slider ?? '')."></span>
</label>";
  }
}