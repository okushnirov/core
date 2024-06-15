<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\{Items\Interfaces\HtmlInterface, Items\Options\Options, Items\Options\OptionsCount,
  Items\Options\OptionsDict, Items\Options\OptionsSQL, Items\Options\OptionsWS, Render
};

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
      $attribute = 'disabled="" tabindex="-1"';
      $class .= ' no-update';
    } else {
      unset($xmlItem['class'], $xmlItem['disabled']);
      
      $attribute = self::getAttribute($xmlItem);
      $attribute = isset($xmlItem->source['xpath']) || isset($xmlItem->source['f-xpath'])
        ? self::getXPathAttribute($xmlItem->source, $xmlData, $attribute) : $attribute;
    }
    
    $attribute = trim('class="'.trim($class).'" '.$attribute);
    $fieldName = '';
    
    if (isset($xmlItem->source['xpath'])) {
      preg_match_all("/['|\"](.*)['|\"]/", $xmlItem->source['xpath'], $matches, PREG_SET_ORDER);
      $fieldName = trim($matches[0][1] ?? '');
    }
    
    $type = (string)($xmlItem->source['type'] ?? 'string');
    
    $value = isset($xmlItem->source) && isset($xmlItem->source['xpath']) ? self::getXPathValue($xmlItem->source,
      $xmlData) : '';
    $value = '' === $value ? self::getValue($xmlItem->source ?? null) : $value;
    $value = 'string' === $type || '' === $value ? $value : (int)$value;
    
    $filter = trim($xmlItem->filter ?? '');
    
    $source = [];
    $isPrepare = false;
    
    if ('' !== $fieldName) {
      self::$prevValues[$fieldName] = $value;
    }
    
    switch ($xmlItem->source['dest'] ?? '') {
      case 'count':
        $source = OptionsCount::get($xmlItem->count ?? null);
        
        break;
      
      case 'dict':
        $isPrepare = static::isTrue($xmlItem->dict['prepare'] ?? '');
        $source = OptionsDict::get($xmlItem->dict ?? null, $objID, $xmlData);
        
        break;
      
      case 'sql':
        $isPrepare = static::isTrue($xmlItem->sql['prepare'] ?? '');
        $source = OptionsSQL::get($xmlItem->sql ?? null);
        
        break;
      
      case 'ws':
        $isPrepare = static::isTrue($xmlItem->ws['prepare'] ?? '');
        $source = OptionsWS::get($xmlItem->ws ?? null);
    }
    
    $option = isset($xmlItem->option) ? Options::first($xmlItem->option, false, $type, $value, $fieldName) : '';
    
    return "<select $attribute>$option".Options::list($source, $type, $value, $isPrepare, 0, $filter, $fieldName)
      ."</select>";
  }
}