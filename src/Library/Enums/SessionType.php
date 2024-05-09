<?php

namespace okushnirov\core\Library\Enums;

enum SessionType
{
  case NONE;
  
  case DB;
  
  case PHP;
  
  case WS;
}