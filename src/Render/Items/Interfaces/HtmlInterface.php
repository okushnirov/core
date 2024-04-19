<?php

namespace okushnirov\core\Render\Items\Interfaces;

interface HtmlInterface
{
  public static function html(\SimpleXMLElement $xmlItem, $objID = false, $xmlData = false, $variables = false):string;
}