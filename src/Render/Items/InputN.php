<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\{Items\Interfaces\HtmlInterface, Render};

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
    $value = '' === $value ? '' : self::convertValue($xmlItem->convert ?? null, $value);
    
    return 'input' === $tagName ? "<input $attribute value=\"$value\"/>" : "<$tagName $attribute>$value</$tagName>";
  }
}