<?php

use okushnirov\core\Library\{Authorization, Enums\CookieType, Enums\SessionType, Lang};

require_once __DIR__.'/../../php/handler_error.php';

Lang::set(SessionType::NONE, CookieType::No);

$userLogin = 'userLogin';
$userPass = 'userPassword';

$auth = new Authorization($userLogin, $userPass);

try {
  $isLogin = $auth->check(session: SessionType::NONE);
} catch (\Exception $e) {
  
  exit("ErrorCode: ".$e->getCode()." Message: ".$e->getMessage());
}

exit("User $userLogin is ".($auth::$isLogin ? '' : 'not').' logged in');