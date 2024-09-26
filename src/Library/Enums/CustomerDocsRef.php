<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\CustomerTypes;

enum CustomerDocsRef: string implements CustomerTypes
{
  case Company = 'СУБЪЕКТ_ЮЛ';
  
  case Businessman = 'СУБЪЕКТ_ФОП';
  
  case Person = 'СУБЪЕКТ_ФЛ';
}