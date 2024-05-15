<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Crypt, Library\Enums\Encrypt, Library\Lang, Library\Str, Library\User,
  Render\Items\Dict\Dict, Render\Items\Interfaces\HtmlInterface, Render\Render
};

class TableN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string
  {
    $dictID = (int)($xmlItem->dict['id'] ?? 0);
    $dictIDCrypt = $dictID ? Crypt::action((string)$dictID, Encrypt::CHR) : '';
    
    $tableID = trim($xmlItem['id'] ?? '');
    $tableClass = trim($xmlItem['class'] ?? '');
    $tableWidth = trim($xmlItem['width'] ?? '100%');
    
    $tableAttr = '' === $tableClass ? '' : "class=\"$tableClass\"";
    $tableAttr .= '' === $tableID ? '' : "id=\"$tableID\"";
    $tableAttr .= '' === $dictIDCrypt ? '' : " data-ref=\"$dictIDCrypt\"";
    $tableAttr .= " width=\"$tableWidth\"";
    
    $source = match (trim($xmlItem->source['dest'] ?? '')) {
      'dict' => Dict::list(Dict::getQuery($xmlItem->dict ?? null, $objID, $xmlData)),
      'sql' => Dict::list(trim($xmlItem->sql['sql'] ?? '')),
      default => [],
    };
    
    $html = "
<table $tableAttr>";
    
    if (!$xmlItem->column->count()) {
      
      return $html."
</table>";
    }
    
    $arrayColumnsLabel = [];
    $langShort = Lang::getShort(Lang::$lang);
    
    if (isset($xmlItem->column->th)) {
      $theadClass = empty($xmlItem->thead['class']) ? false : trim($xmlItem->thead['class']);
      $theadTrClass = empty($xmlItem->thead->tr['class']) ? false : trim($xmlItem->thead->tr['class']);
      
      $html .= "
  <thead class='$theadClass'>
    <tr class='$theadTrClass'>";
      
      foreach ($xmlItem->column as $column) {
        $thAttribute = '';
        
        if ($dictID) {
          if (empty($column['field'])) {
            
            continue;
          }
          
          $langPostFix = self::isTrue($column['lang-postfix']);
          $field = Str::lowerCase($column['field']).($langPostFix ? $langShort : '');
          $fieldCrypt = Crypt::action($field, Encrypt::CHR);
          $thAttribute .= "ref=\"$fieldCrypt\"";
        }
        
        $isPrepare = self::isTrue($column->th['prepare']);
        $thName = trim($column->th->{Lang::$lang} ?? $column->th);
        $thName = $isPrepare ? Str::prepare($thName) : $thName;
        
        $thAttribute .= self::getAttribute($column->th);
        
        $arrayColumnsLabel[] = $thName;
        
        $html .= "
      <th $thAttribute>$thName</th>";
      }
      
      $html .= "
      </tr>
    </thead>";
    }
    
    $tbodyClass = empty($xmlItem->tbody['class']) ? false : trim($xmlItem->tbody['class']);
    $tbodyTrClass = empty($xmlItem->tbody->tr['class']) ? false : trim($xmlItem->tbody->tr['class']);
    
    $html .= "
    <tbody class='$tbodyClass'>";
    
    if (empty($source)) {
      if ($xmlItem->column->count()) {
        $arrayRows = [];
        $indexCol = 0;
        
        foreach ($xmlItem->column as $column) {
          $indexRow = 0;
          
          foreach ($column->td as $row) {
            if (!isset($arrayRows[$indexRow])) {
              $arrayRows[$indexRow] = [];
            }
            
            $arrayRows[$indexRow][$indexCol]['class'] = empty($row['class']) ? '' : $row['class'];
            $arrayRows[$indexRow][$indexCol]['prepare'] = !(!empty($row['prepare'])
              && 'false' === Str::lowerCase($row['prepare']));
            $arrayRows[$indexRow][$indexCol]['value'] = empty($row->{Lang::$lang}) ? (string)$row
              : (string)$row->{lang::$lang};
            $indexRow++;
          }
          
          $indexCol++;
        }
        
        unset ($row, $column);
        
        if (!empty($arrayRows)) {
          foreach ($arrayRows as $row) {
            $html .= "
      <tr class='$tbodyTrClass'>";
            
            $indexCol = 0;
            
            foreach ($row as $column) {
              $isTdPrepare = self::isTrue($column['prepare'] ?? '');
              $tdClass = trim($column['class'] ?? '');
              $tdValue = $isTdPrepare ? Str::prepare($column['value']) : $column['value'];
              $tdAttribute = isset($arrayColumnsLabel[$indexCol]) ? Str::prepare(mb_eregi_replace('<br>', ' ',
                $arrayColumnsLabel[$indexCol])) : '';
              
              $html .= "
        <td class='$tdClass' aria-label='$tdAttribute'>$tdValue</td>";
              
              $indexCol++;
            }
            
            $html .= "
      </tr>";
          }
        }
      }
    } else {
      $methods = (User::$login ?? null) ? self::getMethodList($objID) : [];
      
      foreach ($source as $row) {
        $html .= self::htmlTableRowDict($xmlItem, $row, $methods, $langShort);
      }
    }
    
    $html .= "
  </tbody>
</table>";
    
    return $html;
  }
  
  public static function htmlTableRowDict(
    \SimpleXMLElement $xmlItem, array | bool $row, array $methods, string $langShort):string
  {
    $key = Str::lowerCase($xmlItem['key'] ?? '');
    $recIdentity = $row[$key] ?? '';
    
    if ('' === $recIdentity) {
      
      return '';
    }
    
    $methodRef = trim($xmlItem->tbody->tr['ref'] ?? '');
    $method = $methodRef ? self::getMethodData($methodRef, $methods) : new \stdClass();
    $methodAccess = 1 === ($method->access ?? 0);
    $recIdentityCrypt = Crypt::action($recIdentity, Encrypt::CHR);
    $tbodyTrClass = empty($xmlItem->tbody) || empty($xmlItem->tbody->tr) || empty($xmlItem->tbody->tr['class']) ? ''
      : trim($xmlItem->tbody->tr['class']);
    $trAttr = $methodAccess ? $method->attribute : '';
    
    $html = "
      <tr class='$tbodyTrClass' data-rec-id='$recIdentityCrypt' $trAttr>";
    
    foreach ($xmlItem->column as $column) {
      $langPostFix = self::isTrue($column['lang-postfix']);
      $field = isset($column['field']) ? $column['field'].($langPostFix ? $langShort : '') : '';
      
      if ('' === $field) {
        
        continue;
      }
      
      $thName = trim($column->th->{Lang::$lang} ?? $column->th);
      $tdAttr = self::getAttribute($column);
      $tdAttr .= " aria-label='$thName'";
      $tdName = Str::prepare($row[$field] ?? '');
      
      /*
      $data->tdDictID = (int)($column->td['dictID'] ?? 0);
      $data->tdName = 0 < $data->tdDictID && isset(self::$dict[$data->tdDictID][$row[$data->field]])
        ? self::$dict[$data->tdDictID][$row[$data->field]] : Str::prepare($row[$data->field] ?? '');
      
      if ($data->tdDictID) {
        $data->tdDictValues = explode(';', trim($data->tdName, ';'));
        $data->tdDictName = [];
        
        foreach ($data->tdDictValues as $tdDictValue) {
          foreach (self::$dict[$data->tdDictID] as $dictRow) {
            if ($dictRow['id'] === $tdDictValue) {
              $data->tdDictName[] = empty($dictRow[$column->td['dictFieldName'].Lang::getShort(Lang::$lang)])
                ? (empty($dictRow[(string)$column->td['dictFieldName']]) ? ''
                  : $dictRow[(string)$column->td['dictFieldName']]) : $dictRow[$column->td['dictFieldName']
                .Lang::getShort(Lang::$lang)];
            }
          }
        }
        
        $data->tdName = empty($data->tdDictName) ? $data->tdName : implode('<br>', $data->tdDictName);
      }*/
      
      $html .= "
        <td $tdAttr>$tdName</td>";
    }
    
    $html .= "
      </tr>";
    
    return $html;
  }
}