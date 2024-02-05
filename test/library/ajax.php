<?php

use okushnirov\core\Library\{Ajax, Enums\CookieType, Enums\SessionType};

require_once __DIR__.'/../../php/handler_error.php';

$a = new Ajax(session: SessionType::NONE, cookie: CookieType::No);

$a::init();
$a::isLogin();
$a::result();