<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\{Items\Interfaces\HtmlInterface, Render};

class TumblerN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    $class = (string)($xmlItem->input['class'] ?? '');
    $disabled = self::isTrue($xmlItem->input['disabled'] ?? (is_object($variables)
      ? ($variables->disabled ?? $variables->readonly ?? '') : (is_array($variables)
        ? ($variables['disabled'] ?? $variables['readonly'] ?? '') : '')));
    
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
    $valueOn = 'integer' === $valueOnType ? (int)$valueOn : $valueOn;
    
    $attribute .= '' === $class ? '' : ' class="'.trim($class).'"';
    $attribute .= $valueOn === $value ? ' checked=""' : '';
    $attribute .= " value=\"$value\"";
    
    return "<{$xmlItem->getName()} ".self::getAttribute($xmlItem).">
  <input ".trim($attribute)." />
  <span ".self::getAttribute($xmlItem->slider ?? '')."></span>
</{$xmlItem->getName()}>";
  }
}