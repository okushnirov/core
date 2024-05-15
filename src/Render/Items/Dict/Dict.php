<?php

namespace okushnirov\core\Render\Items\Dict;

use okushnirov\core\{Library\DbSQLAnywhere, Library\Enums\SQLAnywhere, Library\Lang, Library\User, Render\Render};

class Dict extends Render
{
  public static function getQuery(
    ?\SimpleXMLElement $xml, int $objID = 0, \SimpleXMLElement | bool $xmlData = false):string
  {
    $id = (int)($xml['id'] ?? 0);
    
    if (!$id) {
      
      return '';
    }
    
    /**
     * 0 - Назва
     * 1 - ID
     * інакше - isnull("Порядок", "Назва")
     */
    $order = (int)($xml['order'] ?? 0);
    $parent = (string)($xml['parent'] ?? '');
    $parentName = (string)($xml['parentName'] ?? '');
    
    if ($parentName) {
      if ($objID) {
        $parentNameEscape = DbSQLAnywhere::escape($parentName, true);
        
        $SQL = "SELECT \"dbo\".\"_объект_значение_поля\"($objID,$parentNameEscape)";
        $parent = (string)DbSQLAnywhere::query($SQL, SQLAnywhere::COLUMN);
      } else {
        $parentFieldData = self::getXMLValue($parentName, $xmlData);
        $parent = (string)($parentFieldData['value'] ?? '');
      }
    }
    
    $parent = '' === $parent ? 'null' : (int)$parent;
    
    $partner = User::$partner->id ?? 0;
    $partner = $partner ? : 'null';
    $lang = Lang::getShort(Lang::$lang);//DbSQLAnywhere::escape(Lang::$lang, true);
    
    return "CALL \"dbo\".\"_справочник_список\"($id,$order,$parent,$partner,'$lang')";
  }
  
  public static function list(string $query):array
  {
    if ('' === trim($query)) {
      
      return [];
    }
    
    $result = DbSQLAnywhere::query($query, SQLAnywhere::FETCH_ALL);
    
    return empty($result) ? [] : $result;
  }
}