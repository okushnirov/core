<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Lang, Library\Str, Render\Items\Interfaces\HtmlInterface, Render\Render};

class RowFields extends Render implements HtmlInterface
{
  private static array $add = [
    'uk' => 'Додати',
    'ru' => 'Добавить'
  ];
  
  private static bool $debug = false;
  
  private static bool $disabled = false;
  
  private static array $fields = [];
  
  private static bool $moved = true;
  
  private static array $remove = [
    'uk' => 'Видалити',
    'ru' => 'Удалить'
  ];
  
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    if (empty($xmlItem['name'] ?? '')) {
      
      return '';
    }
    
    self::$disabled = self::isTrue($xmlItem['disabled'] ?? $variables['readonly'] ?? '');
    
    self::$moved = self::isTrue($xmlItem['moved'] ?? true);
    
    unset($xmlItem['disabled'], $xmlItem['moved']);
    
    $attribute = trim(self::getAttribute($xmlItem).(self::$disabled ? ' disabled="" tabindex="-1"' : ''));
    
    $fieldData = [];
    $fieldName = (string)$xmlItem['name'];
    
    if (isset($xmlItem->value['xpath'])) {
      $fieldData = $xmlData->xpath($xmlItem->value['xpath'].'/row') ?? [];
      
      foreach ($fieldData as $row) {
        $rowID = (int)($row['id'] ?? 0);
        
        if (0 >= $rowID) {
          
          continue;
        }
        
        foreach ($row->children() as $cell) {
          $name = trim((string)($cell['name'] ?? ''));
          
          if (!$name) {
            
            continue;
          }
          
          if (!isset(self::$fields[$fieldName])) {
            self::$fields[$fieldName] = [];
          }
          
          if (!isset(self::$fields[$fieldName][$rowID])) {
            self::$fields[$fieldName][$rowID] = [];
          }
          
          self::$fields[$fieldName][$rowID][$name] = (string)$cell;
        }
      }
    }
    
    $html = '';
    
    if (isset($xmlItem->header)
      && $xmlItem->header->children()
                         ->count()) {
      $headerAttr = self::getAttribute($xmlItem->header);
      $htmlHeader = self::$disabled || !self::$moved ? '' : '<div class="header-wrap"></div>';
      
      foreach ($xmlItem->header->children() as $cell) {
        $isPrepare = self::isTrue($cell['prepare'] ?? '');
        
        unset($cell['prepare']);
        
        $cellAttr = self::getAttribute($cell ?? '');
        $cellName = (string)($cell->{Lang::$lang} ?? $cell ?? '');
        
        $htmlHeader .= "<{$cell->getName()} $cellAttr>".($isPrepare ? Str::prepare($cellName) : $cellName)
          ."</{$cell->getName()}>";
      }
      
      $htmlHeader .= self::$disabled || !self::$moved ? '' : '<div class="header-wrap"></div>';
      
      $html .= $htmlHeader ? "<div $headerAttr>$htmlHeader</div>" : '';
    }
    
    if (isset($xmlItem->rows)
      && $xmlItem->rows->children()
                       ->count()) {
      for ($rowID = 1; $rowID <= count($fieldData); $rowID++) {
        $html .= self::_getRow($xmlItem, $rowID);
      }
      
      $html .= empty($fieldData) ? self::_getRow($xmlItem, $rowID) : '';
    }
    
    $html = "<div $attribute>".$html.(self::$disabled
        ? ''
        : self::_getRow($xmlItem, -1).'<div class="fld-action">
<button type="button" data-ref="row-add">'.(self::$add[Lang::$lang] ?? 'Add').'</button>
<button type="button" data-ref="row-remove" disabled="">'.(self::$remove[Lang::$lang] ?? 'Remove').'</button>
<input class="fld-cell-el" type="hidden" name="row:change"/>
</div>').'</div>';
    
    if (self::$debug) {
      trigger_error($html);
    }
    
    return $html;
  }
  
  private static function _getRow(\SimpleXMLElement $xmlItem, int $rowID = -1):string
  {
    $fieldName = (string)$xmlItem['name'];
    $rowsAttr = trim(self::getAttribute($xmlItem->rows ?? '').(-1 === $rowID ? ' data-empty=""' : '').(self::$disabled
      || !self::$moved ? ' data-not-moved=""' : ''));
    
    $html = self::$disabled ? ''
      : '<div class="fld-check"><input class="checkbox-n no-update" type="checkbox" /></div>';
    
    if (self::$debug && 0 < $rowID) {
      trigger_error(json_encode(self::$fields[$fieldName][$rowID] ?? "Data field #$fieldName not found",
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    foreach ($xmlItem->rows->children() as $row) {
      $rowAttr = trim(self::getAttribute($row));
      $html .= "<{$row->getName()} $rowAttr>";
      
      foreach ($row->children() as $cell) {
        $cellAttr = trim(self::getAttribute($cell));
        $html .= "<{$cell->getName()} $cellAttr>";
        
        foreach ($cell->children() as $item) {
          $itemName = trim((string)($item['name'] ?? ''));
          
          if (empty($itemName)) {
            $html .= Render::xml2HTML(Render::xml2DOM($item), false, false, [
              'readonly' => self::$disabled
            ]);
            
            continue;
          }
          
          $itemValue = in_array($item->getName(), [
            'select',
            'fieldset'
          ], true) ? 'source' : 'value';
          
          if (isset($item->{$itemValue})) {
            $item->{$itemValue} = -1 == $rowID ? '' : (string)(self::$fields[$fieldName][$rowID][$itemName] ?? '');
          }
          
          if (self::$debug && 0 < $rowID) {
            trigger_error($rowID.' -> '.$itemName.' = '.$item->value);
          }
          
          $htmlEl = Render::xml2HTML(Render::xml2DOM($item), false, false, [
            'readonly' => self::$disabled
          ]);
          
          $html .= $htmlEl;
        }
        
        $html .= "</{$cell->getName()}>";
      }
      
      $html .= "</{$row->getName()}>";
    }
    
    $html .= self::$disabled || !self::$moved ? ''
      : '<div class="fld-moved"><div class="cmd-move" data-ref="row-up"></div><div class="cmd-move" data-ref="row-down"></div></div>';
    
    return $html ? "<div $rowsAttr>$html</div>" : '';
  }
}