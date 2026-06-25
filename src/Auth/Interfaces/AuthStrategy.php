<?php

namespace okushnirov\core\Auth\Interfaces;

interface AuthStrategy
{
  public function authenticate():bool;
}