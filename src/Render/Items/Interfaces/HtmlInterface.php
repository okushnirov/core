<?php

namespace okushnirov\core\Render\Items\Interfaces;

interface HtmlInterface
{
  public static function html(
    \SimpleXMLElement $xmlItem, int $objID = 0, \SimpleXMLElement | bool $xmlData = false,
    mixed             $variables = false):string;
}