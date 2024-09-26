<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\CustomerTypes;

enum CustomerFrontRef: string implements CustomerTypes
{
  case Company = 'ЮЛ';
  
  case Businessman = 'ФОП';
  
  case Person = 'ФЛ';
}
