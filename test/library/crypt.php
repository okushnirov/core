<?php

use okushnirov\core\Library\{Crypt, Enums\Decrypt, Enums\Encrypt};

require_once __DIR__.'/../../php/handler_error.php';

$int = 42;
$intCrypt = Crypt::action($int, Encrypt::INT);
$intDecrypt = Crypt::action($intCrypt, Decrypt::INT);

echo "<pre>
<strong>INTEGER</strong>
  IN: $int<br>
  Crypt: $intCrypt<br>
  Decrypt: $intDecrypt<br>
  IN ".($int === (int)$intDecrypt ? " = " : " <> ")." OUT
</pre>";

$string = 'тестовая строка';
$stringCrypt = Crypt::action($string, Encrypt::CHR);
$stringDecrypt = Crypt::action($stringCrypt, Decrypt::CHR);

echo "<pre>
<strong>STRING</strong>
  IN: $string<br>
  Crypt: $stringCrypt<br>
  Decrypt: $stringDecrypt<br>
  IN ".($string === $stringDecrypt ? " = " : " <> ")." OUT
</pre>";

$url = '/folder=10?a=1&b=2&c';
$urlCrypt = Crypt::action($url, Encrypt::BASE);
$urlDecrypt = Crypt::action($urlCrypt, Decrypt::BASE);

echo "<pre>
<strong>URL</strong>
  IN: $url<br>
  Crypt: $urlCrypt<br>
  Decrypt: $urlDecrypt<br>
  IN ".($url === $urlDecrypt ? " = " : " <> ")." OUT
</pre>";