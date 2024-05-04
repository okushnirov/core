<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Str, Render\Items\Interfaces\HtmlInterface, Render\Items\Library\OptionsDict,
  Render\Items\Library\OptionsSQL, Render\Render
};

class RadioboxOptionsN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    if (!isset($xmlItem->source)) {
      
      return '';
    }
    
    # Source
    $source = [];
    
    # Type
    $type = 'string';
    
    if (isset($xmlItem->dict)) {
      $source = OptionsDict::get($xmlItem->dict, $objID);
      $type = (string)($xmlItem->dict['type'] ?? $type);
    } # SQL
    elseif (isset($xmlItem->sql)) {
      $source = OptionsSQL::get($xmlItem->sql);
      $type = (string)($xmlItem->sql['type'] ?? $type);
    }
    
    if (empty($source)) {
      
      return '';
    }
    
    # Class
    $class = trim($xmlItem->input['class'] ?? '');
    
    # Readonly
    $readonly = self::isTrue($xmlItem->input['readonly'] ?? $variables['readonly'] ?? '');
    
    # Values
    $value = isset($xmlItem->source['xpath']) ? self::getXPathValue($xmlItem->source, $xmlData) : '';
    $value = '' === $value ? self::getValue($xmlItem->source) : $value;
    $value = 'string' === $type || '' === $value ? $value : (int)$value;
    
    # Label
    $labelIsAfter = 'after' === strtolower(trim($xmlItem->label['position'] ?? 'after'));
    
    unset($xmlItem->input['type'], $xmlItem->label['position']);
    
    $labelAttribute = self::getAttribute($xmlItem->label);
    
    $html = '';
    
    # Settings
    if ($readonly) {
      $chkAttribute = 'readonly="" tabindex="-1"';
      $class .= ' no-update';
      $name = '';
    } else {
      $name = (string)($xmlItem->input['name'] ?? '');
      
      unset($xmlItem->source['xpath'], $xmlItem->input['class'], $xmlItem->input['name']);
      
      $chkAttribute = self::getAttribute($xmlItem->input);
    }
    
    # Attribute
    $chkAttribute = trim('class="'.trim($class).'" '.$chkAttribute);
    $chkAttribute .= !$readonly && $name ? " name=\"".$name."\"" : '';
    
    # Container
    $htmlContainer = isset($xmlItem->container) ? "<fieldset ".self::getAttribute($xmlItem->container).">" : '';
    
    foreach ($source as $key => $label) {
      $id = (new \DateTime())->format('Hisu')."_$key";
      $key = 'string' === $type ? $key : (int)$key;
      
      $labelHtml = "<label ".($readonly ? 'data-readonly=""' : "for=\"$id\"")." $labelAttribute>".Str::prepare($label)
        ."</label>";
      
      $html .= ($htmlContainer ? : '').($labelIsAfter ? '' : $labelHtml)."<input ".($readonly ? '' : "id=\"$id\"")
        ." type=\"radio\" $chkAttribute ".($key === $value ? 'checked=""' : '')." value=\"$key\"/>".($labelIsAfter
          ? $labelHtml : '').($htmlContainer ? '</fieldset>' : '');
    }
    
    return "<{$xmlItem->getName()} ".self::getAttribute($xmlItem).">$html</{$xmlItem->getName()}>";
  }
}