<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\Render\{Items\Interfaces\HtmlInterface, Items\Options\Options, Items\Options\OptionsDict,
  Items\Options\OptionsSQL, Items\Options\OptionsWS, Render
};

class SelectOptionsN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    if (!isset($xmlItem->source) || !isset($xmlItem->destination)) {
      
      return '';
    }
    
    # Source
    $attributeSource = self::getAttribute($xmlItem->source);
    $sourcePrepare = false;
    $source = [];
    
    if (isset($xmlItem->source->dict)) {
      $sourcePrepare = static::isTrue($xmlItem->source->dict['prepare'] ?? '');
      $source = OptionsDict::get($xmlItem->source->dict, $objID, $xmlData);
    } elseif (isset($xmlItem->source->sql)) {
      $sourcePrepare = static::isTrue($xmlItem->source->sql['prepare'] ?? '');
      $source = OptionsSQL::get($xmlItem->source->sql);
    } elseif (isset($xmlItem->source->ws)) {
      $sourcePrepare = static::isTrue($xmlItem->source->ws['prepare'] ?? '');
      $source = OptionsWS::get($xmlItem->source->ws);
    }
    
    $filter = trim($xmlItem->source->filter ?? '');
    
    # Destination
    $attributeDestination = self::getAttribute($xmlItem->destination);
    $attributeDestination = isset($xmlItem->destination->value)
    && (isset($xmlItem->destination->value['xpath']) || isset($xmlItem->destination->value['f-xpath']))
      ? self::getXPathAttribute($xmlItem->destination->value, $xmlData, $attributeDestination) : $attributeDestination;
    $destinationPrepare = static::isTrue($xmlItem->destination['prepare'] ?? '');
    $destination = [];
    
    # Source name
    preg_match('/data-source-name="(.*?)"/', $attributeSource, $matches, PREG_OFFSET_CAPTURE);
    
    if (empty($matches)) {
      preg_match('/name="(.*?)"/', $attributeDestination, $matches, PREG_OFFSET_CAPTURE);
      $attributeSource .= empty($matches[0][0]) ? '' : " data-source-".$matches[0][0];
    }
    
    # Value
    $value = isset($xmlItem->destination->value) && isset($xmlItem->destination->value['xpath'])
      ? self::getXPathValue($xmlItem->destination->value, $xmlData) : '';
    $value = '' === $value ? self::getValue($xmlItem->value ?? null) : $value;
    
    # Type data
    switch ((int)($xmlItem->destination['data-type'] ?? 0)) {
      # Value into string separator
      case 0:
        $array = Options::explode($source, $value, $xmlItem->destination['data-separator'] ?? ';');
        $source = $array['source'];
        $destination = $array['destination'];
        
        break;
      
      # Value into row table
      case 1:
        // Значения из строк таблицы
    }
    
    # Options Source & Destination
    $optionSource = Options::first($xmlItem->source->option ?? '', !empty($source));
    $optionSource .= Options::list($source, 'string', '', $sourcePrepare, 1, $filter);
    $optionDestination = Options::first($xmlItem->destination->option ?? '', !empty($destination));
    $optionDestination .= Options::list($destination, 'string', '', $destinationPrepare, 1);
    
    # Source Container
    $htmlS = "
    <select $attributeSource>$optionSource</select>";
    $htmlS = isset($xmlItem->source->container) ? "
  <div class=\"".($xmlItem->source->container['class'] ?? '')."\">"
      .Render::xml2HTML(Render::xml2DOM($xmlItem->source->container->children()), $objID, $xmlData, $variables)."$htmlS
  </div>" : $htmlS;
    
    # Center Container
    $htmlC = isset($xmlItem->c) ? "
    <div ".self::getAttribute($xmlItem->c).">".Render::xml2HTML(Render::xml2DOM($xmlItem->c->children()))."</div>" : '';
    
    # Destination Container
    $htmlD = "
    <select $attributeDestination>$optionDestination</select>";
    $htmlD = isset($xmlItem->destination->container) ? "
  <div class=\"".($xmlItem->destination->container['class'] ?? '')."\">"
      .Render::xml2HTML(Render::xml2DOM($xmlItem->destination->container->children()), $objID, $xmlData, $variables)."$htmlD
  </div>" : $htmlD;
    
    return "<{$xmlItem->getName()} ".self::getAttribute($xmlItem).">$htmlS$htmlC$htmlD
</{$xmlItem->getName()}>";
  }
}