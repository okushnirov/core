<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Str, Render\Items\Interfaces\HtmlInterface, Render\Items\Options\Options,
  Render\Items\Options\OptionsDict, Render\Items\Options\OptionsSQL, Render\Items\Options\OptionsWS, Render\Render
};

class SelectOf extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    if (!isset($xmlItem->source) || !isset($xmlItem->destination)) {
      
      return '';
    }
    
    # Source
    $sourceFilter = trim($xmlItem->source->filter ?? '');
    
    if (isset($xmlItem->source->dict)) {
      $sourcePrepare = static::isTrue($xmlItem->source->dict['prepare'] ?? '');
      $source = OptionsDict::get($xmlItem->source->dict, $objID, $xmlData);
    } elseif (isset($xmlItem->source->sql)) {
      $sourcePrepare = static::isTrue($xmlItem->source->sql['prepare'] ?? '');
      $source = OptionsSQL::get($xmlItem->source->sql);
    } elseif (isset($xmlItem->source->ws)) {
      $sourcePrepare = static::isTrue($xmlItem->source->ws['prepare'] ?? '');
      $source = OptionsWS::get($xmlItem->source->ws);
    } else {
      $source = [];
      $sourcePrepare = false;
    }
    
    # Destination
    $isReadonly = self::isTrue($xmlItem->destination->input['readonly'] ?? (is_object($variables)
      ? ($variables->readonly ?? '') : (is_array($variables) ? ($variables['readonly'] ?? '')
        : '')));
    $destClass = (string)($xmlItem->destination->input['class'] ?? '');
    
    if ($isReadonly) {
      $destClass .= ' no-update';
    } else {
      unset($xmlItem->destination->input['class']);
      
      $destAttribute = self::getAttribute($xmlItem->destination?->input);
      $destAttribute = isset($xmlItem->destination?->input?->value['xpath'])
      || isset($xmlItem->destination?->input?->value['f-xpath'])
        ? self::getXPathAttribute($xmlItem->destination->input->value, $xmlData, $destAttribute) : $destAttribute;
    }
    
    $destPrepare = static::isTrue($xmlItem->destination->value['prepare'] ?? '');
    $dest = [];
    
    # Value
    $value = isset($xmlItem->destination?->input?->value) && isset($xmlItem->destination?->input?->value['xpath'])
      ? self::getXPathValue($xmlItem->destination->input->value, $xmlData) : '';
    $value = '' === $value ? self::getValue($xmlItem->destination?->input?->value ?? null) : $value;
    
    # Type data
    switch ((int)($xmlItem->destination['data-type'] ?? 0)) {
      # Value into string separator
      case 0:
        $array = Options::explode($source, $value, $xmlItem->destination->input['data-separator'] ?? ';');
        $source = $array['source'];
        $dest = $array['destination'];
        
        break;
      
      # Value into row table
      case 1:
        // Значения из строк таблицы
    }
    
    # Options Source
    $optionSource = Options::first($xmlItem->source->select->option ?? '');
    $optionSource .= Options::list($source, 'string', '', $sourcePrepare, 1, $sourceFilter);
    
    # Source Container
    $htmlS = $isReadonly
      ? ''
      : mb_eregi_replace('#source', "<select ".self::getAttribute($xmlItem->source->select).">$optionSource</select>",
        isset($xmlItem->source->container) ? Render::xml2HTML(Render::xml2DOM($xmlItem->source->container->children()),
          $objID, $xmlData, $variables) : '');
    
    # Destination Container
    $htmlD = mb_eregi_replace('#dest:items', self::options($dest, $destPrepare), mb_eregi_replace('#dest:input',
      $isReadonly ? "<input class=\"$destClass\" readonly=\"\" type=\"hidden\" value=\"\"/>"
        : "<input class=\"$destClass\" $destAttribute value=\"$value\"/>", isset($xmlItem->destination->container)
        ? Render::xml2HTML(Render::xml2DOM($xmlItem->destination->container->children()), $objID, $xmlData, $variables)
        : ''));
    
    return "<{$xmlItem->getName()} ".self::getAttribute($xmlItem).">$htmlS$htmlD</{$xmlItem->getName()}>";
  }
  
  private static function options(array $options, bool $isPrepare):string
  {
    $html = '';
    $order = 1;
    
    foreach ($options as $key => $value) {
      $html .= "
<div class=\"--dest-item\" data-order=\"".$order++."\" data-value=\"$key\">
  <div class=\"--name\">".trim($isPrepare ? Str::prepare($value) : $value)."</div>
  <button class=\"--dest-move\" onclick=\"\" type=\"button\" data-direction=\"up\"/>
  <button class=\"--dest-move\" onclick=\"\" type=\"button\" data-direction=\"down\"/>
  <button class=\"--dest-cmd\" onclick=\"\" type=\"button\"/>
</div>";
    }
    
    return $html;
  }
}