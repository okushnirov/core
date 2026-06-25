<?php

namespace okushnirov\core\Library;

final class HighLight
{
  public static function get(string $text, string $searchString, string $lang = 'uk'):string
  {
    if ('' === $searchString) {
      
      return $text;
    }
    
    $sanitized = mb_ereg_replace("\*|\||\&|\(|\)|\~|\[|\]", ' ', $searchString);
    $sanitized = preg_replace('/\s+/', ' ', trim($sanitized));
    
    if ('' === $sanitized) {
      
      return $text;
    }
    
    $keywords = explode(' ', $sanitized);
    $patterns = [];
    
    foreach ($keywords as $word) {
      if ('' === $word) {
        
        continue;
      }
      
      $altWord = (false === mb_stripos($text, $word)) ? ('ru' === $lang ? self::convertStringEnRu($word)
        : self::convertStringEnUa($word)) : $word;
      
      if ('' === $altWord) {
        
        continue;
      }
      
      $quotedWord = preg_quote($altWord, '/');
      
      $patterns[] = '/(?i)('.$quotedWord.')/u';
    }
    
    if (empty($patterns)) {
      
      return $text;
    }
    
    $patterns = array_unique($patterns);
    
    foreach ($patterns as $pattern) {
      $text = preg_replace($pattern, "<span class='highlight'>$1</span>", $text);
    }
    
    return $text;
  }
  
  private static function convertStringEnRu(string $string):string
  {
    if ('' === $string) {
      
      return '';
    }
    
    $map = [
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
      "]" => "ъ",
      "a" => "ф",
      "s" => "ы",
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
    ];
    
    return str_replace(array_keys($map), array_values($map), $string);
  }
  
  private static function convertStringEnUa(string $string):string
  {
    if ('' === $string) {
      
      return '';
    }
    
    $map = [
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
      "`" => "щ",
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
      "}" => "Ї",
      "A" => "Ф",
      "S" => "І",
      "D" => "В",
      "F" => "А",
      "G" => "П",
      "H" => "Р",
      "J" => "О",
      "K" => "Л",
      "L" => "Д",
      ":" => "Ж",
      "\"" => "Є",
      "Z" => "Я",
      "X" => "Ч",
      "C" => "С",
      "V" => "М",
      "B" => "И",
      "N" => "Т",
      "M" => "Ь",
      "<" => "Б",
      ">" => "Ю",
      "~" => "Й"
    ];
    
    return str_replace(array_keys($map), array_values($map), $string);
  }
}