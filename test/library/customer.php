<?php

use okushnirov\core\Library\{Customer, Enums\CustomerType};

require_once __DIR__.'/../../php/handler_error.php';

$customers = [
  CustomerType::PERSON->name => [
    '2248000331' => 'РНОКПП',
    '123456789' => 'Паспорт у вигляді ID карти',
    'АА123456' => 'Серія та номер паспорта у вигляді книжечки',
    '0123456789' => 'Невірний код'
  ],
  CustomerType::BUSINESSMAN->name => [
    '2248000331' => 'РНОКПП',
    '0123456789' => 'Невірний код'
  ],
  CustomerType::COMPANY->name => [
    '12345678' => 'Код ЄДРПОУ',
    '123456789' => 'Невірний код ЄДРПОУ'
  ]
];

foreach ($customers as $type => $codes) {
  foreach ($codes as $code => $title) {
    echo "<pre>
<strong>$type - $title</strong>
  Code: $code Check: ".Customer::checkCode($code, CustomerType::fromName($type) ? 'Valid' : 'Not valid')."
</pre>";
  }
}