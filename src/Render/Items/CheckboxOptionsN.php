<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Str, Render\Items\Interfaces\HtmlInterface, Render\Items\Options\Options,
  Render\Items\Options\OptionsDict, Render\Items\Options\OptionsSQL, Render\Render
};

class CheckboxOptionsN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    if (!isset($xmlItem->source)) {
      
      return '';
    }
    
    $source = [];
    
    if (isset($xmlItem->dict)) {
      $source = OptionsDict::get($xmlItem->dict, $objID, $xmlData);
    } elseif (isset($xmlItem->sql)) {
      $source = OptionsSQL::get($xmlItem->sql);
    }
    
    if (empty($source)) {
      
      return '';
    }
    
    $chkClass = trim($xmlItem->source['class'] ?? '');
    
    $readonly = self::isTrue($xmlItem->input['readonly'] ?? $variables['readonly'] ?? '');
    
    $separator = $xmlItem->input['data-separator'] ?? ';';
    
    $value = isset($xmlItem->source['xpath']) ? self::getXPathValue($xmlItem->source, $xmlData) : '';
    $value = '' === $value ? self::getValue($xmlItem->source) : $value;
    $array = Options::explode($source, $value, $separator);
    
    $filter = trim($xmlItem->filter ?? '');
    
    $labelIsAfter = 'after' === strtolower(trim($xmlItem->label['position'] ?? 'after'));
    
    unset($xmlItem->label['position']);
    
    $labelAttribute = self::getAttribute($xmlItem->label);
    
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
    
    $chkAttribute = trim('class="'.trim($chkClass).'" '.$chkAttribute);
    $chkAttribute .= !$readonly && $name ? " data-name=\"$name\"" : '';
    
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