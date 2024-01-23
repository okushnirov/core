<?php

namespace okushnirov\core\Library;

final class HighLight
{
  public static function get(string $text, string $searchString):string
  {
    if ('' === $searchString) {
      
      return $text;
    }
    
    $keywords = preg_split("/\s+/",
      preg_quote(trim(mb_ereg_replace('  ', ' ', mb_ereg_replace("\*|\||\"|\&|\(|\)|\~|\[|\]", ' ', $searchString)))),
      -1, PREG_SPLIT_NO_EMPTY);
    
    foreach ($keywords as $word) {
      $pattern = false === mb_stripos($text, $word) ? self::_convertStringEnRu($word) : $word;
      $text = mb_eregi_replace($pattern, "<span class='highlight'>\\0</span>", $text);
    }
    
    return $text;
  }
  
  private static function _convertStringEnRu(string $string):string
  {
    
    return '' === $string
      ? ''
      : strtr($string, [
        "q" => "й",
        "w" => "ц",
        "e" => "у",
        "r" => "к",
        "t" => "е",
        "y" => "н",
        "u" => "г",
        "i" => "ш",
        "o" => "щ",
        "p" => "з",
        "[" => "х",
        "]" => "ї",
        "a" => "ф",
        "s" => "і",
        "d" => "в",
        "f" => "а",
        "g" => "п",
        "h" => "р",
        "j" => "о",
        "k" => "л",
        "l" => "д",
        ";" => "ж",
        "'" => "є",
        "z" => "я",
        "x" => "ч",
        "c" => "с",
        "v" => "м",
        "b" => "и",
        "n" => "т",
        "m" => "ь",
        "," => "б",
        "." => "ю",
        "`" => "ё",
        "Q" => "Й",
        "W" => "Ц",
        "E" => "У",
        "R" => "К",
        "T" => "Е",
        "Y" => "Н",
        "U" => "Г",
        "I" => "Ш",
        "O" => "Щ",
        "P" => "З",
        "{" => "Х",
        "}" => "Ъ",
        "A" => "Ф",
        "S" => "Ы",
        "D" => "В",
        "F" => "А",
        "G" => "П",
        "H" => "Р",
        "J" => "О",
        "K" => "Л",
        "L" => "Д",
        ":" => "Ж",
        "\"" => "Э",
        "Z" => "Я",
        "X" => "Ч",
        "C" => "С",
        "V" => "М",
        "B" => "И",
        "N" => "Т",
        "M" => "Ь",
        "<" => "Б",
        ">" => "Ю",
        "~" => "Ё"
      ]);
  }
}