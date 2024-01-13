<?php

use okushnirov\core\Library\{Ajax, Enums\SessionType};

require_once __DIR__.'/../../php/handler_error.php';

$a = new Ajax(session: SessionType::NONE);

$a::init();
$a::isLogin();
$a::result();