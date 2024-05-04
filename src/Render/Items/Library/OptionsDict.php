<?php

namespace okushnirov\core\Render\Items\Library;

use okushnirov\core\Library\{DbSQLAnywhere, Enums\SQLAnywhere, Lang, User};

class OptionsDict
{
  public static function get(?\SimpleXMLElement $xml, int $objID = 0):array
  {
    $id = (int)($xml['id'] ?? 0);
    
    if (empty($id)) {
      
      return [];
    }
    
    /**
     * 0 - Назва
     * 1 - ID
     * інакше isnull("Порядок", "Назва")
     */
    $order = (int)($xml['order'] ?? 0);
    $parent = (string)($xml['parent'] ?? '');
    $parentName = (string)($xml['parentName'] ?? '');
    
    if ($parentName && 0 !== $objID) {
      $parentNameEscape = DbSQLAnywhere::escape($parentName, true);
      
      $SQL = "SELECT \"_объект_значение_поля\"($objID,$parentNameEscape)";
      $parent = (string)DbSQLAnywhere::query($SQL, SQLAnywhere::COLUMN);
      
      // if $parentName && empty($objID) Разобраться с методом получения данных self::__getXMLValue
      /*$parentFieldData = self::__getXMLValue($parentName, $xmlData);
      $parent = (string)($parentFieldData['value'] ?? '');*/
    }
    
    $parent = '' === $parent ? 'null' : (int)$parent;
    
    $partner = User::$partner->id ?? 0;
    $partner = $partner ? : 'null';
    $lang = DbSQLAnywhere::escape(Lang::$lang, true);
    
    return (new Options())::fill($xml, "CALL \"dbo\".\"_справочник_список\"($id,$order,$parent,$partner,$lang)");
  }
}