<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\{Items\Interfaces\HtmlInterface, Render};

class CheckboxN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    $class = (string)($xmlItem['class'] ?? '');
    $disabled = self::isTrue($xmlItem['disabled'] ?? (is_object($variables)
      ? ($variables->disabled ?? $variables->readonly ?? '') : (is_array($variables)
        ? ($variables['disabled'] ?? $variables['readonly'] ?? '') : '')));
    
    unset($xmlItem['class'], $xmlItem['disabled']);
    
    $attribute = self::getAttribute($xmlItem);
    
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
    
    $valueOnType = strtolower($xmlItem['data-on-type'] ?? 'number');
    $valueOn = (string)($xmlItem['data-on'] ?? '1');
    
    if ('number' === $valueOnType && '' !== $value && '' !== $valueOn) {
      $value = (int)$value;
      $valueOn = (int)$valueOn;
    }
    
    $attribute .= '' === $class ? '' : ' class="'.trim($class).'"';
    $attribute .= $valueOn === $value ? ' checked=""' : '';
    $attribute .= " value=\"$value\"";
    
    return "<input ".trim($attribute)."/>";
  }
}