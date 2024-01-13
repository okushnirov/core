<?php

namespace okushnirov\core\Library\Enums;

enum SQLAnywhere: int
{
  case NO_RESULT = -1;
  
  case FETCH = 0;
  
  case FETCH_ALL = 1;
  
  case OBJECT = 2;
  
  case OBJECT_ALL = 3;
  
  case COLUMN = 4;
  
  case CALL = 5;
}