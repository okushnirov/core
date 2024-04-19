<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Str, Render\Items\Interfaces\HtmlInterface, Render\Items\Library\Options,
  Render\Items\Library\OptionsDict, Render\Items\Library\OptionsSQL, Render\Render
};

class CheckboxOptionsN extends Render implements HtmlInterface
{
  public static function html(\SimpleXMLElement $xmlItem, $objID = false, $xmlData = false, $variables = false):string
  {
    if (!isset($xmlItem->source)) {
      
      return '';
    }
    
    $source = [];
    
    if (isset($xmlItem->dict)) {
      $source = OptionsDict::get($xmlItem->dict, $objID);
    } # SQL
    elseif (isset($xmlItem->sql)) {
      $source = OptionsSQL::get($xmlItem->sql);
    }
    
    if (empty($source)) {
      
      return '';
    }
    
    # Class
    $chkClass = trim($xmlItem->source['class'] ?? '');
    
    # Readonly
    $readonly = self::isTrue($xmlItem->input['readonly'] ?? $variables['readonly'] ?? '');
    
    # Separator
    $separator = $xmlItem->input['data-separator'] ?? ';';
    
    # Values
    $value = isset($xmlItem->source['xpath']) ? self::getXPathValue($xmlItem->source, $xmlData) : '';
    $value = '' === $value ? self::getValue($xmlItem->source) : $value;
    $array = Options::explode($source, $value, $separator);
    
    # Filter
    $filter = trim($xmlItem->filter ?? '');
    
    # Label
    $labelIsAfter = 'after' === strtolower(trim($xmlItem->label['position'] ?? 'after'));
    
    unset($xmlItem->label['position']);
    
    $labelAttribute = self::getAttribute($xmlItem->label);
    
    # Settings
    if ($readonly) {
      $chkAttribute = 'readonly="" tabindex="-1"';
      $chkClass .= ' no-update';
      $html = '';
      $name = '';
    } else {
      unset($xmlItem->source['xpath'], $xmlItem->source['class']);
      
      $chkAttribute = self::getAttribute($xmlItem->source);
      $name = (string)($xmlItem->input['name'] ?? '');
      $html = "<input type=\"hidden\" ".self::getAttribute($xmlItem->input)." value=\"$value\" />";
    }
    
    # Attribute
    $chkAttribute = trim('class="'.trim($chkClass).'" '.$chkAttribute);
    $chkAttribute .= !$readonly && $name ? " data-name=\"$name\"" : '';
    
    # Container
    $htmlContainer = isset($xmlItem->container) ? "<fieldset ".self::getAttribute($xmlItem->container).">" : '';
    
    foreach ($source as $key => $label) {
      if ('' !== $filter && false === mb_stripos($filter, "$separator$key$separator")) {
        
        continue;
      }
      
      $id = (new \DateTime())->format('u')."_$key";
      
      $labelHtml = "<label ".($readonly ? 'data-readonly=""' : "for=\"$id\"")." $labelAttribute>".Str::prepare($label)
        ."</label>";
      
      $html .= ($htmlContainer ? : '').($labelIsAfter ? '' : $labelHtml)."<input ".($readonly ? '' : "id=\"$id\"")
        ." type=\"checkbox\" $chkAttribute ".(isset($array['destination'][$key]) ? 'checked=""' : '')
        ." value=\"$key\"/>".($labelIsAfter ? $labelHtml : '').($htmlContainer ? '</fieldset>' : '');
    }
    
    return "<{$xmlItem->getName()} ".self::getAttribute($xmlItem).">$html</{$xmlItem->getName()}>";
  }
}