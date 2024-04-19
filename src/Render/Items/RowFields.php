<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Lang, Library\Str, Render\Items\Interfaces\HtmlInterface, Render\Render};

class RowFields extends Render implements HtmlInterface
{
  /**
   * Add title
   *
   * @var array|string[]
   */
  private static array $_add = [
    'uk' => 'Додати',
    'ru' => 'Добавить'
  ];
  
  /**
   * Debug
   *
   * @var bool
   */
  private static bool $_debug = false;
  
  /**
   * Disabled
   *
   * @var bool
   */
  private static bool $_disabled = false;
  
  /**
   * Fields
   *
   * @var array
   */
  private static array $_fields = [];
  
  /**
   * Moved rows
   *
   * @var bool
   */
  private static bool $_moved = true;
  
  /**
   * Remove title
   *
   * @var array|string[]
   */
  private static array $_remove = [
    'uk' => 'Видалити',
    'ru' => 'Удалить'
  ];
  
  /**
   * Render
   *
   * @param \SimpleXMLElement $xmlItem
   * @param int|bool $objID
   * @param \SimpleXMLElement|bool $xmlData
   * @param mixed $variables
   *
   * @return string
   */
  public static function html(\SimpleXMLElement $xmlItem, $objID = false, $xmlData = false, $variables = false):string
  {
    if (empty($xmlItem['name'] ?? '')) {
      
      return '';
    }
    
    # Disabled
    self::$_disabled = self::isTrue($xmlItem['disabled'] ?? $variables['readonly'] ?? '');
    
    # Moved
    self::$_moved = self::isTrue($xmlItem['moved'] ?? true);
    
    unset($xmlItem['disabled'], $xmlItem['moved']);
    
    # Attributes
    $attribute = trim(self::getAttribute($xmlItem).(self::$_disabled ? ' disabled="" tabindex="-1"' : ''));
    
    # Value
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
          
          if (!isset(self::$_fields[$fieldName])) {
            self::$_fields[$fieldName] = [];
          }
          
          if (!isset(self::$_fields[$fieldName][$rowID])) {
            self::$_fields[$fieldName][$rowID] = [];
          }
          
          self::$_fields[$fieldName][$rowID][$name] = (string)$cell;
        }
      }
    }
    
    $html = '';
    
    # Header
    if (isset($xmlItem->header)
      && $xmlItem->header->children()
                         ->count()) {
      $headerAttr = self::getAttribute($xmlItem->header);
      $htmlHeader = self::$_disabled || !self::$_moved ? '' : '<div class="header-wrap"></div>';
      
      foreach ($xmlItem->header->children() as $cell) {
        $isPrepare = self::isTrue($cell['prepare'] ?? '');
        
        unset($cell['prepare']);
        
        $cellAttr = self::getAttribute($cell ?? '');
        $cellName = (string)($cell->{Lang::$lang} ?? $cell ?? '');
        
        $htmlHeader .= "<{$cell->getName()} $cellAttr>".($isPrepare ? Str::prepare($cellName) : $cellName)
          ."</{$cell->getName()}>";
      }
      
      $htmlHeader .= self::$_disabled || !self::$_moved ? '' : '<div class="header-wrap"></div>';
      
      $html .= $htmlHeader ? "<div $headerAttr>$htmlHeader</div>" : '';
    }
    
    # Rows
    if (isset($xmlItem->rows)
      && $xmlItem->rows->children()
                       ->count()) {
      for ($rowID = 1; $rowID <= count($fieldData); $rowID++) {
        $html .= self::_getRow($xmlItem, $rowID);
      }
      
      $html .= empty($fieldData) ? self::_getRow($xmlItem, $rowID) : '';
    }
    
    # Actions
    $html = "<div $attribute>".$html.(self::$_disabled
        ? ''
        : self::_getRow($xmlItem, -1).'<div class="fld-action">
<button type="button" data-ref="row-add">'.(self::$_add[Lang::$lang] ?? 'Add').'</button>
<button type="button" data-ref="row-remove" disabled="">'.(self::$_remove[Lang::$lang] ?? 'Remove').'</button>
<input class="fld-cell-el" type="hidden" name="row:change"/>
</div>').'</div>';
    
    if (self::$_debug) {
      trigger_error($html);
    }
    
    return $html;
  }
  
  /**
   * Get row data
   *
   * @param \SimpleXMLElement $xmlItem
   * @param int $rowID
   *
   * @return string
   */
  private static function _getRow(\SimpleXMLElement $xmlItem, int $rowID = -1):string
  {
    $fieldName = (string)$xmlItem['name'];
    $rowsAttr = trim(self::getAttribute($xmlItem->rows ?? '').(-1 === $rowID ? ' data-empty=""' : '').(self::$_disabled
      || !self::$_moved ? ' data-not-moved=""' : ''));
    
    # Checkbox
    $html = self::$_disabled ? ''
      : '<div class="fld-check"><input class="checkbox-n no-update" type="checkbox" /></div>';
    
    if (self::$_debug && 0 < $rowID) {
      trigger_error(json_encode(self::$_fields[$fieldName][$rowID] ?? "Data field #$fieldName not found",
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    # Row
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
              'readonly' => self::$_disabled
            ]);
            
            continue;
          }
          
          # Set item value
          $itemValue = in_array($item->getName(), [
            'select',
            'fieldset'
          ], true) ? 'source' : 'value';
          
          if (isset($item->{$itemValue})) {
            $item->{$itemValue} = -1 == $rowID ? '' : (string)(self::$_fields[$fieldName][$rowID][$itemName] ?? '');
          }
          
          if (self::$_debug && 0 < $rowID) {
            trigger_error($rowID.' -> '.$itemName.' = '.$item->value);
          }
          
          # Get HTML element | DOM2HTML
          $htmlEl = Render::xml2HTML(Render::xml2DOM($item), false, false, [
            'readonly' => self::$_disabled
          ]);
          
          # Correction node this
          
          $html .= $htmlEl;
        }
        
        $html .= "</{$cell->getName()}>";
      }
      
      $html .= "</{$row->getName()}>";
    }
    
    # Moved
    $html .= self::$_disabled || !self::$_moved ? ''
      : '<div class="fld-moved"><div class="cmd-move" data-ref="row-up"></div><div class="cmd-move" data-ref="row-down"></div></div>';
    
    return $html ? "<div $rowsAttr>$html</div>" : '';
  }
}