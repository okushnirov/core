<?php

namespace okushnirov\core\Render\Items;

use okushnirov\core\{Library\Location, Library\User, Render\Items\Interfaces\HtmlInterface, Render\Render};

class AvatarN extends Render implements HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, $objID = false, $xmlData = false, $variables = false):string
  {
    $value = self::getXPathValue($xmlItem, $xmlData);
    $noAvatar = '' === $value;
    
    return "<input class=\"avatar-upload no-update\" ".self::getXPathAttribute($xmlItem, $xmlData)." accept=\"image/*\" type=\"file\" value=\"$value\"/>
<div class=\"avatar-image ".($noAvatar ? 'no-avatar' : '').'" '.($noAvatar ? ''
        : 'style="background-image: url('.$value.');"').'>'.('profile' === Location::$folder
      || User::$isDev ? "
  <div class=\"--remove mif-bin\" ".($noAvatar ? 'style="display:none;"' : "data-image=\"$value\"")."></div>
  <div class=\"--add mif-add-photo\"></div>" : '').'
</div>';
  }
}