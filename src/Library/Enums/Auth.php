<?php

namespace okushnirov\core\Library\Enums;

enum Auth
{
  case DB;
  
  case DB_USER;
  
  case LDAP;
  
  case LDAP_DB;
}